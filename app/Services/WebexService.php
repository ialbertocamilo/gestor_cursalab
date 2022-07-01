<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ApiResponse;
use GuzzleHttp\Client;
use Firebase\JWT\JWT;
use Carbon\Carbon;

use App\Models\Error;
use App\Models\Usuario;

class WebexService extends MeetingService
{
    private $base_url = "https://webexapis.com/v1";

    protected function send($account, $path = '', $data = [], $method = 'get')
    {
        $response = null;

        try {

            $this->logActivity($account, $path, $method);

            $url = "$this->base_url$path";
            $token = $account->generateJWT();

            $client = new Client();
            $params = [
                'headers' => [
                    'Content-Type' => 'application/json',
//                    'Authorization' => 'Bearer '.$account->token,
                    'Authorization' => 'Bearer '.$token,
                ],
            ];

            if ($data and $method != 'get'){
                $params['body'] = json_encode($data);
            }

            $response = $client->$method($url, $params);

            $response = json_decode($response->getBody(), true);

        } catch (\Exception $e) {

            $response = $e->getResponse();
            $responseBodyAsString = $response->getBody()->getContents();
            info($responseBodyAsString);

            $message = $this->getCustomMessage($account, $path);

            Error::storeAndNotificateException($e, request(), $message);

            // Si viene de localhost/cron no cortar el flujo
            if (request()->ip() != '127.0.0.1')
                abort(response()->json(['message' => "ERROR WEBEX API"], 422));
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

    // https://marketplace.webex.us/docs/api-reference/webex-api/methods#operation/meetingCreate
    // https://marketplace.webex.us/docs/api-reference/webex-api/methods#operation/meetingUpdate
    protected function createOrUpdateMeeting($account, &$data, $meeting = null)
    {
        $config = config('webex.requests.meeting.create');
        $method = ($meeting and $meeting->identifier) ? 'put' : 'post';
        $url = ($meeting and $meeting->identifier) ? "/meetings/$meeting->identifier" : "/meetings";

        $data_webex = array_replace_recursive($config, [
            'title' => $data['name'] ?? $meeting->name,
            'agenda' => $data['description'] ?? $meeting->description ?? '',
            'start' => $data['starts_at'] ?? $meeting->starts_at,
            'end' => $data['finishes_at'] ?? $meeting->finishes_at,
            'password' => $meeting->password ?? $data['password'],
        ]);

        $result = $this->send($account, $url, $data_webex, $method);
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

    // https://marketplace.webexgov.com/docs/api-reference/webex-api/dashboards/dashboardmeetingparticipants
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

    // https://marketplace.webexgov.com/docs/api-reference/webex-api/dashboards/dashboardmeetingdetail
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

    protected function prepareAttendantsData($meeting, $data)
    {
        try {

        } catch (\Exception $e) {

            Error::storeAndNotificateException($e, request());

            if (request()->ip() != '127.0.0.1')
                abort(errorExceptionServer());
        }

        return $data_attendants;
    }

}
