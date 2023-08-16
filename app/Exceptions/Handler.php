<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


use Throwable;
use Exception;

use App\Models\Error;

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
    public function render($request, Throwable $exception)
    {
        Error::storeAndNotificateException($exception, $request);
        if($request->is('integrations*') && $this->isHttpException($exception)){
            switch ($exception->getStatusCode())
                {
                case 405:
                    return response()->json(['data' =>['message'=>'Method not allowed.']],405);
                break;
                case 404:
                    return response()->json(['data' =>['message'=>'Route not found.']],404);
                break;
            }
        }
        // Error 419 - Token mismatch
        // if ($exception instanceof TokenMismatchException) {
        //     return redirect('/');
        // }
        // Error 404 - Not found
        // if ($exception instanceof NotFoundHttpException) {
        //     return redirect('/'); // Redirigir a la página de inicio
        // }
        // if ($request->wantsJson()) {
        //     // Define the response
        //     $response = [
        //         'message' => 'Ha ocurrido un problema. Contáctate con el equipo de soporte.'
        //     ];
        //     // Default response of 400
        //     $status = 500;
        //     // If this exception is an instance of HttpException
        //     if ($this->isHttpException($exception)) {
        //         // Grab the HTTP status code from the Exception
        //         $status = $exception->getStatusCode();
        //     }
        //     // Return a JSON response with the response array and status code
        //     return response()->json($response, $status);
        // }
        // if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
        //     return response()->json(['error' => 'token is expired'], 400);
        // } elseif ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
        //     return response()->json(['error' => 'token is invalid'], 400);
        // } elseif ($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
        //     return response()->json(['error' => 'token absent'], 400);
        // }

        // if ($request->wantsJson()) {
        //     // return $this->prepareJsonResponse($request, $exception->getMessage());
        //     return response()->json(['error' => $exception->getMessage()], 422);
        // }

        return parent::render($request, $exception);
    }
}
