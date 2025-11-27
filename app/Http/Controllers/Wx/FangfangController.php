<?php

namespace App\Http\Controllers\Wx;


use App\Services\Order\ExpressServices;

class FangfangController extends WxController
{
    protected $except = ['getData', 'getGeoData', 'get1', 'get2', 'get3'];

    public function getData()
    {
        return $this->success([100, 300, 200, 400, 500, 600]);
    }

    public function getGeoData()
    {
        $geoData = ExpressServices::getInstance()->getGeoData('YTO', 'YT7560733451381');
        return $this->success($geoData);
    }

    public function get1()
    {
        return $this->success([
            'name' => 'get1',
            'age' => 18,
            'sex' => '男',
            'phone' => '12345678901',
            'address' => '中国上海',
        ]);
    }

    public function get2()
    {
        return $this->success([
            'name' => 'get2',
            'age' => 18,
            'sex' => '男',
            'phone' => '12345678901',
            'address' => '中国上海',
        ]);
    }

    public function get3()
    {
        return $this->success([
            'name' => 'get3',
            'age' => 18,
            'sex' => '男',
            'phone' => '12345678901',
            'address' => '中国上海',
        ]);
    }
}
