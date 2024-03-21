<?php

namespace App\Http\Controllers\Induction\ApiRest;

use App\Http\Controllers\Controller;
use App\Http\Resources\Induccion\SupervisorProcessesResource;
use App\Models\Benefit;
use App\Models\CheckList;
use App\Models\ChecklistRpta;
use App\Models\ChecklistRptaItem;
use App\Models\EntrenadorUsuario;
use App\Models\Process;
use App\Models\ProcessInstructor;
use App\Models\ProcessSummaryUser;
use App\Models\ProcessUserAttendance;
use App\Models\Speaker;
use App\Models\Taxonomy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestProcessController extends Controller
{

    public function getProcess(Process $process)
    {
        $user = Auth::user();
        $data = [
            'process' => $process?->id,
            'user' => $user
        ];
        $apiResponse = Process::getUserProcessApi($data);

        return response()->json($apiResponse, 200);
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
            'stages' => '0/5',
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

        $process_user = Process::with(['instructions','stages.activities.type','stages.activities.requirement'])
                            ->whereIn('id', $process_assigned)
                            ->first();

        $process_sum_user = ProcessSummaryUser::where('process_id', $process_user->id)
                                            ->where('user_id', $user->id)
                                            ->first();
        $response['data'] = [
            'id' => $user->id,
            'fullname' => $user->fullname,
            'document' => $user->document ?? 'Sin documento',
            'module' => $user->resource->subworkspace?->name ?? 'No module',
            'active' => $user->active,
            'limit_absences' => $process_user?->absences ?? 0,
            'count_absences' => $process_sum_user?->absences ?? 0,
            'percentage' => 0,
            'process' => [
                'id' => $process_user?->id,
                'title' => $process_user?->title,
            ],
            'stages' => $process_user?->stages ?? null
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
