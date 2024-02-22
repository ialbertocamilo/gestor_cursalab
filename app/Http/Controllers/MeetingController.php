<?php

namespace App\Http\Controllers;

use App\Models\Benefit;
use App\Models\CriterionWorkspace;
use App\Models\Meeting;
use App\Models\Usuario;
use App\Models\Taxonomy;
use App\Models\Attendant;
use App\Models\Criterion;
use App\Models\Workspace;
use Illuminate\Http\Request;
use App\Models\BenefitProperty;
use App\Models\SourceMultimarca;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\MeetingRequest;
use App\Exports\Meeting\MeetingExport;
use App\Http\Resources\MeetingResource;
use App\Http\Requests\MeetingAppRequest;
use App\Http\Resources\MeetingAppResource;
use App\Http\Requests\MeetingFinishRequest;
use App\Exports\Meeting\GeneralMeetingsExport;
use App\Http\Requests\Meeting\MeetingSearchAttendantRequest;
use App\Http\Resources\Meeting\MeetingSearchAttendantsResource;
use App\Http\Requests\Meeting\MeetingUploadAttendantsFormRequest;

class MeetingController extends Controller
{
    public function search(Request $request)
    {
        // $request->merge(['usuario_id' => 436]);
        $request->include_topic_meetings = true;
        $meetings = Meeting::search($request);
        MeetingResource::collection($meetings);

        return $this->success($meetings);
    }

    public function getListSelects()
    {
        $types = Taxonomy::getSelectData('meeting', 'type');
        $statuses = Taxonomy::getSelectData('meeting', 'status');

        return $this->success(get_defined_vars());
    }

    /**
     * Process request to load modules and groups
     *
     * @param Request $request
     * @return JsonResponse
     */
    /*public function getSelectSearchFilters(Request $request)
    {
        $modulos = Criterion::getValuesForSelect('module');
        $grupos = Criterion::getValuesForSelect('group');

        return $this->success(compact('grupos', 'modulos'));
    }*/
    public function getSelectSearchFilters()
    {
        $modulos = Workspace::loadSubWorkspaces(['id','name as nombre']);
        return $this->success(compact('modulos'));
    }

    /**
     * Process request to load data for form selects
     *
     * @param false $compactResponse
     * @return array|JsonResponse
     */
     public function getFormSelects($compactResponse = false)
    {

        $default_meeting_type = Taxonomy::getFirstData('meeting', 'type', 'room');
        $user_types = Taxonomy::getSelectData('meeting', 'user');
        $types = Taxonomy::getSelectData('meeting', 'type');
        $default_meeting_type = Taxonomy::getFirstData('meeting', 'type', 'room');
        //don't include benefit type if you don't have a benefit activated
        if(
            !Benefit::where('workspace_id',get_current_workspace()->id)->where('active',ACTIVE)
            ->whereHas('type', fn($q) => $q->whereIn('code', ['sesion_online','sesion_hibrida']))
            ->first()
        ){
            $types = collect($types)->filter(function ($type) {
                return $type['code'] != 'benefits';
            });
        }
        $hosts = Usuario::getCurrentHosts();

        $response = compact('types', 'hosts', 'user_types', 'default_meeting_type');

        return $compactResponse ? $response : $this->success($response);
    }

    /**
     * Process request to create a new meeting
     *
     * @param MeetingRequest $request
     * @return JsonResponse
     */
    public function store(MeetingRequest $request)
    {
        $meeting = Meeting::storeRequest($request->validated());
        return $this->success(['msg' => 'Reunión creada correctamente.']);
                               // 'status' => $meeting->buildPrefix() ]);
    }

    public function getDuplicatedData(Meeting $meeting)
    {
        // $meeting->load('type', 'host.config');
        $meeting->load('type', 'host');

        extract(
            $this->getFormSelects(true), EXTR_OVERWRITE
        );

        $duplicate = [
            'type' => $meeting->type,
            'host' => $meeting->host,
            'attendants' => Attendant::getMeetingAttendantsForMeeting($meeting),
            'description' => $meeting->description,
        ];

        return $this->success(
            compact(
                'types',
                'user_types',
                'hosts',
                'duplicate'
            )
        );
    }

    public function edit(Meeting $meeting)
    {
        $meeting->load(
            'type',
//            'host.config',
            'host',
            'status'
        );

        $meeting->attendants = Attendant::getMeetingAttendantsForMeeting($meeting);

        $meeting->setDateAndTimeToForm();
        $benefits = [];
        $silabos = [];
        if($meeting->model_type == 'App\Models\BenefitProperty'){
            $benefits  = Benefit::select('id','title')->where('workspace_id',get_current_workspace()->id)
            ->whereHas('type', fn($q) => $q->whereIn('code', ['sesion_online','sesion_hibrida']))
            ->get();
            $silabo_selected = BenefitProperty::find($meeting->model_id);
            $silabos =  BenefitProperty::where('benefit_id',$silabo_selected->benefit_id)->where('type_id',$silabo_selected->type_id)->get();
            $meeting->model_id = $silabo_selected;
            $meeting->benefit = $benefits->where('id',$silabo_selected->benefit->id)->first();
        }
        extract($this->getFormSelects(true), EXTR_OVERWRITE);

        return $this->success(get_defined_vars());
    }

    public function update(Meeting $meeting, MeetingRequest $request)
    {
        Meeting::storeRequest($request->validated(), $meeting);

        return $this->success(['msg' => 'Reunión actualizada correctamente.']);
    }

