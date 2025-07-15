<?php

namespace App;

use App\Exceptions\BusinessException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait VerifyRequestInput
{

    public function verifyId($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer|digits_between:1,20|min:1');
    }

    /**
     * 验证字段
     *
     * @param $key
     * @param  $default
     * @param  $rule
     * @return mixed
     * @throws BusinessException
     */
    public function verifyData($key, $default, $rule)
    {
        $value = request()->input($key, $default);
        $validator = Validator::make([$key => $value], [$key => $rule]);
        if (is_null($default) && is_null($value)) {
            return $value;
        }
        if ($validator->fails()) {
            throw new BusinessException(CodeResponse::PARAM_VALUE_ILLEGAL);
        }
        return $value;
    }

    public function verifyString($key, $default = null)
    {
        return $this->verifyData($key, $default, 'string');
    }

    public function verifyBoolean($key, $default = null)
    {
        return $this->verifyData($key, $default, 'boolean');
    }

    public function verifyInteger($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer');
    }

    public function verifyPositiveInteger($key, $default = null)
    {
        return $this->verifyData($key, $default, 'integer|min:1');
    }

    public function verifyEnum($key, $default = null, $enum = [])
    {
        return $this->verifyData($key, $default, Rule::in($enum));
    }

    public function verifyArrayNotEmpty($key, $default = [])
    {
        return $this->verifyData($key, $default, 'array|min:1');
    }
}
