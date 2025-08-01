<?php

namespace App\Http\Controllers\Wx;


use App\Services\Order\ExpressServices;

class FangfangController extends WxController
{
    protected $except = ['getData', 'getGeoData'];

    public function getData()
    {
        return $this->success([100, 300, 200, 400, 500, 600]);
    }

    public function getGeoData()
    {
        $geoData = ExpressServices::getInstance()->getGeoData('YTO', 'YT7560733451381');
        return $this->success($geoData);
    }
}
