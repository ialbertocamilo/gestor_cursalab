<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Redirect;
// use App\Models\Master\Customer;

class ValidateAdminCurrentSession

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
        $user = auth()->user();

        $expectsJson = $request->expectsJson();

        if (!$user->canAccessPlatform()) {

            // $user->token()->revoke();
            session()->invalidate();
            auth()->logout();
            
            if (! $expectsJson ) {
                return redirect('/login');
            }

            return response()->json(['error' => 'Platform service unavailable. User logged out'], 403);
        }

        if (!$user->active) {

            session()->invalidate();
            auth()->logout();
            // $user->token()->revoke();
            // $request->guard()->logout();
            // session()->invalidate();
            // session()->regenerateToken();

            if (! $expectsJson ) {
                return redirect('/login');
            }

            return response()->json(['error' => 'Admin inactive logged out'], 403);
        }

        return $next($request);
    }
}
