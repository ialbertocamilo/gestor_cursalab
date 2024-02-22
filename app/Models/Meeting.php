<?php

namespace App\Models;

use stdClass;
// use function foo\func;
use Carbon\Carbon;
use App\Services\ZoomService;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\MeetingAppResource;
use Illuminate\Http\Request;

class Meeting extends BaseModel
{
    protected $dates = [
        'starts_at', 'finishes_at', 'started_at', 'finished_at', 'report_generated_at',
        'attendance_call_first_at', 'attendance_call_middle_at', 'attendance_call_last_at',
        'url_start_generated_at',
    ];

    protected $fillable = [
        'workspace_id',
        'name', 'description', 'embed', 'raw_data_response',
        'url', 'url_start', 'identifier', 'username', 'password',
        'starts_at', 'finishes_at', 'duration', 'started_at', 'finished_at', 'report_generated_at',
        'url_start_generated_at',
        'attendance_call_first_at', 'attendance_call_middle_at', 'attendance_call_last_at',
        'status_id', 'account_id', 'type_id', 'host_id', 'user_id','model_id','model_type'
    ];

    protected $hidden = ['raw_data_response'];

    protected $casts = [
        'embed' => 'boolean',
        'raw_data_response' => 'array',
    ];

    /*

        Relationships

    --------------------------------------------------------------------------*/

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    public function attendants()
    {
        return $this->hasManySync(Attendant::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function workspace() {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    public function host()
    {
        return $this->belongsTo(Usuario::class, 'host_id');
        //return $this->belongsTo(User::class, 'host_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function platform_family()
    {
        return $this->belongsTo(Taxonomy::class, 'platform_family_id');
    }

    public function browser_family()
    {
        return $this->belongsTo(Taxonomy::class, 'browser_family_id');
    }

    public function device_family()
    {
        return $this->belongsTo(Taxonomy::class, 'device_family_id');
    }

    public function device_model()
    {
        return $this->belongsTo(Taxonomy::class, 'device_model_id');
    }

    /*

        Scopes

    --------------------------------------------------------------------------*/

    public function scopeBetweenScheduleDates($query, $dates)
    {
        return $query->where('starts_at', '<=', $dates['finishes_at'])
            ->where('finishes_at', '>=', $dates['starts_at']);
    }

    public function scopeOfReservedStatus($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->whereIn('code', ['scheduled', 'in-progress', 'reserved']);
        });
    }

    public function scopeExcludeMeeting($query, $meeting = NULL)
    {
        $meeting_id = $meeting;
        if (is_object($meeting)) $meeting_id = $meeting->id;

        return $query->when($meeting_id, function ($q) use ($meeting_id) {
            $q->where('meetings.id', '<>', $meeting_id);
        });
    }

    /*

        Helpers

    --------------------------------------------------------------------------*/

    public function attendantsWithFirstLogintAt()
    {
        return $this->attendants()->whereNotNull('first_login_at');
    }

    public function datesHaveChanged($dates)
    {
        return (
            $this->starts_at != $dates['starts_at'] ||
            $this->finishes_at != $dates['finishes_at']
        );
    }

    public function typeHasChanged($type)
    {
        return $this->type->id != $type->id;
    }

    public function getRangeTime()
    {
        $starts = $this->starts_at->format('g:i a');
        $finishes = $this->finishes_at->format('g:i a');

        return "{$starts} a {$finishes}";
    }

    public function getRangeTimeWithDuration()
    {
        $starts = $this->starts_at->format('g:i a');
        $finishes = $this->finishes_at->format('g:i a');

        return "{$starts} a {$finishes} ({$this->duration} minutos)";
    }

    public function getCurrentDateStatus()
    {
        if ($this->status->code == 'scheduled' and $this->starts_at->isToday() and $this->finishes_at > now())
            return 'HOY';

        if ($this->isOnTime())
            return 'LIVE';

        return NULL;
    }

    public function getTotalDuration()
    {
        $meeting = $this;

        if ($meeting->started_at and $meeting->finished_at) {
            $started_at = $meeting->started_at < $meeting->starts_at ?
                $meeting->starts_at : $meeting->started_at;
            return $meeting->finished_at->diffInMinutes($started_at);
        }
        return 0;
    }

    public function buildPrefix(string $flag = 'M')
    {
        return $flag.$this->id.'-'.$this->starts_at->format('md');
    }

    public function isOnTime()
    {
        return now()->between($this->starts_at, $this->finishes_at);
    }

    public function canBeEdited()
    {
        return in_array($this->status->code, ['scheduled', 'in-progress']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status->code, ['scheduled']);
    }

    public function canBeDeleted()
    {
        return in_array($this->status->code, ['scheduled']);
    }

    public function isLive()
    {
        return $this->status->code == 'in-progress';
    }


    /*

        Methods

    --------------------------------------------------------------------------*/

    public function getRealPercetageOfAttendees()
    {
        $attendants = $this->attendants()->count();

        // When there are attendants, calculate the percentage

        if ($attendants > 0) {

            $division = $this->attendantsWithFirstLogintAt()->count() / $attendants;
            return number_format((float)$division * 100, 2);

        } else {
            return 0;
        }
    }

    protected function search($request, $method = 'paginate')
    {
        $query = self::with('type', 'status', 'account.service', 'workspace', 'host', 'attendants.type')->withCount('attendants');

        # meeting segun workspaceid
        $currWorkspaceIndex = get_current_workspace_indexes('id');
        $query->where('workspace_id', $currWorkspaceIndex);
        # meeting segun workspaceid
        if(!$request->include_topic_meetings){
            $query->where('model_type','<>','App\\Models\\Topic');
        }
        if ($request->usuario_id)
            $query->whereHas('attendants', function ($q) use ($request) {
                $q->where('usuario_id', $request->usuario_id);
            });

        if ($request->q)
            $query->where('name', 'like', "%$request->q%");

        if ($request->type)
            $query->where('type_id', $request->type);

        if ($request->statuses)
            $query->whereIn('status_id', $request->statuses);

        if ($request->date)
            $query->whereDate('starts_at', '=', $request->date);

        if ($request->dates) :
            if (count($request->dates) > 1) {
                $starts_at = $request->dates[0] < $request->dates[1] ? $request->dates[0] : $request->dates[1];
                $finishes_at = $request->dates[0] < $request->dates[1] ? $request->dates[1] : $request->dates[0];

                $query->whereDate('starts_at', '>=', $starts_at);
                $query->whereDate('finishes_at', '<=', $finishes_at);
            } else if (isset($request->dates[0])) {
                $query->whereDate('starts_at', $request->dates[0]);
            }
        endif;

        if ($request->date_start)
            $query->whereDate('starts_at', '>=', $request->date_start);

        if ($request->date_finish)
            $query->whereDate('starts_at', '<=', $request->date_finish);

        $field = $request->sortBy ?? 'starts_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);
        // ->orderBy('name', $sort)->orderBy('id', $sort);

        if ($method == 'paginate')
            return $query->paginate($request->paginate);

        return $query->$method();
    }

    protected function getAppData(){
        $scheduled = Taxonomy::getFirstData('meeting', 'status', 'scheduled');
        $started = Taxonomy::getFirstData('meeting', 'status', 'in-progress');
        $finished = Taxonomy::getFirstData('meeting', 'status', 'finished');
        $overdue = Taxonomy::getFirstData('meeting', 'status', 'overdue');
        $cancelled = Taxonomy::getFirstData('meeting', 'status', 'cancelled');

        $subworkspace = auth()->user()->subworkspace;
        $request->merge(['workspace_id' => $subworkspace->parent_id]);

        $filters_today = new Request([
            'usuario_id' => auth()->user()->id,
            'statuses' => [$scheduled->id, $started->id],
            'date' => Carbon::today(),
        ]);

        $filters_scheduled = new Request([
            'usuario_id' => auth()->user()->id,
            'statuses' => [$scheduled->id],
            'date_start' => Carbon::tomorrow(),
        ]);

        $filters_finished = new Request([
            'usuario_id' => auth()->user()->id,
            'statuses' => [$finished->id, $overdue->id, $cancelled->id],
        ]);

        $data = [
            'today' => [
                'code' => 'today',
                'title' => 'Hoy',
                'total' => Meeting::search($filters_today, 'count'),
            ],
            'scheduled' => [
                'code' => 'scheduled',
                'title' => 'Próximas',
                'total' => Meeting::search($filters_scheduled, 'count'),
            ],
            'finished' => [
                'code' => 'finished',
                'title' => 'Historial',
                'total' => Meeting::search($filters_finished, 'count'),
            ],

            'current_server_time' => [
                'timestamp' => (int) (now()->timestamp . '000'),
                'value' => now(),
            ],

            'recommendations' => config('meetings.recommendations'),
        ];
        return $data;
    }

    protected function storeRequest($data, $meeting = null, $files = [])
    {
        try {

            $dates = Account::getDatesToSchedule(
                $data['starts_at'], $data['finishes_at']
            );

            $status = Taxonomy::getFirstData('meeting', 'status', 'scheduled');
            $type = Taxonomy::find($data['type_id']);
            $host = Usuario::find($data['host_id']);
            $data['model_type'] = isset($data['model_type']) ? $data['model_type'] : 'App\\Models\\Meeting';
            if($type->code == 'benefits'){
                $data['model_type'] = 'App\\Models\\BenefitProperty';
            }
            $datesHaveChanged = $meeting && $meeting->datesHaveChanged($data);

            #add workspace id
            $data['workspace_id'] = $data['workspace_id'] ?? get_current_workspace_indexes('id');
            #add workspace id

            DB::beginTransaction();
            if ($meeting) {

                if ($datesHaveChanged || $meeting->typeHasChanged($type)) {

                    $account = Account::getOneAvailableForMeeting(
                        $type, $dates, $meeting
                    );
                    $account->createOrUpdateMeetingService($data, $meeting); // zoom ref
                    $data['account_id'] = $account->id;

                    Meeting::getScheduledAttendanceCall($data);
                }
//                info("DATA UPDATE STORE REQUEST");
//                info($data);
                $meeting->update($data);

            } else {

                $account = Account::getOneAvailableForMeeting($type, $dates);

                $data['user_id'] = auth()->user() instanceof User ? auth()->user()->id : NULL;
                $data['status_id'] = $status->id;
                $data['account_id'] = $account->id;

                $account->createOrUpdateMeetingService($data); // zoom ref

                Meeting::getScheduledAttendanceCall($data);

                $meeting = Meeting::create($data);

            }

            $data['attendants'][] = Attendant::mergeHostToAttendants(
                $meeting, $host
            );

            $attendants = $meeting->attendants()->sync($data['attendants']);

            Attendant::createOrUpdatePersonalLinkMeeting(
                $meeting, $datesHaveChanged
            );

            // Insert meeting in master database

            SourceMultimarca::insertSource(
                $meeting->identifier,'meeting', $meeting->id
            );

            DB::commit();

            $meeting->sendMeetingPushNotifications($attendants);
            $meeting->sendMeetingEmails();

        } catch (\Exception $e) {

            info('e error meeting');
            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }

        return $meeting;
    }

    protected function addAttendantFromUser(Meeting $meeting,array $users ){
        $users_in_meeting = $meeting->attendants()->get();
        $user_type = Taxonomy::getFirstData('meeting', 'user','normal');
        $attendants = [];
        foreach ($users as $user) {
            $user_in_meeting = $users_in_meeting->where('usuario_id',$user['id'])->first();
            if(!$user_in_meeting){
                $attendants[] = [
                    'usuario_id' => $user['id'],
                    'type_id' => $user_type?->id, 
                    'id' => null
                ];
            }
        }
        if(count($attendants)){
            $meeting->attendants()->sync($attendants,false);
            // $meeting->sendMeetingPushNotifications($attendants);
        }
    }

    protected function deleteAttendantFromUser(Meeting $meeting,array $users){
        $users_id_to_delete = collect($users)->pluck('id');
        $meeting->attendants()->where('usuario_id',$users_id_to_delete)->delete();
    }
    protected function getScheduledAttendanceCall(&$data)
    {
        $starts_at = carbonFromFormat($data['starts_at']);

        $start_unixtime = $starts_at->timestamp;
        $duration_seconds = $data['duration'] * 60;

        $data['attendance_call_first_at'] = $start_unixtime + ($duration_seconds * 0.25);
        $data['attendance_call_middle_at'] = $start_unixtime + ($duration_seconds * 0.50);
        $data['attendance_call_last_at'] = $start_unixtime + ($duration_seconds * 0.75);

        return true;
    }

    public function sendMeetingEmails()
    {

    }

    public function removeCurrentHost()
    {
        return $this->removeUsuarioFromMeeting($this->host);
    }

    public function removeUsuarioFromMeeting($usuario)
    {
        return $this->attendants()->where('usuario_id', $usuario->id)->delete();
    }

    public function getAttendantTokens($attendants)
    {
        $added = !empty($attendants['created'][0])
                    ? Usuario::asAttendantsWithToken($attendants['created'][0])->pluck('token_firebase')
                    : [];

        $remained = !empty($attendants['updated'])
                    ? Usuario::asAttendantsWithToken($attendants['updated'])->pluck('token_firebase')
                    : [];

        return compact('added', 'remained');
    }

    public function sendMeetingPushNotifications($attendants)
    {
        $tokens = $this->getAttendantTokens($attendants);

        $date = $this->starts_at->format('d/m/Y');
        $time = $this->starts_at->format('H:i');
        $body = "La reunión se llevará a cabo el $date a las $time horas.";

        if (count($tokens['added']) > 0):

            $title = '¡Hola! tienes una nueva reunión agendada';

            PushNotification::enviar($title, $body, $tokens['added']);

        endif;

        if (count($tokens['remained']) > 0 and ($this->wasChanged('starts_at') || $this->wasChanged('finishes_at'))):

            $title = "Se ha actualizado la fecha de la reunión: $this->name";

            PushNotification::enviar($title, $body, $tokens['remained']);

        endif;
    }

    public function finalize($force = false)
    {
        $meeting = $this;
        $status = Taxonomy::getFirstData('meeting', 'status', 'finished');

        try {

            switch ($meeting->account->service->code) {
                case 'zoom':

                    $result = ZoomService::endMeeting($meeting);

                    break;

                case 'jitsi':
                    // TODO $result = JitsiService::endMeeting($meeting);
                    break;

                case 'vimeo':
                    // TODO $result = VimeoService::endMeeting($meeting);
                    break;

                default:
                    break;
            }

            $meeting->update(['status_id' => $status->id, 'finished_at' => now()]);

        } catch (\Exception $e) {

            Error::storeAndNotificateAppException($e, [
                'message' => 'Error al finalizar reunión. Debe finalizarse manualmente en su propio panel de administración.',
                'meeting_id' => $meeting->id
            ]);

            if (request()->ip() != '127.0.0.1')
                abort(errorExceptionServer());
        }

        return true;
    }

    public function cancel($force = false)
    {
        $meeting = $this;
        $status = Taxonomy::getFirstData('meeting', 'status', 'cancelled');

        $meeting->update(['status_id' => $status->id]);
        $meeting->load('account');
        $canceled_meeting = Account::deleteMeeting($meeting);

        $title = "Se ha cancelado la reunión {$meeting->name}";
        $body = "";
        $this->sendPushNotificationToAttendants($title, $body);

        return true;
    }

    public function deleteMeeting($force = false)
    {
        $meeting = $this;

        $meeting->delete();
        $meeting->attendants()->delete();
        $deleted_meeting = Account::deleteMeeting($meeting);

        $title = "Se ha eliminado la reunión {$meeting->name}";
        $body = "";

        $this->sendPushNotificationToAttendants($title, $body);

        return true;
    }

    public function sendPushNotificationToAttendants($title, $body, $excluded_attendants_id = [], $custom_firebase_tokens = null)
    {
        if (!$custom_firebase_tokens)
            $firebase_tokens = $this->getFirebaseTokensFromAttendants($excluded_attendants_id);
        else
            $firebase_tokens = array_diff($custom_firebase_tokens, $excluded_attendants_id);

        PushNotification::enviar($title, $body, $firebase_tokens);
    }

    public function getFirebaseTokensFromAttendants($excluded_attendants_id = [])
    {
        $attendants_usuarios_token = $this->attendants()
            ->whereHas('usuario', function ($q) use ($excluded_attendants_id) {
                $q->whereNotNull('token_firebase');
            })
            ->whereNotIn('id', $excluded_attendants_id)
            ->get()
            ->pluck('usuario.token_firebase');

        return $attendants_usuarios_token;
    }

    protected function finalizeWebhook($data, $type)
    {
        $message = "Webhook End Meeting";
        \Log::channel($type . '-activity-log')->info($message);
        switch ($type) {
            case 'zoom':

                $meeting_identifier = $data['payload']['object']['id'] ?? null;
                $meeting = Meeting::where('identifier', $meeting_identifier)->first();

                if ($meeting) {
                    $status = Taxonomy::getFirstData('meeting', 'status', 'finished');

                    $meeting->update(['status_id' => $status->id, 'finished_at' => now()]);

                } else {
                    \Log::channel($type . '-activity-log')->info($data);
                    Error::storeAndNotificateDefault(
                        'Error al finalizar reunión.',
                        'Aulas Virtuales',
                        'Webhook Zoom');
                }

                break;

            case 'jitsi':
                // TODO $result = JitsiService::endMeeting($meeting);
                break;

            case 'vimeo':
                // TODO $result = VimeoService::endMeeting($meeting);
                break;

            default:
                break;
        }
    }

    public function updateMeetingUrlStart()
    {
        $meeting = $this;

        switch ($meeting->account->service->code) {
            case 'zoom':
                $result = ZoomService::getAMeeting($meeting);

                $meeting->url_start = $result['start_url'];
                $meeting->url_start_generated_at = now();

                $meeting->save();
                break;

            case 'jitsi':
                // TODO
                break;

            default:
                break;
        }

        return true;
    }

    public function updateAttendantsData()
    {
        $meeting = $this;

        switch ($meeting->account->service->code) {
            case 'zoom':

                $zoom_type_query = $meeting->status->code == 'in-progress' ? 'live' : 'past';

                $result = ZoomService::getListMeetingParticipants($meeting, $zoom_type_query);

                if ($result->count()) {
                    $meeting->update(['raw_data_response' => $result]);

                    $data = ZoomService::prepareAttendantsData($meeting, $result);

                    foreach ($data as $usuario_id => $row) {
                        Attendant::where('meeting_id', $meeting->id)->where('usuario_id', $usuario_id)->update($row);
                    }

                    info(" ------- Actualización de usuarios del meeting => {$meeting->id} ------- ");

                    $meeting->update(['report_generated_at' => now()]);
                }

                break;

            case 'jitsi':
                // TODO $result = JitsiService::endMeeting($meeting);
                break;

            default:
                break;
        }

        return true;
    }
    protected function getListMeetingsByUser($request,$format_response = 'data'){
        $scheduled = Taxonomy::getFirstData('meeting', 'status', 'scheduled');
        $started = Taxonomy::getFirstData('meeting', 'status', 'in-progress');
        $finished = Taxonomy::getFirstData('meeting', 'status', 'finished');
        $overdue = Taxonomy::getFirstData('meeting', 'status', 'overdue');
        $cancelled = Taxonomy::getFirstData('meeting', 'status', 'cancelled');

        $subworkspace = auth()->user()->subworkspace;

        $request->merge(['usuario_id' => auth()->user()->id, 'workspace_id' => $subworkspace->parent_id]);

        if ($request->code) {
            if ($request->code == 'today')
                $request->merge([
                    'statuses' => [$scheduled->id, $started->id],
                    'date' => Carbon::today(),
                ]);

            if ($request->code == 'scheduled')
                $request->merge([
                    'statuses' => [$scheduled->id],
                    'date_start' => Carbon::tomorrow(),
                ]);

            if ($request->code == 'finished')
                $request->merge([
                    'statuses' => [$finished->id, $overdue->id, $cancelled->id],
                    'sortDesc' => 'true',
                ]);
        }

        $meetings = Meeting::search($request);
        $meetings = MeetingAppResource::collection($meetings);
        if(count($meetings) == 0){
            return [];
        }
        // info(__function__);
        $result = json_decode($meetings->toJson(), true);
        if($format_response == 'data'){
            $result['data'] = collect($result)->groupBy('key')->all();
            if (count($result['data']) === 0) $result['data'] = new stdClass();
        }else{
            $sessions_group_by_date = $result;
            $sessions_group_by_date = collect($sessions_group_by_date)->groupBy('key')->all();
            if (count($sessions_group_by_date) === 0) $sessions_group_by_date = new stdClass();
            return $sessions_group_by_date;
        }
        return $result;
    }
    public function getDatesFormatted()
    {
        $meeting = $this;

        $meeting->date_title = $meeting->starts_at->format('d/m/Y g:i A') . ' (' . $meeting->duration . ' min)';
        $meeting->created_at_formatted = $meeting->created_at->format('d/m/Y g:i A');

        $meeting->attendance_call_first_at_formatted = $meeting->attendance_call_first_at ? $meeting->attendance_call_first_at->format('g:i:s A') : 'No definido';
        $meeting->attendance_call_middle_at_formatted = $meeting->attendance_call_middle_at ? $meeting->attendance_call_middle_at->format('g:i:s A') : 'No definido';
        $meeting->attendance_call_last_at_formatted = $meeting->attendance_call_last_at ? $meeting->attendance_call_last_at->format('g:i:s A') : 'No definido';

        $meeting->starts_at_formatted = $meeting->starts_at->format('d/m/Y g:i A');
        $meeting->finishes_at_formatted = $meeting->finishes_at->format('g:i A');

//        $meeting->started_at_formatted = $meeting->started_at ? $meeting->started_at->format('d/m/Y g:i A') : '¿¿';
        $meeting->started_at_formatted = $meeting->started_at ?
            ($meeting->started_at < $meeting->starts_at ? $meeting->starts_at->format('d/m/Y g:i A') : $meeting->started_at->format('d/m/Y g:i A'))
            : '¿¿';


        $meeting->finished_at_formatted = $meeting->finished_at ? $meeting->finished_at->format('d/m/Y g:i A') : '??';

        $meeting->real_duration = $meeting->getTotalDuration() ?? '??';


        $meeting->report_generated_at_formatted = $meeting->report_generated_at ? $meeting->report_generated_at->format('d/m/Y g:i:s a') : NULL;
        $meeting->report_generated_at_diff = $meeting->report_generated_at ? $meeting->report_generated_at->diffForHumans() : NULL;
    }

    public function getStats()
    {
        $meeting = $this;

        $stats = [];

        // Stats 1
        $stats['guestsVsAttendees'] = $meeting->getGuestsVsAttendees();
        $stats['averageTimeInMeeting'] = $meeting->averageTimeInMeeting();

        $stats['attendeesPerCallAssistance'] = $meeting->attendeesPerCallAssistance();
        $stats['topGuestDurationInMeeting'] = $meeting->topGuestDurationInMeeting();

        if (auth()->user()->isMasterOrAdminCursalab()) {
            $stats['devicesStatsByPlatformFamily'] = $meeting->devicesStatsByPlatformFamily();
            $stats['devicesStatsByBrowserFamily'] = $meeting->devicesStatsByBrowserFamily();
            $stats['statsByDeviceFamily'] = $meeting->statsByDeviceFamily();
            $stats['statsByDeviceModel'] = $meeting->statsByDeviceModel();
        }

        $meeting->stats = $stats;
    }

    public function statsByDeviceModel()
    {
        $meeting = $this;
        $platforms = $meeting->attendants()
            ->with('device_model:id,name')
            ->whereNotNull('device_model_id')
            ->groupBy('device_model_id')
            ->select('id', 'device_model_id', 'meeting_id',
                DB::raw("count(id) as custom_count")
            )
            ->get();

        $i = 0;
        $data = [];

        foreach ($platforms as $platform) {
            $data['labels'][] = $platform->device_model->name ?? 'No identificado';
            $data['values'][] = $platform->custom_count;
            $i++;
        }

        if (count($data) == 0) {
            $data['labels'] = ["Sin registros"];
            $data['values'] = [0];
        }

        return $data;
    }

    public function statsByDeviceFamily()
    {
        $meeting = $this;
        $platforms = $meeting->attendants()
            ->with('device_family:id,name')
            ->whereNotNull('device_family_id')
            ->groupBy('device_family_id')
            ->select('id', 'device_family_id', 'meeting_id',
                DB::raw("count(id) as custom_count")
            )
            ->get();

        $i = 0;
        $data = [];

        foreach ($platforms as $platform) {
            $data['labels'][] = $platform->device_family->name ?? 'No identificado';
            $data['values'][] = $platform->custom_count;
            $i++;
        }

        if (count($data) == 0) {
            $data['labels'] = ["Sin registros"];
            $data['values'] = [0];
        }

        return $data;
    }

    public function devicesStatsByBrowserFamily()
    {
        $meeting = $this;
        $platforms = $meeting->attendants()
            ->with('browser_family:id,name')
            ->whereNotNull('browser_family_id')
            ->groupBy('browser_family_id')
            ->select('id', 'browser_family_id', 'meeting_id',
                DB::raw("count(id) as custom_count")
            )
            ->get();

        $i = 0;
        $data = [];

        foreach ($platforms as $platform) {
            $data['labels'][] = $platform->browser_family->name;
            $data['values'][] = $platform->custom_count;
            $i++;
        }

        if (count($data) == 0) {
            $data['labels'] = ["Sin registros"];
            $data['values'] = [0];
        }

        return $data;
    }

    public function devicesStatsByPlatformFamily()
    {
        $meeting = $this;
        $platforms = $meeting->attendants()
            ->with('platform_family:id,name')
            ->whereNotNull('platform_family_id')
            ->groupBy('platform_family_id')
            ->select('id', 'platform_family_id', 'meeting_id',
                DB::raw("count(id) as custom_count")
            )
            ->get();

        $i = 0;
        $data = [];

        foreach ($platforms as $platform) {
            $data['labels'][] = $platform->platform_family->name;
            $data['values'][] = $platform->custom_count;
            $i++;
        }

        if (count($data) == 0) {
            $data['labels'] = ["Sin registros"];
            $data['values'] = [0];
        }

        return $data;
    }

    public function topGuestDurationInMeeting()
    {
        $data = [];
        $host = Taxonomy::getFirstData('meeting', 'user', 'host');
        $cohost = Taxonomy::getFirstData('meeting', 'user', 'cohost');

        $top_attendants = $this->attendants()
            ->with('usuario:id,name,document')
            ->select('total_duration', 'usuario_id')
            ->orderBy('total_duration', 'DESC')
            ->whereNotNull('total_duration')
            ->whereNotIn('type_id', [$host->id, $cohost->id])
            ->limit(5)->get();

        $i = 1;

        foreach ($top_attendants as $top_attendant) {
//            $data['labels'][] = "Top {$i}";
            $data['labels'][] = $top_attendant->usuario->document;
            $data['values'][] = $top_attendant->total_duration;
            $i++;
        }

        if (count($data) == 0) {
            $data['labels'] = ["Top 1", "Top 2", "Top 3", "Top 4", "Top 5"];
            $data['values'] = [0, 0, 0, 0, 0];
        }
        $data['name'] = "Minutos en reunión";

        return $data;
    }

    public function averageTimeInMeeting()
    {
        $meeting = $this;

        $total_duration = $meeting->isLive() ?
            $meeting->started_at->diffInMinutes(now())
            : $meeting->finished_at->diffInMinutes($meeting->started_at);

        $average_time_in_meeting_participants = 0;

        foreach ($meeting->attendants as $attendant) {
            $average_time_in_meeting_participants += $attendant->total_duration;
        }

        $average_formated = (float)number_format(($average_time_in_meeting_participants / $meeting->attendants->count()) * 100, 2);

        return [
            'labels' => ["Total minutos de la reunión", "Minutos promedio de asistentes"],
            'values' => [$total_duration, $average_formated]
        ];
    }

    public function getGuestsVsAttendees()
    {
        $meeting = $this;
        $count_no_attendants = $meeting->attendants()->whereNull('first_login_at')->count();
        $count_attendants = $meeting->attendants()->whereNotNull('first_login_at')->count();

        return [
            'name' => 'Cantidad',
            'labels' => ['Asistieron', 'No Asistieron'],
            'values' => [$count_attendants, $count_no_attendants],
        ];
    }

    public function attendeesPerCallAssistance()
    {
        $meeting = $this;
        $first_call = $meeting->attendants()
            ->where('present_at_first_call', 1)
            ->count();
        $middle_call = $meeting->attendants()
            ->where('present_at_middle_call', 1)
            ->count();
        $last_call = $meeting->attendants()
            ->where('present_at_last_call', 1)
            ->count();

        return [
            'name' => 'Cantidad',
            'labels' => ['Primera Asistencia', 'Segunda Asistencia', 'Tercera Asistencia'],
            'values' => [$first_call, $middle_call, $last_call],
        ];
    }

    public function setDateAndTimeToForm()
    {
        $this->date = $this->starts_at->format('Y-m-d');
        $this->time = $this->starts_at->format('H:i');
    }

    public function start()
    {
        $meeting = $this;
        $usuario = auth()->user();

        $status = Taxonomy::getFirstData('meeting', 'status', 'in-progress');

        // switch ($meeting->account->service->code) {
        //     case 'zoom':
        //         // $result = ZoomService::startMeeting($meeting);
        //         break;

        //     case 'jitsi':
        //         // TODO $result = JitsiService::startMeeting($meeting);
        //         break;

        //     default:
        //         break;
        // }

        if (!$meeting->started_at) {

            if (now()->diffInMinutes($meeting->url_generated_at) > 120){
                $meeting->updateMeetingUrlStart();
            }


            $info = Error::getUserDeviceInfo();

            $data = $info;

            $data['status_id'] = $status->id;
            $data['started_at'] = now();

            $meeting->update($data);

            if (!$usuario instanceof User) {
                $data_attendant = $info;
                $data_attendant['first_attempt_at'] = now();

                Attendant::where('meeting_id', $meeting->id)->where('usuario_id', $usuario->id)->update($data_attendant);
            }

            return $meeting->url_start;
        }

        if ($usuario instanceof User) return $meeting->url;

        return $meeting->attendants()->where('usuario_id', $usuario->id)->first()->link ?? $meeting->url;
    }

    public function join()
    {
        $meeting = $this;
        $usuario = auth()->user();

        $attendant = Attendant::where('meeting_id', $meeting->id)->where('usuario_id', $usuario->id)->first();

        $data = Error::getUserDeviceInfo();

        if (!$attendant->first_attempt_at) {
            $data['first_attempt_at'] = now();
        }

        $attendant->update($data);

        return true;
    }

    public function checkIfDataIsReady()
    {
        return $this->status->code == 'finished' AND $this->report_generated_at > $this->finished_at;
    }

    // public function setAttendantInfo()
    // {
    //     $data = Error::getUserDeviceInfo();

    //     $this->update($data);

    //     return true;
    // }

}
