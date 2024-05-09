<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;
        $customer = strtoupper(config('app.customer.slug'));

        if(Session::has('init_2fa')) $guards = []; // guards a vacio
        if(Session::has('init_reset')) $guards = []; // guards a vacio

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                $email = Auth::user()->email_gestor;

                // Log logged user in Slack when is not a cursalab.io email

                if (strpos($email, 'cursalab.io') !== false) {

                } else {

                    if (config('slack.routes.demo')) {
                        $message = "[{$customer}] Cursalab 2.0";
                        $attachments = [
                            [
                                "color" => "#36a64f",
                                "text" => "El usuario con email: $email retornó a la plataforma"
                            ]
                        ];
                        messageToSlackByChannel($message,$attachments,config('slack.routes.demo'));
                    }
                }


                return redirect('/home');
            }
        }

        return $next($request);
    }
}
