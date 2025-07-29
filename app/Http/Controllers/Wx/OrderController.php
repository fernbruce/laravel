<?php

namespace App\Http\Controllers\Wx;


use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Inputs\OrderSubmitInput;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;
use Yansongda\LaravelPay\Facades\Pay;

class OrderController extends WxController
{
    protected $except = ['wxNotify', 'alipayNotify','alipayReturn'];

    /**
     * 提交订单
     * @return JsonResponse
     * @throws BusinessException
     * @throws Throwable
     */
    public function submit()
    {
        $input = OrderSubmitInput::new();

        $lockKey = sprintf('order_submit_%s_%s', $this->userId(), md5(serialize($input)));
        $lock = Cache::lock($lockKey);
        if (!$lock) {
            return $this->fail(CodeResponse::FAIL, '请勿重复请求');
        }
//         $order = DB::transaction(function () use($input){
        return OrderService::getInstance()->submit($this->userId(), $input);
//         });
        return $this->success([
            'orderId' => $order->id,
            'grouponLinkId' => $order->grouponLinkId ?? 0,
        ]);
    }

    /**
     * 用户主动取消订单
     * @return JsonResponse
     * @throws BusinessException
     * @throws Throwable
     */
    public function cancel()
    {
        $orderId = $this->verifyId('orderId');
        OrderService::getInstance()->userCancel($this->userId(), $orderId);
        return $this->success();
    }

    public function refund()
    {
        $orderId = $this->verifyId('orderId');
        OrderService::getInstance()->refund($this->userId(), $orderId);
        return $this->success();
    }

    public function confirm()
    {
        $orderId = $this->verifyId('orderId');
        OrderService::getInstance()->confirm($this->userId(), $orderId);
        return $this->success();
    }

    /**
     * @return JsonResponse
     * @throws BusinessException
     */
    public function delete(): JsonResponse
    {
        $orderId = $this->verifyId('orderId');
        OrderService::getInstance()->delete($this->userId(), $orderId);
        return $this->success();
    }

    public function detail()
    {
        $orderId = $this->verifyId('orderId');
        $orderDetail = OrderService::getInstance()->detail($this->userId(), $orderId);
        return $this->success($orderDetail);
    }

    public function h5pay()
    {
        $orderId = $this->verifyId('orderId');
        $order = OrderService::getInstance()->getWxPayOrder($this->userId(), $orderId);
        return Pay::wechat()->wap($order);
    }

    public function wxNotify()
    {
        $data = Pay::wechat()->verify();
        $data = $data->toArray();
        Log::info('wxNotify', $data);
        DB::transaction(function () use ($data) {
            OrderService::getInstance()->wxNotify($data);
        });
        return Pay::wechat()->success();
    }

    public function h5alipay()
    {
        $orderId = $this->verifyId('orderId');
        $order = OrderService::getInstance()->getAlipayPayOrder($this->userId(), $orderId);
        return $this->success(Pay::alipay()->wap($order)->getContent());
    }

    public function alipayNotify()
    {
        $data = Pay::alipay()->verify()->toArray();
        Log::info('alipayNotify',$data);
        DB::transaction(function () use ($data) {
            OrderService::getInstance()->alipayNotify($data);
        });
        return Pay::alipay()->success();
    }

    public function alipayReturn(){
        $data = Pay::alipay()->find(request()->input())->toArray();
        Log::info('alipayReturn',$data);
        DB::transaction(function () use ($data) {
           OrderService::getInstance()->alipayNotify($data);
        });
        return redirect(env('H5_URL').'/#/user/order/list/0');

    }
}
