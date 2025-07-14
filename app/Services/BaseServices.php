<?php

namespace App\Services;

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

    public function throwBusinessException(array $codeResponse, $info = "")
    {
        throw new BusinessException($codeResponse, $info);
    }
}
