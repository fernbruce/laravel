<?php

namespace App\Http\Controllers\Wx;


use App\Inputs\OrderSubmitInput;
use App\Services\Order\OrderService;
use Illuminate\Support\Facades\DB;

class OrderController extends WxController
{
    /**
     * 提交订单
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\BusinessException
     * @throws \Throwable
     */
    public function submit(){
         $input = OrderSubmitInput::new();
//         $order = DB::transaction(function () use($input){
             return  OrderService::getInstance()->submit($this->userId(),$input);
//         });
         return $this->success([
             'orderId'=>$order->id,
             'grouponLinkId'=>$order->grouponLinkId??0,
         ]);
    }

}
