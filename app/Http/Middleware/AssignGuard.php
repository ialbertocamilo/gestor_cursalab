<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AssignGuard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next,$guard=null)
    {
        if($guard != null){
            auth()->shouldUse($guard);
            $token =trim(substr($request->header('authorization'), 6));
            try {
                $this->token = $token;
                $user = auth()->user();
                if(!$user){
                    return response()->json(['message'=>'Invalid token.'],401);
                }
            } catch (\TokenExpiredException $th) {
                return $this->returnError('401','Unaunthenticated user');
            } catch (\JWTException $th) {
                return $this->returnError('401','Invalid token.');
            }
        }
        return $next($request);
    }
}
