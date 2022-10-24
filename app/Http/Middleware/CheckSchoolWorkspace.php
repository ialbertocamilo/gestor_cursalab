<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

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
        // $user = auth()->user();

        // info($request->segment(0));
        // info($request->segment(1));
        $school_id = $request->segment(2);

        // $school = School::

        $access = false;
        $workspace = get_current_workspace();

        info('W => ' . $workspace->id);
        info('S => ' . $school_id);

        if ($workspace)
        {
            $workspace->load('schools');

            $school = $workspace->schools->where('id', $school_id)->first();

            info($school);

            if ($school)  $access = true;

            // $schools_id = $workspace->schools->pluck('id')
            // schools
        }

        if (!$access) {
            
            if ($request->wantsJson()) return abort(403);

            Redirect::to('welcome')->send();
        }

        return $next($request);
    }
}
