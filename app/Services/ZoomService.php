<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Error;
use GuzzleHttp\Client;
use App\Models\Taxonomy;
use App\Models\Attendant;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ZoomService extends MeetingService
{
    private $base_url = 'https://api.zoom.us/v2';
    public $dateFormat = 'Y-m-d\TH:i:sZ';

    protected function send($account, $path = '', $data = [], $method = 'get')
    {
        $response = null;

        try {

            $this->logActivity($account, $path, $method);

            $url = "$this->base_url$path";
            $token = $account->getZoomAccessToken($account->client_id, $account->client_secret, $account->account_id);
            info($token);

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
//            info("AFTER RESPONSE");
//            info($response);
        } catch (\Exception $e) {

            info($e);

            $message = $this->getCustomMessage($account, $path);
//            $response = $e->getResponse();
//            $responseBodyAsString = $response->getBody()->getContents();
//            info($responseBodyAsString);
            Error::storeAndNotificateException($e, request(), $message);

            // Si viene de localhost/cron no cortar el flujo
            if (request()->ip() != '127.0.0.1')
                abort(response()->json(['message' => "ERROR ZOOM API"], 422));
        }

        return $response;
    }

    protected function getToken($account, $token_type)
    {
        $ttl = ($token_type == 'zak') ? '&ttl=7776000' : '';
        $url = '/users/' . $account->identifier . '/token?type=' . $token_type . $ttl;

        $response = $this->send($account, $url);

        return $response['token'];
    }

    // https://marketplace.zoom.us/docs/api-reference/zoom-api/methods#operation/meetingCreate
    // https://marketplace.zoom.us/docs/api-reference/zoom-api/methods#operation/meetingUpdate
    // https://developers.zoom.us/docs/api/rest/reference/zoom-api/methods/#operation/meetingUpdate
    protected function createOrUpdateRoom($account, &$data, $meeting = null)
    {
        $config = config('zoom.requests.meeting.create');
        $method = ($meeting and $meeting->identifier) ? 'patch' : 'post';
        // $url = ($meeting and $meeting->identifier) ? "/users/{$account->email}/meetings/$meeting->identifier" : "/users/{$account->email}/meetings";
        $url = ($meeting and $meeting->identifier) ? "/meetings/$meeting->identifier" : "/users/{$account->email}/meetings";
        $startTimestamp = strtotime($data['starts_at'] ?? $meeting->starts_at);

        $data_zoom = array_replace_recursive($config, [
            'topic' => $data['name'] ?? $meeting->name,
            'agenda' => $data['description'] ?? $meeting->description ?? '',
            'duration' => $data['duration'] ?? $meeting->duration,
            'password' => $meeting->password ?? Str::random(10),
            'start_time' => date('Y-m-d\TH:i:s', $startTimestamp)
        ]);

        $result = $this->send($account, $url, $data_zoom, $method);
//        $result = ['join_url' => 'URL TEST', 'id' => 'ID TEST', 'password' => 'PASSWORD TEST', 'TEST' => 'XD'];

//        $data['url'] = $result['join_url'];
//        $data['identifier'] = $result['id'];
//        $data['password'] = $result['password'];

        return $result;
    }

    protected function deleteMeeting($meeting)
    {
        $url = "/meetings/$meeting->identifier";

        return $this->send($meeting->account, $url, [], 'delete');
    }

    protected function getAMeeting($meeting)
    {
        $url = "/meetings/{$meeting->identifier}";

        return $this->send($meeting->account, $url, [], 'get');
    }

    // https://marketplace.zoomgov.com/docs/api-reference/zoom-api/dashboards/dashboardmeetingparticipants
    protected function getListMeetingParticipants($meeting, $query_type = 'past')
    {
        $dataResponse = array();
        $next_page_token = null;
        $base_url = "/metrics/meetings/$meeting->identifier/participants?type={$query_type}&page_size=300";

        do {
            $url = $next_page_token ? $base_url . "&next_page_token=$next_page_token" : $base_url;
            $body = $this->send($meeting->account, $url);
            $next_page_token = !empty($body['next_page_token']) ? $body['next_page_token'] : null;

            if (!empty($body['participants']))
                $dataResponse = array_merge($body['participants'], $dataResponse);

        } while ($next_page_token);

        return collect($dataResponse);
    }

    // https://marketplace.zoomgov.com/docs/api-reference/zoom-api/dashboards/dashboardmeetingdetail
    protected function getMeetingDetails($meeting, $query_type = 'past')
    {
        $url = "/metrics/meetings/$meeting->identifier?type={$query_type}";

        return $this->send($meeting->account, $url);
    }

    protected function endMeeting($meeting)
    {
        $url = "/meetings/{$meeting->identifier}/status";

        $dataResponse = ['action' => 'end'];

        return $this->send($meeting->account, $url, $dataResponse, 'put');
    }

    // https://marketplace.zoom.us/docs/api-reference/zoom-api/methods/#operation/addBatchRegistrants
    protected function batchRegistration($meeting, $data)
    {
        $url = "/meetings/{$meeting->identifier}/batch_registrants";

        return $this->send($meeting->account, $url, $data, 'post');
    }

    protected function getLastDateFromUserLog($array, $key)
    {
        $format = 'Y-m-d\TH:i:sZ';

        $row = $this->getFirstElementFromArray($array, $key);

        if ($row)
            return Carbon::createFromFormat($format, $row)->subHours(5);

        return null;
    }

    protected function getFirstElementFromArray($array, $key, $descending = true)
    {
        $data = array_column($array, $key);

        if ($data) {
            $sorted = $descending ? rsort($data) : sort($data);

            return $data[0];
        }

        return null;
    }

    protected function prepareBatchAttendantsData($meeting, $attendants)
    {
        $chunks = $attendants->chunk(30)->all();
        $result = [];
        $prefix = $meeting->buildPrefix();

//        info("prepareBatchAttendantsData");
//        info('meeting');
//        info($meeting);
//        info('meeting->identifier');
//        info($meeting->identifier);
        foreach ($chunks as $chunk) {

            $data = [
                'auto_approve' => true,
                'registrants_confirmation_email' => true,
                'registrants' => $this->setDataForRegistrants($chunk, $prefix),
            ];

            $temp = $this->batchRegistration($meeting, $data)['registrants'];

            foreach ($temp as $item) {

                $usuario_id = $this->getIdFromBatchResult($item);
                $attendant = $attendants->where('usuario_id', $usuario_id)
                                        ->first();

                $result[$attendant->id] = [
                    'link' => $item['join_url'],
                    'identifier' => $item['registrant_id'],
                    'usuario_id' => $usuario_id,
                ];
            }
        }

        return $result;
    }

    protected function getIdFromBatchResult($attendant)
    {
        $explode = explode('@', $attendant['email']);
        $explode = explode('_', $explode[0]);
        $usuario_id = $explode[1];

        return $usuario_id;
    }

    public function setDataForRegistrants($attendants, $prefix)
    {
        $result = [];
        foreach ($attendants as $attendant) {
            $result[] = [
                'email' => "user_{$attendant->usuario->id}_{$attendant->id}@cursalab.io",
                'first_name' => $prefix . ' - ' . $attendant->usuario->name,
                'last_name' => $attendant->usuario->lastname,
            ];
        }
        return $result;
    }

    protected function prepareAttendantsData($meeting, $data)
    {
        try {

            $data_attendants = [];

            $only_in_meeting = $data->where('status', 'in_meeting');

            $id_registrants = $only_in_meeting->pluck('registrant_id')->toArray();
            $ip_registrants = $only_in_meeting->pluck('ip_address')->toArray();

            $participantsGrouppedByIdentifier = $only_in_meeting->groupBy('registrant_id');
            $participantsGrouppedByIpAddress = $only_in_meeting->groupBy('ip_address');

            $attendantsByIdentifier = Attendant::with('usuario:id,name,document')->where('meeting_id', $meeting->id)->whereIn('identifier', $id_registrants)->get();
            $attendantsByIpAddress = Attendant::with('usuario:id,name,document')->where('meeting_id', $meeting->id)->whereIn('ip', $ip_registrants)->get();

            $unknownParticipants = Arr::pull($participantsGrouppedByIdentifier, '');
            $extra_participants = $unknownParticipants->groupBy('ip_address');

            foreach ($participantsGrouppedByIdentifier as $id_registrant => $registros_usuario) {
                $attendant = $attendantsByIdentifier->where('identifier', $id_registrant)->first();

                if (!$attendant) {
                    $attendant = $attendantsByIpAddress->where('ip', $registros_usuario[0]->ip_address ?? NULL)->first();

                    if (!$attendant) continue;

                    if (isset($data_attendants[$attendant->usuario_id])) continue;
                }

                $this->setAttendantData($meeting, $attendant, $registros_usuario, $data_attendants);
            }

            foreach ($extra_participants as $ip_address => $registros_usuario) {
                $attendant = $attendantsByIpAddress->where('ip', $ip_address)->first();

                if (!$attendant) continue;

                if (isset($data_attendants[$attendant->usuario_id])) continue;

                $this->setAttendantData($meeting, $attendant, $registros_usuario, $data_attendants);
            }

        } catch (\Exception $e) {

            Error::storeAndNotificateException($e, request());

            if (request()->ip() != '127.0.0.1') abort(errorExceptionServer());
        }

        return $data_attendants;
    }

    public function setAttendantData($meeting, $attendant, $registros_usuario, &$data_attendants)
    {
        $key = $attendant->usuario_id;
        $now = now();
        $is_present = false;
        $total_duration = 0;

        $data_attendants[$key]['present_at_first_call'] = false;
        $data_attendants[$key]['present_at_middle_call'] = false;
        $data_attendants[$key]['present_at_last_call'] = false;
        //SOLUCIÓN TEMPORAL PARA MARCAR ASISTENCIAS PARA WORKSPACES DE MEXICO
        $has_taxonomy = Taxonomy::where('group','system')->where('type','attendant-call')->where('code','gt-6')->where('active',1)->first();
        $sub_hours_time = $has_taxonomy ? 6 : 5;
        foreach ($registros_usuario as $registro) {
            $join_time = Carbon::createFromFormat($this->dateFormat, $registro['join_time'])->subHours($sub_hours_time);
            $leave_time = isset($registro['leave_time']) ? Carbon::createFromFormat($this->dateFormat, $registro['leave_time'])->subHours($sub_hours_time) : $now;
            if ($meeting->attendance_call_first_at->between($join_time, $leave_time))
                $data_attendants[$key]['present_at_first_call'] = true;

            if ($meeting->attendance_call_middle_at->between($join_time, $leave_time))
                $data_attendants[$key]['present_at_middle_call'] = true;

            if ($meeting->attendance_call_last_at->between($join_time, $leave_time))
                $data_attendants[$key]['present_at_last_call'] = true;

            if (!$is_present) $is_present = !isset($registro['leave_time']);

            $total_duration += $leave_time->diffInMinutes($join_time);
//            info("$key Total duration");
//            info($join_time->format("H:i:s"));
//            info($leave_time->format("H:i:s"));
//            info("Acumulado $total_duration");
        }

        $data_attendants[$key]['online'] = $is_present;

        $total_registros = count($registros_usuario);

        $join_time = $this->getLastDateFromUserLog($registros_usuario->toArray(), 'join_time');
        $leave_time = $this->getLastDateFromUserLog($registros_usuario->toArray(), 'leave_time');

        $data_attendants[$key]['total_logins'] = $total_registros;
        $data_attendants[$key]['total_logouts'] = $total_registros;

        if ($join_time) {
            $data_attendants[$key]['first_login_at'] = $join_time;
            $data_attendants[$key]['last_login_at'] = $join_time;
        }

        if ($leave_time) $data_attendants[$key]['last_logout_at'] = $leave_time;

        $data_attendants[$key]['total_duration'] = $total_duration;

        return $key;
    }

}
