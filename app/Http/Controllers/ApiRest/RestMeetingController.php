<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Requests\AppErrorRequest;
use App\Http\Requests\MeetingAppRequest;
use App\Http\Requests\Meeting\MeetingAppUploadAttendantsRequest;
use App\Http\Requests\Meeting\MeetingAppUploadhAttendantsRequest;
use App\Http\Requests\Meeting\MeetingSearchAttendantRequest;
use App\Http\Resources\MeetingAppResource;
use App\Http\Resources\MeetingResource;
use App\Http\Resources\Meeting\MeetingSearchAttendantsResource;
use App\Models\Abconfig;
use App\Models\Attendant;
use App\Models\Criterio;
use App\Models\Error;
use App\Models\Meeting;
use App\Models\Taxonomy;
use App\Models\Workspace;
use Carbon\Carbon;
use Illuminate\Http\Request;
use stdClass;

class RestMeetingController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth')
    //          ->except(['zoomWebhookEndMeeting', 'finishMeeting']);

    //     // return auth()->shouldUse('api');
    // }

    public function listUserMeetings(Request $request)
    {
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
        MeetingAppResource::collection($meetings);

        info(__function__);

        $result = json_decode($meetings->toJson(), true);
        $result['data'] = collect($result['data'])->groupBy('key')->all();

        if (count($result['data']) === 0) $result['data'] = new stdClass();

        return $this->successApp($result);
    }

    public function getData(Request $request)
    {
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

        return $this->success($data);
    }

    public function startMeeting(Meeting $meeting)
    {
        $url = $meeting->start();

        return $this->success([
            'msg' => 'Se inició la reunión correctamente.',
            'url' => $url]);
    }

    public function joinMeeting(Meeting $meeting)
    {
        $response = $meeting->join();

        return $this->success(['msg' => 'Se unió a la reunión correctamente.']);
    }

    public function finishMeeting(Meeting $meeting)
    {
        $response = $meeting->finalize();

        return $this->success(['msg' => 'Se finalizó la reunión correctamente.']);
    }

    public function storeAppError(AppErrorRequest $request)
    {
        return Error::storeAndNotificateAppException($request->validated(), $request);
    }

    public function zoomWebhookEndMeeting(Request $request)
    {
        $data = $request->all();
        $response = Meeting::finalizeWebhook($data, 'zoom');
    }

    public function getFormData(Request $request)
    {
        $subworkspace = auth()->user()->subworkspace;
        $modulos = Workspace::loadSubWorkspacesSiblings($subworkspace, ['id','name as nombre']);
        $user_types = Taxonomy::getData('meeting', 'user')->pluck('code', 'id');

        $params = ['config_id' => $request->config_id ?? auth()->user()->config_id];
        $grupos = [];

        return $this->success(compact('grupos', 'modulos', 'user_types'));
    }

    public function store(MeetingAppRequest $request)
    {
        $data = $request->validated();

        # set workspace id
        $subworkspace = auth()->user()->subworkspace;
        $data['workspace_id'] = $subworkspace->parent_id;
        # set workspace id

        $meeting = Meeting::storeRequest($data);

        return $this->success(['msg' => 'Reunión creada correctamente',
                               'meeting' => ['code' => $meeting->buildPrefix()] ]);
                               // 'meeting' => new MeetingAppRequest($meeting)]);
    }

    public function update(Meeting $meeting, MeetingAppRequest $request)
    {
        $data = $request->validated();

        # set workspace id
        $subworkspace = auth()->user()->subworkspace;
        $data['workspace_id'] = $subworkspace->parent_id;
        # set workspace id

        Meeting::storeRequest($data, $meeting);

        return $this->success(['msg' => 'Reunión actualizada correctamente']);
    }

    public function edit(Meeting $meeting)
    {
        $meeting->load('type', 'host.config:id,name,logo', 'status');

        $meeting->attendants = Attendant::getMeetingAttendantsForMeeting($meeting);

        $meeting->setDateAndTimeToForm();

        return $this->success(get_defined_vars());
    }

    public function getDuplicatedData(Meeting $meeting)
    {
        // $meeting->load('type', 'host.config');
        $data = [
            // 'type' => $meeting->type,
            // 'host' => $meeting->host,
            'attendants' => Attendant::getMeetingAttendantsForMeeting($meeting),
            'description' => $meeting->description,
            // 'name' => $meeting->name,
            // 'duration' => $meeting->duration,
        ];

        return $this->success($data);
    }
    public function uploadAttendants(MeetingAppUploadAttendantsRequest $request)
    {
        $data = $request->validated();

        $cohost = Taxonomy::getFirstData('meeting', 'user', 'cohost');
        $normal = Taxonomy::getFirstData('meeting', 'user', 'normal');

        $workSpaceIndex = auth()->user()->subworkspace->parent_id;
        $request->merge(['cohost' => $cohost, 'normal' => $normal, 'workspace_id' => $workSpaceIndex]);

        # get subworkspaces ids
        $workspace = Workspace::find($workSpaceIndex);
        $data['config_id'] = $workspace->subworkspaces->pluck('id');
        # get subworkspaces ids

        $attendants = Attendant::searchAttendants($data);
        $attendants = MeetingSearchAttendantsResource::collection($attendants);

        Attendant::filterEmptyMeetingInvitations($attendants);

        return $this->success(compact('attendants'));
    }

    public function searchAttendants(MeetingSearchAttendantRequest $request)
    {
        $data = $request->validated();

        $cohost = Taxonomy::getFirstData('meeting', 'user', 'cohost');
        $normal = Taxonomy::getFirstData('meeting', 'user', 'normal');

        $workSpaceIndex = auth()->user()->subworkspace->parent_id;
        $request->merge(['cohost' => $cohost, 'normal' => $normal, 'workspace_id' => $workSpaceIndex]);

        # get subworkspaces ids
        $workspace = Workspace::find($workSpaceIndex);
        $data['config_id'] = $workspace->subworkspaces->pluck('id');
        # get subworkspaces ids

        $attendants = Attendant::searchAttendants($data);
        $attendants = MeetingSearchAttendantsResource::collection($attendants);

        Attendant::filterEmptyMeetingInvitations($attendants);

        return $this->success(compact('attendants'));
    }

    public function cancel(Meeting $meeting)
    {
        $meeting->cancel();

        return $this->success(['msg' => 'Se canceló la reunión correctamente.']);
    }


    public function destroy(Meeting $meeting)
    {
        $meeting->delete();

        return $this->success(['msg' => 'Se eliminó la reunión correctamente.']);
    }

}
