<?php

namespace App\Http\Controllers\Induction;

use App\Http\Controllers\Controller;
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

        $data = collect();


        return $this->success($data);
    }

    public function info(Request $request)
    {
        $workspace = get_current_workspace();
        $request->mergeIfMissing([
            'workspace_id' => $workspace?->id
        ]);
        $type_employee_onboarding = Taxonomy::getFirstData('user','type', 'employee_onboarding');
        $now = date('Y-m-d');

        $process_total = Process::where('workspace_id', $workspace?->id)->count();
        $process_progress = Process::where('workspace_id', $workspace?->id)->whereDate('starts_at', '<', $now)->active()->count();
        $users_active = User::where('type_id', $type_employee_onboarding->id)->where('active', 1)->count();
        // $instructors = Process::with(['instructors'])->where('workspace_id', $workspace?->id)->get();
        // $instructors = ProcessInstructor::with(['instructors'])->where('workspace_id', $workspace?->id)->get();
        $process_ids = Process::where('workspace_id', $workspace?->id)->pluck('id')->toArray();
        // $process_instructos = ProcessInstructor::whereIn('process_id', $process_ids);
        $instructors = User::select('id', 'name')->with(['processes'])->whereHas('processes', function($e) use ($process_ids){
            $e->whereIn('process_id', $process_ids);
        });
        $instructors2 = User::select('id', 'name')->whereHas('processes', function($e) use ($process_ids){
            $e->whereIn('process_id', $process_ids);
        });

        $response = compact('process_total', 'process_progress', 'users_active', 'instructors', 'process_ids');
        return $this->success($response);
    }
}