    /**
     * Process request to load meeting details
     *
     * @param Meeting $meeting
     * @return JsonResponse
     */
    public function show(Meeting $meeting)
    {
        $meeting->load(
            'type',
            'host',
            //'host.config', // user is no longer related to meeting
            'status',
            'user'
        );

        $meeting->attendants = Attendant::getMeetingAttendantsForMeeting($meeting);
        $meeting->attendants_count = $meeting->attendants()->count();
        $meeting->real_attendants_count = $meeting
                                            ->attendants()
                                            ->whereNotNull('first_login_at')
                                            ->count();

        $meeting->real_percentage_attendees = $meeting->getRealPercetageOfAttendees();
        $meeting->download_ready = $meeting->checkIfDataIsReady();
        $meeting->getDatesFormatted();
        $meeting->prefix = $meeting->buildPrefix();
        $meeting->isMasterOrAdminCursalab = auth()->user()->isMasterOrAdminCursalab();


        return $this->success(get_defined_vars());
    }

    public function start(Meeting $meeting)
    {
        $url = $meeting->start();

        return $this->success(['msg' => 'Se inició la reunión correctamente.', 'url' => $url]);
    }

    public function join(Meeting $meeting)
    {
        $meeting->join();

        return $this->success(['msg' => 'Se unió a la reunión correctamente.']);
    }

    public function finish(Meeting $meeting, MeetingFinishRequest $request)
    {
        $meeting->finalize();

        return $this->success(['msg' => 'Se finalizó la reunión correctamente.']);
    }

    /**
     * Process request to cancel meeting
     *
     * @param Meeting $meeting
     * @return JsonResponse
     */
    public function cancel(Meeting $meeting)
    {
        $meeting->cancel();

        return $this->success(['msg' => 'Se canceló la reunión correctamente.']);
    }


    /**
     * Process request to delete record
     *
     * @param Meeting $meeting
     * @return JsonResponse
     */
    public function destroy(Meeting $meeting)
    {
        SourceMultimarca::destroySource(
            'meeting', $meeting->id, $meeting->identifier
        );

        $meeting->delete();

        return $this->success(['msg' => 'Se eliminó la reunión correctamente.']);
    }

    public function uploadAttendants(MeetingSearchAttendantRequest $request)
    {
        $data = $request->validated();
        $attendants = [];
        $data['usuarios_dni'] = Attendant::getUsuariosDniFromExcel($data);

        if (count($data['usuarios_dni']) > 0) {
            $attendants = Attendant::searchAttendants($data);
            $attendants = MeetingSearchAttendantsResource::collection($attendants); /*mutando datos*/
            Attendant::filterEmptyMeetingInvitations($attendants);
        }

        return $this->success(compact('attendants'));
    }

    // public function exportAllUserMeetings()
    // {
    //     $creador = auth()->user();

    //     $filtros = ['creador_id' => $creador->id];
    //     $eventos_export = new EventosExport($filtros);
    //     $eventos_export->view();
    //     $random = rand(0, 10000);
    //     $date = date('mdY');
    //     ob_end_clean();
    //     ob_start();
    //     return Excel::download($eventos_export, "Eventos_" . $creador->dni . "_" . $date . "_" . $random . ".xlsx");
    // }

    public function exportMeetingReport(Meeting $meeting)
    {
        $meeting->load('user', 'host.config', 'status', 'type', 'attendants');

        $date = date('Y-m-d');
        $random = date('His');
        $file_name = "Reporte-de-reunión-{$meeting->id}-{$date}--{$random}.xlsx";

        $data = [
            'meeting' => $meeting
        ];

        ob_end_clean();
        ob_start();

        return Excel::download(new MeetingExport($data), $file_name);
    }

    public function exportGeneralMeetingsReport(Request $request)
    {
        $data = $request->all();

        $date = date('Y-m-d');
        $random = date('His');
        $file_name = "Reporte-general-de-reuniones-{$date}--{$random}.xlsx";

        $data = [
            'starts_at' => $data['starts_at'] ?? null,
            'finishes_at' => $data['finishes_at'] ?? null,
        ];

        ob_end_clean();
        ob_start();

        return Excel::download(new GeneralMeetingsExport($data), $file_name);
    }

    public function exportAttendants(Meeting $meeting)
    {

    }

    /**
     * Process request to search attendants
     *
     * @param MeetingSearchAttendantRequest $request
     * @return JsonResponse
     */
    public function searchAttendants(MeetingSearchAttendantRequest $request)
    {
        $filters = $request->validated();
        $attendants = Attendant::searchAttendants($filters);
        $attendants = MeetingSearchAttendantsResource::collection($attendants);
        Attendant::filterEmptyMeetingInvitations($attendants); /* mutando resultados */

        return $this->success(compact('attendants'));
    }

    public function getMeetingStats(Meeting $meeting)
    {
        $meeting->getStats();
        $stats = $meeting->stats;
        $isMasterOrAdminCursalab = auth()->user()->isMasterOrAdminCursalab();

        return $this->success(compact('stats', 'isMasterOrAdminCursalab'));
    }

    public function updateUrlStart(Meeting $meeting)
    {
        $meeting->updateMeetingUrlStart();
        $meeting->refresh();
        $url_start = $meeting->url_start;

        return $this->success(compact('url_start'));
    }
    public function updateAttendanceData(Meeting $meeting)
    {
        $meeting->updateAttendantsData();

        $attendants = Attendant::getMeetingAttendantsForMeeting($meeting);
        $isMasterOrAdminCursalab = auth()->user()->isMasterOrAdminCursalab();

        return $this->success(compact('attendants', 'isMasterOrAdminCursalab'));
    }
}
