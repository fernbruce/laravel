<?php

namespace App\Inputs;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\VerifyRequestInput;
use Illuminate\Support\Facades\Validator;

class Input
{
    use VerifyRequestInput;


    /**
     * @return BusinessException
     */
    public function fill($data = null)
    {
        if (is_null($data)) {
            $data = request()->input();
        }
        $validator = Validator::make($data, $this->rules());
        if ($validator->fails()) {
//            throw new BusinessException(CodeResponse::PARAM_VALUE_ILLEGAL,json_encode($validator->errors()->all(), JSON_UNESCAPED_UNICODE));
            throw new BusinessException(CodeResponse::PARAM_VALUE_ILLEGAL);
        }
        $map = get_object_vars($this);
        $keys = array_keys($map);
        collect($data)->map(function ($v, $k) use ($keys) {
            if (in_array($k, $keys)) {
                $this->$k = $v;
            }
        });

        return $this;
    }

    /**
     * @return GoodsListInput|static
     * @throws BusinessException
     */
    public static function new($data = null)
    {
        return (new static())->fill($data);
    }

    public function rules()
    {
    }
}
