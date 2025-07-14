<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Models\User\Address;
use App\Services\User\AddressServices;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class AddressController extends WxController
{

    /**
     * 获取用户地址列表
     * @return JsonResponse
     */
    public function list()
    {
        $list = AddressServices::getInstance()->getAddressListByUserId($this->user()->id);
        // $list = $list->map(function (Address $address) {
        //     $item = [];
        //     $address = $address->toArray();
        //     foreach ($address as $key => $value) {

        //         $key = Str::camel($key);
        //         $item[$key] = $value;
        //     }
        //     return $item;
        // });
        // return $this->successPaginate($list);
        return $this->success([
            'total' => $list->count(),
            'page' => 1,
            'list' => $list,
            'pages' => 1,
            'limit' => $list->count()
        ]);
    }

    public function detail(Request $request)
    {
        $id = $request->input('id');
        if (empty($id) || !is_numeric($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        $address = AddressServices::getInstance()->getAddress($this->user()->id, $id);
        return $this->success($address);
    }

    public function save(Request $request)
    {
        $name = $request->input('name');
        $province = $request->input('province');
        $city = $request->input('city');
        $county = $request->input('county');
        $address_detail = $request->input('address_detail');
        $area_code = $request->input('area_code');
        $postal_code = $request->input('postal_code');
        $tel = $request->input('tel');


        if (empty($name) || empty($province) || empty($city) || empty($county) || empty($address_detail) || empty($area_code) || empty($tel)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        $id = $request->input('id');
        if (empty($id)) {
            $address = new Address();
        } else {
            $address = AddressServices::getInstance()->getAddress($this->user()->id, $id);
        }

        $address->name = $name;
        $address->province = $province;
        $address->city = $city;
        $address->county = $county;
        $address->address_detail = $address_detail;
        $address->area_code = $area_code;
        $address->postal_code = $postal_code;
        $address->tel = $tel;
        $address->user_id = $this->user()->id;
        $address->update_time = Carbon::now()->toDateTimeString();

        $address->save();
        return $this->success();
    }

    /**
     * 删除地址
     * @param  Request  $request
     * @return JsonResponse
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
