<?php

namespace App\Services;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use Mockery;

class BaseServices
{
    protected static $instance = [];

    /**
     * @return static
     */
    public static function getInstance()
    {
//        echo static::class . '>>' . (static::$instance[static::class] ?? [] instanceof static ? 'true' : 'false') . PHP_EOL;
         if (!(static::$instance[static::class]??[]) instanceof static) {
             return static::$instance[static::class] = new static();
         }
         return static::$instance[static::class];

//        if ((static::$instance[static::class] ?? []) instanceof static) {
//            return static::$instance[static::class];
//        }
//        return static::$instance[static::class] = new static();
    }

    public static function getMockInstance(){
        return static::$instance[static::class] = Mockery::mock(static::class)
            ->makePartial();
    }
    public static function rollbackInstance(){
        return static::$instance[static::class] = new static();
    }

    public static function getInstances()
    {
        return static::$instance;
    }
    private function __construct() {}

    private function __clone() {}

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
        $this->throwBusinessException(CodeResponse::PARAM_VALUE_ILLEGAL);
    }


    /**
     * @return void
     * @throws BusinessException
     */
    public function throwUpdateFail(): void
    {
        $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
    }
}
