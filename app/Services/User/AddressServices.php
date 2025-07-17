<?php

namespace App\Services\User;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Models\BaseModel;
use App\Models\User\Address;
use App\Services\BaseServices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use LaravelIdea\Helper\App\Models\User\_IH_Address_QB;

class AddressServices extends BaseServices
{

    public function getDefaultAddress($userId){
       return Address::query()->where('user_id',$userId)
           ->where('is_default',1)
           ->first();
    }

    /**
     * 获取地址或者返回默认地址
     * @param $userId
     * @param $addressId
     * @return BaseModel|Address|Builder|Model|_IH_Address_QB|object|null
     */
    public function getAddressOrDefault($userId, $addressId=null)
    {
        //获取地址
        if (empty($addressId)) {
            $address = $this->getDefaultAddress($userId);
        } else {
            $address = $this->getAddress($userId, $addressId);
            if ($address === null) {
                $this->throwBadArgumentValue();
            }
        }
        return $address;
    }
    /**
     * 查询地址列表
     *
     * @param  integer  $userId
     * @return Address[]|Collection
     */
    public function getAddressListByUserId(int $userId)
    {
        return Address::query()->where('user_id', $userId)->get();
    }

    /**
     * 获取地址
     *
     * @param  integer  $userId
     * @param  integer  $addressId
     * @return Address|null
     */
    public function getAddress($userId, $addressId)
    {
        return Address::query()->where('user_id', $userId)->where('id', $addressId)->first();
    }

    /**
     * 删除地址
     *
     * @param  integer  $userId
     * @param  integer  $addressId
     * @return bool
     */
    public function delete($userId, $addressId)
    {
        $address = $this->getAddress($userId, $addressId);

        if (is_null($address)) {
            $this->throwBusinessException(ResponseCode::PARAM_ERROR);
        }
        return $address->delete();
    }
}
