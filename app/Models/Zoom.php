<?php

namespace App\Models;

use App\Traits\ApiResponse;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;

class Zoom extends Model
{
    private $base_url = "https://api.zoom.us/v2";

    public function logActivity($zoom_account, $path = '', $method)
    {
        $message = "ZOOM #ID {$zoom_account->id} - {$zoom_account->correo} => {$method} => {$path}";

        \Log::channel('zoom-activity-log')->info($message);
    }

    protected function send($zoom_acc, $path = '', $data = [], $method = 'get')
    {
        $response = null;

        try {

            $this->logActivity($zoom_acc, $path, $method);

            $url = "$this->base_url$path";
            $token = $zoom_acc->getZoomAccessToken($zoom_acc->client_id, $zoom_acc->client_secret, $zoom_acc->account_id);

            $client = new Client();

            $params = [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
            ];

            if ($data and $method != 'get')
                $params['body'] = json_encode($data);

            $response = $client->$method($url, $params);

            $response = json_decode($response->getBody(), true);

        } catch (\Exception $e) {
            info('ZOOM SEND');
            info($e);

            $zoom_message = $this->getZoomMessage($zoom_acc, $path);

            Error::storeAndNotificateException($e, request(), $zoom_message);

            // Si viene de localhost/cron no cortar el flujo
            if (request()->ip() != '127.0.0.1')
                abort(response()->json(['message' => "ERROR ZOOM API"], 422));
        }

        return $response;
    }

    public function getZoomMessage($zoom_account, $path)
    {
        $estado = $zoom_account->estado ? 'activo' : 'inactivo';

        return "[ Cuenta ID {$zoom_account->id} => {$zoom_account->correo} con estado {$estado} falló al consultar {$path} ]";
    }

    protected function createOrUpdateRoom($zoom, $data, $event)
    {
        $config = config('zoom.requests.meeting.create');
        $data_zoom = array_replace_recursive($config, [
            'duration' => $data['duracion'] ?? $event->duracion,
            'start_time' => date('Y-m-d\TH:i:s', strtotime($data['hora_inicio'] ?? $event->fecha_inicio)),
            'topic' => $data['titulo'] ?? $event->titulo,
            'agenda' => $data['descripcion'] ?? $event->descripcion,
            'password' => $event->zoom_password ?? rand(111111, 999999)
        ]);

//        info('array_replace_recursive', [$data_zoom]);

        if ($event->zoom_meeting_id) {
            $this->send($event->cuenta_zoom, "/users/$zoom->zoom_userid/meetings/$event->zoom_meeting_id", $data_zoom, 'patch');
            return 'updated';
        }

        return $this->send($event->cuenta_zoom, "/users/$zoom->zoom_userid/meetings", $data_zoom, 'post');
    }

    protected function deleteMeeting($event)
    {
        $url = "/meetings/$event->zoom_meeting_id";
        return $this->send($event->cuenta_zoom, $url, [], 'delete');
    }

    protected function getToken($zoom, $token_type)
    {
        $ttl = ($token_type == 'zak') ? '&ttl=7776000' : '';
        $url = '/users/' . $zoom->zoom_userid . '/token?type=' . $token_type . $ttl;
        $response = $this->send($zoom, $url);
        return $response['token'];
    }

    // https://marketplace.zoomgov.com/docs/api-reference/zoom-api/dashboards/dashboardmeetingparticipants
    protected function getListMeetingParticipants($evento, $zoom_type_query = 'past')
    {
        $dataResponse = array();
        $next_page_token = null;
        $base_url = "/metrics/meetings/$evento->zoom_meeting_id/participants?type={$zoom_type_query}&page_size=300";
        do {
            $url = $next_page_token ? $base_url . "&next_page_token=$next_page_token" : $base_url;
            $body = $this->send($evento->cuenta_zoom, $url);
            $next_page_token = !empty($body['next_page_token']) ? $body['next_page_token'] : null;

            if (!empty($body['participants']))
                $dataResponse = array_merge($body['participants'], $dataResponse);

        } while ($next_page_token);

//        info($dataResponse);

        return collect($dataResponse);
    }

    // https://marketplace.zoomgov.com/docs/api-reference/zoom-api/dashboards/dashboardmeetingdetail
    protected function getMeetingDetails($evento, $zoom_type_query = 'past')
    {
        $url = "/metrics/meetings/$evento->zoom_meeting_id?type={$zoom_type_query}";

        return $this->send($evento->cuenta_zoom, $url);
    }

    protected function endMeetingApi($event)
    {
        $dataResponse = [
            'action' => 'end'
        ];
        $url = "/meetings/{$event->zoom_meeting_id}/status";
        return $this->send($event->cuenta_zoom, $url, $dataResponse, 'put');
    }
}
