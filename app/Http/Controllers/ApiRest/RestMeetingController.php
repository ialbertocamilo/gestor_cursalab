<?php

namespace App\Http\Controllers\ApiRest;

use stdClass;
use Carbon\Carbon;
use App\Models\Error;
use App\Models\Meeting;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Taxonomy;
use App\Models\Attendant;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Models\CriterionValue;
use App\Http\Controllers\Controller;
use App\Http\Requests\AppErrorRequest;
use App\Http\Resources\MeetingResource;
use App\Http\Requests\MeetingAppRequest;
use App\Http\Resources\MeetingAppResource;
use App\Http\Requests\Meeting\MeetingSearchAttendantRequest;
use App\Http\Resources\Meeting\MeetingSearchAttendantsResource;
use App\Http\Requests\Meeting\MeetingAppUploadAttendantsRequest;
use App\Http\Requests\Meeting\MeetingAppUploadhAttendantsRequest;

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
        $result = Meeting::getListMeetingsByUser($request);
        return $this->successApp($result);
    }

    public function getData(Request $request)
    {
        $data = Meeting::getAppData();
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
        // info(__function__);
        $data = $request->all();
        Meeting::finalizeWebhook($data, 'zoom');
    }

    public function getFormData(Request $request)
    {
        $subworkspace = auth()->user()->subworkspace;
        $modulos = Workspace::loadSubWorkspacesSiblings($subworkspace, ['id','name as nombre']);
        $user_types = Taxonomy::getData('meeting', 'user')->pluck('code', 'id');

        $params = ['config_id' => $request->config_id ?? auth()->user()->config_id];
        $grupos = CriterionValue::query()
        ->whereRelation('criterion', 'code', 'grupo')
        ->whereRelation('workspaces', 'id', $subworkspace->parent_id)
        ->where('active', ACTIVE)
        ->select('id', "value_text as nombre")
        ->get();
        
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
