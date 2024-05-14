<?php

namespace App\Models;

use App\Services\WebexService;
use App\Services\ZoomService;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;

class Account extends BaseModel
{
    protected $fillable = [
        'workspace_id',
        'name', 'description', 'email', 'username', 'password', 'identifier', 'active',
        'key', 'secret', 'token', 'refresh_token', 'sdk_token', 'zak_token',
        'service_id', 'plan_id', 'type_id','account_id','client_id','client_secret', 'max_assistants', 'old_id'
    ];

    public function service()
    {
        return $this->belongsTo(Taxonomy::class, 'service_id');
    }

    public function plan()
    {
        return $this->belongsTo(Taxonomy::class, 'plan_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function meetings()
    {
        return $this->hasMany(Meeting::class);
    }




    public function generateJWT()
    {
        $payload = [
            "sub" => "JC-jwt-1",
            "iss" => $this->key,
            "exp" => time() + (7 * 24 * 60 * 60), // +1 semana
            // "exp" => time() + 3600, // +1 hora
        ];

        $temp = JWT::encode($payload, $this->secret, 'HS256');
        //        $this->refresh_token = $temp;

        $this->update(['refresh_token' => $temp]);

        return $this->refresh_token;
    }

    public function regenerateTokens()
    {
        $this->sdk_token = Zoom::getToken($this, 'token');
        $this->zak_token = Zoom::getToken($this, 'zak');

        $this->save();

        return ['sdk_token' => $this->sdk_token, 'zak_token' => $this->zak_token];
    }

    public function getZoomAccessToken($clientID, $clientSecret, $accountID) {
        $client = new Client();

        $response = $client->request('POST', 'https://zoom.us/oauth/token?grant_type=account_credentials&account_id=' . $accountID, [
            'auth' => [
                $clientID,
                $clientSecret
            ]
        ]);

        $data = json_decode($response->getBody(), true);

        $accessToken = $data['access_token'];

        return $accessToken;
    }



    protected function search($request)
    {
        $query = self::with('service', 'plan');

        # meeting segun workspaceid
        $currWorkspaceIndex = get_current_workspace_indexes('id');
        $query->where('workspace_id', $currWorkspaceIndex);

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%$request->q%");
                $q->orWhere('email', 'like', "%$request->q%");
            });
        }

        if ($request->service)
            $query->where('service_id', $request->service);

        if ($request->type)
            $query->where('type_id', $request->type);

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    protected function getActivesByType($type_id)
    {
        return Account::where('type_id', $type_id)->where('active', ACTIVE)->get();
    }

    protected function getDatesToSchedule($starts_at, $finishes_at, $minutes = 20)
    {
        $dates['starts_at'] = carbonFromFormat($starts_at)->subMinutes($minutes)->format('Y-m-d H:i:s');
        $dates['finishes_at'] = $finishes_at;

        return $dates;
    }

    protected function getOneAvailableForMeeting($type, $dates, $meeting = NULL)
    {
        if ($meeting and !$meeting->datesHaveChanged($dates)) return NULL;

        $accounts = $this->getAvailablesForMeeting($type, $dates, $meeting);

        return $accounts->count() ? $accounts->shuffle()->first() : NULL;
    }

    protected function getAvailablesForMeeting($type, $dates, $meeting = NULL, $method = 'get')
    {
        // From Gestor
        $workspace = get_current_workspace();

        // Or from APP
        $subworkspace = $workspace ? NULL : auth()->user()->subworkspace;

        $accounts = self::where('type_id', $type->id)
                        ->where('active', ACTIVE)
                        ->where('workspace_id', $workspace->id ?? $subworkspace->parent_id ?? NULL)
                        ->whereDoesntHave('meetings', function ($query) use ($dates, $meeting) {
                            $query->excludeMeeting($meeting);
                            $query->betweenScheduleDates($dates);
                            $query->ofReservedStatus();
                        })
                        ->$method();

        return $accounts;
    }

    protected function deleteMeeting($meeting)
    {
        switch ($meeting->account->service->code) {
            case "zoom":
                $deleted_meeting = ZoomService::deleteMeeting($meeting);
                break;

            case 'jitsi':
                // TODO $result = JitsiService::createOrUpdateMeeting($this, $data, $meeting);
                break;

            case 'vimeo':
                // TODO $result = VimeoService::createOrUpdateMeeting($this, $data, $meeting);
                break;

            case 'webex':
                // TODO:
                break;

            default:
                break;
        }
    }

    public function createOrUpdateMeetingService(&$data, $meeting = NULL)
    {
        $result = NULL;

        if ($meeting and !$meeting->datesHaveChanged($data)) return $result;

        switch ($this->service->code) {
            case 'zoom':

                $result = ZoomService::createOrUpdateRoom($this, $data, $meeting);

                if ($meeting and $meeting->datesHaveChanged($data)){
                    $meeting_details = ZoomService::getAMeeting($meeting);
                    $data['url_start'] = $meeting_details['url_start'] ?? $meeting->url_start;
                } else {
                    $data['url_start'] = $result['start_url'] ?? $meeting->url_start;
                }
                $data['url_start_generated_at'] = now();

                $data['url'] = $result['join_url'] ?? $meeting->url;
                $data['identifier'] = $result['id'] ?? $meeting->identifier;
                $data['password'] = $result['password'] ?? (
                    $meeting ? $meeting->password : null
                );

                // dd($data);
                break;

            case 'jitsi':
                // TODO $result = JitsiService::createOrUpdateMeeting($this, $data, $meeting);
                break;

            case 'vimeo':
                // TODO $result = VimeoService::createOrUpdateMeeting($this, $data, $meeting);
                break;

            case 'webex':
                $result = WebexService::createOrUpdateMeeting($this, $data, $meeting);

                // info($result);

                $data['identifier'] = $result['id'];
                $data['url_start'] = $result['webLink'] ?? NULL;
                $data['url'] = $result['webLink'];
//                $data['password'] = $result['password'];

                break;

            default:
                break;
        }

        return $result;
    }
}
