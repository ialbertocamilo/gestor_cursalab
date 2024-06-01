<?php

namespace App\Http\Controllers\Induction\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Induccion\SupervisorProcessesResource;
use App\Models\Activity;
use App\Models\Benefit;
use App\Models\CheckList;
use App\Models\ChecklistRpta;
use App\Models\ChecklistRptaItem;
use App\Models\EntrenadorUsuario;
use App\Models\Internship;
use App\Models\InternshipUser;
use App\Models\Post;
use App\Models\Process;
use App\Models\ProcessInstructor;
use App\Models\ProcessSummaryUser;
use App\Models\ProcessUserAttendance;
use App\Models\Speaker;
use App\Models\Stage;
use App\Models\Taxonomy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RestProcessController extends Controller
{

    public function getFaqs()
    {
        $tax_id = Taxonomy::where('type', 'section')->where('code', 'faq')->first('id');
        $platform_id = Taxonomy::getFirstData('project', 'platform', 'onboarding');
        $preguntas = Post::where('section_id', $tax_id->id)->where('platform_id_onb', $platform_id?->id)->get();

        return $this->success($preguntas);
    }

    public function getPasantiasAsignadas()
    {
        $user = Auth::user();
        $data = [
            'user' => $user
        ];

        $internship_ids = [];
        $internships = Internship::where('active', true)->get();
        if($internships)
        {
            foreach($internships as $internship)
            {
                $leaders = $internship->leaders ? json_decode($internship->leaders) : null;
                if(is_array($leaders))
                {
                    foreach ($leaders as $leader)
                    {
                        if($user->id == $leader) {
                            array_push($internship_ids, $internship->id);
                        }
                    }
                }
            }
        }
        $internship_ids = array_unique($internship_ids);

        $stages_ids = Activity::whereIn('model_id', $internship_ids)->where('model_type', Internship::class)->pluck('stage_id')->toArray();
        $stages_ids = array_unique($stages_ids);

        $processes_ids = Stage::whereIn('id', $stages_ids)->pluck('process_id')->toArray();
        $processes_ids = array_unique($processes_ids);

        $processes = Process::whereIn('id', $processes_ids)->select('id', 'workspace_id', 'title', 'active', 'starts_at', 'finishes_at')->paginate(10);

        SupervisorProcessesResource::collection($processes);

        $response = [
            'processes' => $processes
        ];
        return $this->success($response);
    }

    public function getPasantiaAsignada(Process $process)
    {
        $user = Auth::user();
        $data = [
            'user' => $user
        ];
        $page = null;

        $process_data = Process::select('id','title', 'description', 'active')
                    ->where('id', $process->id)
                    ->first();

        if($process)
        {
            $stages_ids = Stage::where('process_id', $process->id)->pluck('id')->toArray();
            $internship_ids = Activity::whereIn('stage_id', $stages_ids)->where('model_type', Internship::class)->pluck('model_id')->toArray();

            $users = InternshipUser::whereIn('internship_id', $internship_ids)->where('leader_id', $user->id)->select('id', 'internship_id', 'user_id', 'status_id')->get();
            $students = [];
            if($users) {
                foreach ($users as $uss) {

                    $student = User::where('id', $uss->user_id)->select('id', 'name', 'lastname', 'surname', 'fullname', 'email', 'email_gestor')->first();
                    if($student) {

                        $student->status = $uss->status?->code;
                        $student->internship_id = $uss->internship_id;
                        $student->user_id = $uss->user_id;
                        $student->leader_id = $user->id;
                        array_push($students, $student);
                    }
                }
            }
            $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
            $students = $students instanceof Collection ? $students : Collection::make($students);

            $process_data->students = new LengthAwarePaginator($students->forPage($page, 10), $students->count(), 10, $page, []);

            $process_data->percentage = 0;
        }

        return ['data'=> $process_data];
    }
    public function getPasantiaUserInfo(Request $request)
    {
        $user = Auth::user();
        $internship_id = $request->internship_id;
        $leader_id = $user->id;
        $user_id = $request->user_id;
        $internship_user = InternshipUser::where('internship_id', $internship_id)->where('leader_id', $leader_id)->where('user_id', $user_id)->first();
        if($internship_user)
        {
            if($internship_user->meeting_date_1)
                $internship_user->meeting_date_1 = date('Y-m-d', strtotime($internship_user->meeting_date_1));
            if($internship_user->meeting_date_2)
                $internship_user->meeting_date_2 = date('Y-m-d', strtotime($internship_user->meeting_date_2));
            if($internship_user->meeting_time_1)
                $internship_user->meeting_time_1 = date('H:i', strtotime($internship_user->meeting_time_1));
            if($internship_user->meeting_time_2)
                $internship_user->meeting_time_2 = date('H:i', strtotime($internship_user->meeting_time_2));

            $internship_user->full_name = $user->fullname;
            $internship_user->document = $user->document ?? 'Sin documento';
        }

        return $this->success($internship_user);
    }
    public function savePasantiaUserInfo(Request $request)
    {
        $user = Auth::user();

        $internship_id = $request->internship_id;
        $leader_id = $user->id;
        $user_id = $request->user_id;
        $meeting_date_1 = $request->meeting_date_1;
        $meeting_time_1 = $request->meeting_time_1;

        $status = Taxonomy::getFirstData('internship', 'status', 'confirmed');

        $internship_user = InternshipUser::where('internship_id', $internship_id)->where('leader_id', $leader_id)->where('user_id', $user_id)->first();

        if($internship_user) {
            $internship_user->status_id = $status?->id;

            if($meeting_date_1)
                $internship_user->meeting_date_1 = $meeting_date_1;

            if($meeting_time_1)
                $internship_user->meeting_time_1 = $meeting_time_1;

            $internship_user->save();
        }

        return $this->success($internship_user);
    }

    public function getProcess(Process $process)
    {
        $user = Auth::user();
        $data = [
            'process' => $process?->id,
            'user' => $user
        ];
        $apiResponse = Process::getUserProcessApi($data);

        return $this->success($apiResponse);
    }

    public function getUserProcess(Process $process)
    {
        $user = Auth::user();
        $data = [
            'process' => $process?->id,
            'user' => $user
        ];
        $apiResponse = Process::getUserProcessApi($data);

        return $this->success($apiResponse);
    }

    public function getUserProcessInstructions( Process $process )
    {
        $apiResponse = Process::getUserProcessOnlyInstructionsApi($process?->id);

        return $this->success($apiResponse);
    }

    public function saveUserProcessInstructions( Process $process, Request $request )
    {
        $user = Auth::user();
        $data = [
            'process' => $process?->id,
            'user' => $user
        ];
        $status = Taxonomy::getFirstData('user-process', 'status', 'in-progress');

        $user_summary = $user->summary_process()->where('process_id', $process->id)->first();

        if($user_summary) {
            $user_summary->status_id = $status?->id;
            $user_summary->completed_instruction = true;
            if(is_null($user_summary->first_entry))
                $user_summary->first_entry = now()->format('y-m-d H:i:s');
            $user_summary->save();
        }
        else {
            $data = [
                'user_id' => $user->id,
                'process_id' => $process->id,
                'status_id' => $status?->id,
                'progress' => 0,
                'absences' => 0,
                'completed_instruction' => true,
                'first_entry' => now()->format('y-m-d H:i:s')
            ];
            ProcessSummaryUser::create($data);
        }

        $apiResponse['error'] = false;
        $apiResponse['message'] = 'La información del usuario se actualizó correctamente.';

        return $this->success($apiResponse);
    }

    public function getSupervisorProcesses()
    {
        $user = Auth::user();
        $data = [
            'user' => $user ?? null,
        ];
        $apiResponse = Process::getSupervisorProcessesApi($data);

        SupervisorProcessesResource::collection($apiResponse);

        // return response()->json($apiResponse, 200);
        $response = [
            'progress' => 0,
            'stages' => '0/'.$apiResponse->total(),
            'processes' => $apiResponse
        ];
        return $this->success($response);
    }

    public function getSupervisorProcess(Process $process)
    {
        $user = Auth::user();
        $data = [
            'process' => $process?->id,
            'user' => $user
        ];
        $apiResponse = Process::getSupervisorProcessApi($data);

        return response()->json($apiResponse, 200);
    }

    public function getSupervisorProcessOnlyStudents(Process $process)
    {
        $user = Auth::user();
        $data = [
            'process' => $process?->id,
            'user' => $user
        ];
        $apiResponse = Process::getSupervisorProcessOnlyStudentsApi($data);

        return response()->json($apiResponse, 200);
    }

    public function getSupervisorProcessOnlySupervisors(Process $process)
    {
        $user = Auth::user();
        $data = [
            'process' => $process?->id,
            'user' => $user
        ];
        $apiResponse = Process::getSupervisorProcessOnlySupervisorsApi($data);

        return response()->json($apiResponse, 200);
    }

    public function getInfoStudent(User $user)
    {
        $process_assigned = Process::getProcessesAssigned($user);

        $process_user = Process::select('id', 'title')
                            ->whereIn('id', $process_assigned)
                            ->first();

        $summary_user_process = Process::getUserProcessApi(['user' => $user, 'process' => $process_user->id]);

        $response['data'] = [
            'id' => $user->id,
            'fullname' => $user->fullname,
            'document' => $user->document ?? 'Sin documento',
            'module' => $user->resource->subworkspace?->name ?? 'No module',
            'active' => $user->active,
            'count_absences' => $summary_user_process->count_absences,
            'user_absences' => $summary_user_process->user_absences,
            'user_activities_progress' => $summary_user_process->user_activities_progress,
            'user_activities_total' => $summary_user_process->user_activities_total,
            'user_activities_progressbar' => $summary_user_process->user_activities_progressbar,
            'process' => [
                'id' => $process_user?->id,
                'title' => $process_user?->title,
            ],
            'stages' => $summary_user_process->stages
        ];

        return response()->json($response, 200);
    }

    public function saveAttendance( Process $process, Request $request )
    {
        $instructor = Auth::user();
        $type_attendance = Taxonomy::getFirstData('processes', 'attendance', $request->status_id);

        $data_search = [
            'process_id' => $process->id,
            'user_id' => $request?->user_id ?? null,
            'registered' => $request?->registered ?? null,
        ];

        $data_save = [
            'process_id' => $process->id,
            'user_id' => $request?->user_id ?? null,
            'activity_id' => $request?->activity_id ?? null,
            'instructor_id' => $instructor->id ?? null,
            'status_id' => $type_attendance?->id,
            'registered' => $request?->registered ?? null,
            'justification' => $request?->justification ?? null,
        ];

        $user_attendance = ProcessUserAttendance::updateOrCreate($data_search, $data_save);

        $falta_injustificada = Taxonomy::getFirstData('processes', 'attendance', 'falta_injustificada');
        $absences = ProcessUserAttendance::where('user_id', $request?->user_id)
                                            ->where('process_id', $process->id)
                                            ->where('status_id', $falta_injustificada->id)
                                            ->count();

        ProcessSummaryUser::updateOrCreate([
            'user_id' => $request?->user_id,
            'process_id' => $process->id
        ],[
            'absences' => $absences
        ]);

        $response['absences'] = $absences ?? 0;

        return response()->json($response, 200);
    }

    public function saveAttendanceMassive( Request $request )
    {
        $instructor = Auth::user();
        $type_attendance = Taxonomy::getFirstData('processes', 'attendance', $request->status_id);

        if(is_array($request?->users) && count($request?->users) > 0)
        {
            foreach ($request?->users as $user) {
                $data_search = [
                    'process_id' => $request?->process_id,
                    'user_id' => $user,
                    'registered' => $request?->registered ?? null,
                ];
                $data_save = [
                    'process_id' => $request?->process_id,
                    'user_id' => $user,
                    'activity_id' => $request?->activity_id ?? null,
                    'instructor_id' => $instructor->id ?? null,
                    'status_id' => $type_attendance?->id,
                    'registered' => $request?->registered ?? null,
                    'justification' => $request?->justification ?? null,
                ];

                $user_attendance = ProcessUserAttendance::updateOrCreate($data_search, $data_save);

                $falta_injustificada = Taxonomy::getFirstData('processes', 'attendance', 'falta_injustificada');
                $absences = ProcessUserAttendance::where('user_id', $user)
                                                    ->where('process_id', $request?->process_id)
                                                    ->where('status_id', $falta_injustificada->id)
                                                    ->count();

                ProcessSummaryUser::updateOrCreate([
                    'user_id' => $user,
                    'process_id' => $request?->process_id
                ],[
                    'absences' => $absences
                ]);
            }
        }

        $response['success'] = true;

        return response()->json($response, 200);
    }

    public function getUsersAbsencesMassive(Process $process)
    {
        $user = Auth::user();
        $user->load('processes');
        $processes = $user->processes()->select('id', 'title')->get();
        if($processes) {
            foreach ($processes as $process) {
                $assistants = Process::getProcessAssistantsList($process,false);
                $process->assistants = $assistants;
            }
        }
        // ProcessAssistantsSearchResource::collection($assistants)
        //                                 ->map(function($i) use ($process) {
        //                                     $i->process = $process?->id;
        //                                     $i->limit_absences = $process?->absences ?? 0;
        //                                 });
        return $this->success($processes);
    }
}
