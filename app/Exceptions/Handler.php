<?php

namespace App\Exceptions;

use App\Breadly\Components\JsonOutput;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     *
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($request->route() && stripos(strtolower($request->route()->uri()), 'api/') === 0) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    public function handleApiException($request, Exception $exception)
    {
        \Log::error($exception);
        if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
            $response = JsonOutput::httpResponse("Token expired.", 401);
        } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
            $response = JsonOutput::httpResponse("Invalid token.", 401);
        } else if ($exception instanceof \Tymon\JWTAuth\Exceptions\JWTException) {
            $response = JsonOutput::httpResponse("Authentication error.", 401);
        } else if ($exception instanceof ModelNotFoundException) {
            $response = JsonOutput::httpResponse($exception->getMessage(), 404);
        } elseif ($exception instanceof ValidationException) {
            $errorMessages = $exception->validator->errors()->all();
            $response = JsonOutput::httpResponse($errorMessages[0], 422);
        } else {
            $response = JsonOutput::httpResponse($exception->getMessage(), 500);
        }

        return $response;
    }
}
