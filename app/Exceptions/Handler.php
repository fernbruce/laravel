<?php

namespace App\Exceptions;

use App\CodeResponse;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //不会上报，如果上报会记录错误日志，因为是业务异常所以不需要记录错误日志
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
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * 进行异常的格式化
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Exception  $exception
     * @return Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
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
