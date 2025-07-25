<?php

namespace App\Exceptions;

use App\CodeResponse;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        BusinessException::class
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
     * @param  Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Exception  $exception
     * @return Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'code' => CodeResponse::PARAM_VALUE_ILLEGAL[0],
                'errmsg' => CodeResponse::PARAM_VALUE_ILLEGAL[1],
            ]);
        }
        if ($exception instanceof BusinessException) {
            return response()->json([
                'errno' => $exception->getCode(),
                'errmsg' => $exception->getMessage(),
            ]);
        }
        return parent::render($request, $exception);
    }
}
