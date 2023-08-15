<?php

namespace App\Http\Middleware;

use DB;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestartSessionLimit

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
        $token = auth()->user()->token();

        DB::table('oauth_access_tokens')
            ->where('id', $token->id)
            ->update(['updated_at' => now()]);

        return $next($request);
    }
}
