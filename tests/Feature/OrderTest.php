<?php

namespace Tests\Feature;

use App\Models\Order\Order;
use App\Services\Order\ExpressServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;


class OrderTest extends TestCase
{
    use DatabaseTransactions;

//    public function testDetail()
//    {
//        // todo 删除非一级目录的字段
//        $this->assertLitemallApiGet('wx/order/detail?orderId=1',['expressInfo','expCode','expNo','expName']);
//    }


    public function testExpress()
    {
//        $ret = ExpressServices::getInstance()->getOrderTraces('JTSD', 'JT5376253296132');
//        $ret = ExpressServices::getInstance()->getOrderTraces('STO', '777308993434829');
//        $ret = ExpressServices::getInstance()->getOrderTraces('YTO', 'YT8777253089737');
//        $ret =  ExpressServices::getInstance()->getOrderTraces('YTO', 'YT7560733451381');
//        $ret =  ExpressServices::getInstance()->getOrderTraces('ZTO', '78926807604226');
//        print_r(json_decode($ret, true));
//        dd($ret);
        // Array
        // (
        //         [EBusinessID] => 1892828
        //         [ShipperCode] => YTO
        //         [LogisticCode] => YT7560733451381
        //         [Location] => 佛山市
        //         [State] => 2
        //         [StateEx] => 2
        //         [Traces] => Array
        //         (
        //             [0] => Array
        //             (
        //                     [Action] => 1
        //                     [AcceptStation] => 您的快件在【广东省佛山市市场部】已揽收，揽收人: 王珊（19856293667）【物流问题无需找商家或平台，请致电（02088888888）（专属热线:95554）更快解决】
        //                     [AcceptTime] => 2025-07-26 18:49:01
        //                     [Location] => 佛山市
        //             )

        //             [1] => Array
        //             (
        //                     [Action] => 204
        //                     [AcceptStation] => 您的快件已经到达【佛山转运中心】【物流问题无需找商家或平台，请致电（专属热线:95554）更快解决】
        //                     [AcceptTime] => 2025-07-26 21:22:58
        //                     [Location] => 佛山市
        //             )

        //             [2] => Array
        //             (
        //                     [Action] => 2
        //                     [AcceptStation] => 您的快件离开【佛山转运中心】，已发往【南京转运中心】。预计【07月28日】到达【南京市】，因运输距离较远，预计将在【28日晚上】为您更新快件状态，请您放心！
        //                     [AcceptTime] => 2025-07-27 05:33:55
        //                     [Location] => 佛山市
        //             )

        //         )

        //         [Success] => 1

        // )
    }
}
