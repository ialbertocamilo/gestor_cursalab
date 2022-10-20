<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class CheckRol

{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param   string $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$checkrol)
    {
        $user = auth()->user();
        $access = false;

        // === check workpsace only to 25 - Farmacias Peruanas ===
        $workspace_id = session('workspace')['id'];
        if($workspace_id !== 25 and $request->is('glosario'))
            Redirect::to('welcome')->send();
        // === check workpsace only to 25 - Farmacias Peruanas ===

        if (!$user->isAn('super-user')) {
            foreach ($checkrol as $rol) {
                if ($user->isAn($rol)) {
                    $access = true;
                }
            }
            if (!$access) {
                if ($request->wantsJson())
                {
                    return abort(403);
                }

                Redirect::to('welcome')->send();
            }
        }
        return $next($request);
    }
}
