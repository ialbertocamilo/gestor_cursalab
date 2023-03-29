<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\School;

class CheckSchoolWorkspace

{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param   string $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $school_id = $request->segment(2);

        $access = false;
        $workspace = get_current_workspace();

        if ($workspace)
        {
            $subworkspaces_id = $workspace->subworkspaces->pluck('id');

            $schools = School::whereHas('subworkspaces', function($q) use ($subworkspaces_id) {
                $q->whereIn('subworkspace_id', $subworkspaces_id);
            })
            ->get();
            
            // $workspace->load('schools');

            $school = $schools->where('id', $school_id)->first();

            if ($school)  $access = true;
        }

        if (!$access) {
            
            if ($request->wantsJson()) return abort(403);

            Redirect::to('welcome')->send();
        }

        return $next($request);
    }
}
