<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Exceptions\MissingScopeException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof MissingScopeException && $request->wantsJson()){
            return response()->json([
                'error' => 'Anda tidak memiliki akses pada halaman ini',
            ], 403);
        } else if ($exception instanceof AuthenticationException) {
            return response()->json(
                [
                    'status'  => false,
                    'data'    => [],
                    'message' => 'You are unauthenticated',
                    'error'   => $exception->getMessage(),
                ],
                401
            );
        } else if($exception instanceof ValidationException) {
            $error = array_values($exception->errors());
            for ($a = 0; $a < count($error); $a++) {
                for ($b = 0; $b < count($error[$a]);$b++) {
                    $err[] = $error[$a][$b];
                }
            }
            return response()->json([
                'data'	=> [],
                'status'    => false,
                'message'   => $exception->getMessage(),
                'errors'    => $err
            ]);
        } else {
            return response()->json([
                'data'	=> [],
                'message'   => $exception->getMessage(),
                'status'    => false
            ]);
        }
    }
}
