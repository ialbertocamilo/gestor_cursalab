<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Taxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class hasHability
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next,$section)
    {
        $user = auth()->user();
        $access = false;

        // === check workpsace only to 25 - Farmacias Peruanas ===
        $workspace_id = session('workspace')['id'] ?? null;
        if($workspace_id == 25 and $request->is('glosario')){
            return $next($request);
        }
        // === check workpsace only to 25 - Farmacias Peruanas ===
        if (!$user->isAn('super-user')) {
            $entity_id = Taxonomy::select('id')->where('group','gestor')->where('type','submenu')->where('code',$section)->first()?->id;
            if ($user->getAbilities()->where('entity_id',$entity_id)->first()) {
                $access = true;
            }
            if (!$access) {
                if ($request->wantsJson())
                {
                    return abort(403);
                }

                Redirect::to('home')->send();
            }
        }
        return $next($request);
    }
}
