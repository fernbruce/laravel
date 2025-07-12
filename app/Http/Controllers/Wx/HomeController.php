<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Wx\WxController;


class HomeController extends WxController
{
    protected $only = [];

    public function redirectShareUrl()
    {
        $type = $this->verifyString('type', 'groupon');
        $id = $this->verifyId('id');
        if ($type == 'groupon') {
            return redirect(env('H5_URL') . '/#/items/detail/' . $id);
        }
        if ($type == 'goods') {
            return redirect(env('H5_URL') . '/#/items/detail/' . $id);
        }

        return redirect(env('H5_URL') . '/#/items/detail/' . $id);
    }
}
