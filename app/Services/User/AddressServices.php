<?php

namespace App\Services\User;

use App\Models\User\Address;
use App\Services\BaseServices;

class AddressServices extends BaseServices
{

    /**
     * 查询地址列表
     *
     * @param integer $userId
     * @return Address[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAddressListByUserId(int $userId)
    {
        return Address::query()->where('user_id', $userId)->where('deleted', 0)->get();
    }

    /**
     * 获取地址
     *
     * @param integer $userId
     * @param integer $addressId
     * @return Address|null
     */
    public function getAddress($userId, $addressId)
    {
        return Address::query()->where('user_id', $userId)->where('id', $addressId)->where('deleted', 0)->first();
    }

    /**
     * 删除地址
     *
     * @param integer $userId
     * @param integer $addressId
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
