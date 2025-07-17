<?php

namespace App\Services;

use App\CodeResponse;
use App\Exceptions\BusinessException;

class BaseServices
{
    protected static $instance;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!static::$instance instanceof static) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @param  array  $codeResponse
     * @param  string  $info
     * @return mixed
     * @throws BusinessException
     */
    public function throwBusinessException(array $codeResponse, string $info = "")
    {
        throw new BusinessException($codeResponse, $info);
    }

    /**
     * @return void
     * @throws BusinessException
     */
    public function throwBadArgumentValue(): void
    {
         $this-> throwBusinessException(CodeResponse::PARAM_VALUE_ILLEGAL);
    }
}
