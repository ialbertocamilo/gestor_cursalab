<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
use App\Http\Resources\Induccion\DashboardSupervisorsResource;
use App\Models\Process;
use App\Models\ProcessInstructor;
use App\Models\ProcessSummaryUser;
use App\Models\SummaryUser;
use App\Models\Taxonomy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing(['workspace' => $workspace?->id]);
        $request->mergeIfMissing(['code' => 'supervise']);

        $data = User::query()
            ->whereHas('segments')
            ->withCount([
                'segments' => function ($q) {
                    $q->
                    whereRelation('code', 'code', 'user-supervise');
                },
            ])
            ->whereRelation('segments.code', 'code', 'user-supervise');

        $data->withWhereHas('subworkspace', function ($query) use ($request) {
            if ($request->subworkspace)
                $query->whereIn('id', $request->subworkspace);
            else
                $query->where('parent_id', $request->workspace);
        });

        if ($request->has('q'))
            $data->filterText($request->q);

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $instructors = $data->orderBy($field, $sort)->paginate($request->paginate);
        // $workspace = get_current_workspace();
        // $request->mergeIfMissing([
        //     'workspace_id' => $workspace?->id
        // ]);

        // $process_ids = Process::where('workspace_id', $workspace?->id)->pluck('id')->toArray();
        // $instructors = User::select('id', 'name', 'lastname', 'surname','subworkspace_id', 'document')
        //                     ->whereHas('processes', function($e) use ($process_ids){
        //                         $e->whereIn('process_id', $process_ids);
        //                     })
        //                     ->with(['subworkspace'])
        //                     ->paginate(15);
        DashboardSupervisorsResource::collection($instructors);
        return $this->success($instructors);
    }

    public function searchProcess(Process $process, Request $request)
    {
        $stages = $process->stages;
        foreach($stages as $stage) {
            $stage->percentage = rand(10,60);
        }
        return $this->success($stages);
    }

    public function info(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing([
            'workspace_id' => $workspace?->id
        ]);
        $type_employee_onboarding = Taxonomy::getFirstData('user','type', 'employee_onboarding');
        $now = date('Y-m-d');

        $processes = Process::where('workspace_id', $workspace?->id)->select('id', 'title')->get();

        $process_total = Process::where('workspace_id', $workspace?->id)->count();
        $process_progress = Process::where('workspace_id', $workspace?->id)->whereDate('starts_at', '<', $now)->active()->count();
        $process_bar = $process_total > 0 ? ($process_progress * 100) / $process_total : 0;

        $users_active = User::where('type_id', $type_employee_onboarding->id)->where('active', 1)->count();
        $users_total = User::where('type_id', $type_employee_onboarding->id)->count();
        $users_bar = $users_total > 0 ? ($users_active * 100) / $users_total : 0;

        $summary_process_in_months = ProcessSummaryUser::selectRaw(
            'SUM(CASE WHEN month(completed_process_date) = 1 THEN 1 ELSE 0 END) as ENE,
            SUM(CASE WHEN month(completed_process_date) = 2 THEN 1 ELSE 0 END) as FEB,
            SUM(CASE WHEN month(completed_process_date) = 3 THEN 1 ELSE 0 END) as MAR,
            SUM(CASE WHEN month(completed_process_date) = 4 THEN 1 ELSE 0 END) as ABR,
            SUM(CASE WHEN month(completed_process_date) = 5 THEN 1 ELSE 0 END) as MAY,
            SUM(CASE WHEN month(completed_process_date) = 6 THEN 1 ELSE 0 END) as JUN,
            SUM(CASE WHEN month(completed_process_date) = 7 THEN 1 ELSE 0 END) as JUL,
            SUM(CASE WHEN month(completed_process_date) = 8 THEN 1 ELSE 0 END) as AGO,
            SUM(CASE WHEN month(completed_process_date) = 9 THEN 1 ELSE 0 END) as SEP,
            SUM(CASE WHEN month(completed_process_date) = 10 THEN 1 ELSE 0 END) as OCT,
            SUM(CASE WHEN month(completed_process_date) = 11 THEN 1 ELSE 0 END) as NOV,
            SUM(CASE WHEN month(completed_process_date) = 12 THEN 1 ELSE 0 END) as DIC'
            )
            ->get()->toArray();

        $data_graphic_bars = count($summary_process_in_months) ? array_values($summary_process_in_months[0]) : [];

        $response = compact('process_total', 'process_progress', 'process_bar', 'users_active', 'processes', 'users_total', 'users_bar', 'data_graphic_bars');
        return $this->success($response);
    }
}
