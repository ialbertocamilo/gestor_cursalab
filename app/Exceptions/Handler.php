<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Exception;

use App\Error;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function report(Throwable $exception)
    {
        // Kill reporting if this is an "access denied" (code 9) OAuthServerException.
        if ($exception instanceof \League\OAuth2\Server\Exception\OAuthServerException && $exception->getCode() == 9) {
            return;
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     */
    // public function render($request, Exception $exception)
    // {
    //     Error::storeAndNotificateException($exception, $request);

    //     if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
    //         return response()->json(['error' => 'token is expired'], 400);
    //     } elseif ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
    //         return response()->json(['error' => 'token is invalid'], 400);
    //     } elseif ($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
    //         return response()->json(['error' => 'token absent'], 400);
    //     }

    //     // if ($request->wantsJson()) {
    //     //     // return $this->prepareJsonResponse($request, $exception->getMessage());
    //     //     return response()->json(['error' => $exception->getMessage()], 422);
    //     // }

    //     return parent::render($request, $exception);
    // }
}
