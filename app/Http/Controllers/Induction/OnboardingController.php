<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
use App\Http\Resources\Induccion\DashboardSupervisorsResource;
use App\Models\Process;
use App\Models\ProcessInstructor;
use App\Models\Taxonomy;
use App\Models\User;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing([
            'workspace_id' => $workspace?->id
        ]);

        $process_ids = Process::where('workspace_id', $workspace?->id)->pluck('id')->toArray();
        $instructors = User::select('id', 'name', 'lastname', 'surname')
                            ->whereHas('processes', function($e) use ($process_ids){
                                $e->whereIn('process_id', $process_ids);
                            })
                            ->paginate(15);
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
        $users_active = User::where('type_id', $type_employee_onboarding->id)->where('active', 1)->count();

        $response = compact('process_total', 'process_progress', 'users_active', 'processes');
        return $this->success($response);
    }
}
