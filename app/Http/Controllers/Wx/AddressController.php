<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Wx\WxController;
use App\Models\Address;
use App\Services\AddressServices;
use Illuminate\Http\Request;
use Str;

class AddressController extends WxController
{

    /**
     * 获取用户地址列表
     * @return JsonResponse
     */
    public function list()
    {
        $list = AddressServices::getInstance()->getAddressListByUserId($this->user()->id);
        $list = $list->map(function (Address $address) {
            $item = [];
            $address = $address->toArray();
            foreach ($address as $key => $value) {

                $key = Str::camel($key);
                $item[$key] = $value;
            }
            return $item;
        });
        return $this->success([
            'total' => $list->count(),
            'page' => 1,
            'list' => $list,
            'pages' => 1,
            'limit' => $list->count()
        ]);
    }

    public function detail() {}

    public function save() {}

    /**
     * 删除地址
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $id = $request->input('id');
        if (empty($id) || !is_numeric($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        AddressServices::getInstance()->delete($this->user()->id, $id);
        return $this->success();
    }
}
