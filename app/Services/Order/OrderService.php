<?php

namespace App\Services\Order;


use App\CodeResponse;
use App\Enums\OrderEnums;
use App\Exceptions\BusinessException;
use App\Inputs\OrderSubmitInput;
use App\Jobs\OrderUnPaidTimeEndJob;
use App\Models\Order\Cart;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use App\Notifications\NewPaidOrderEmailNotify;
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\CouponService;
use App\Services\Promotion\GrouponService;
use App\Services\SystemServices;
use App\Services\User\AddressServices;
use App\Services\User\UserServices;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Throwable;

class OrderService extends BaseServices
{
    /**
     * @param $userId
     * @param  OrderSubmitInput  $input
     * @throws BusinessException
     */
    public function submit($userId, OrderSubmitInput $input)
    {
        //验证团购规则的有效性
        if (!empty($input->grouponRulesId)) {
            GrouponService::getInstance()->checkGrouponValid($userId, $input->grouponRulesId);
        }
        $address = addressServices::getInstance()->getAddress($userId, $input->addressId);
        if (empty($address)) {
            return $this->throwBadArgumentValue();
        }

        //获取购物车的商品列表
        $checkedGoodsList = CartService::getInstance()->getCheckedCartList($userId, $input->cartId);

        //计算商品总额
        $grouponPrice = 0;
        $checkedGoodsPrice = CartService::getInstance()->getCartPriceCutGroupon(
            $checkedGoodsList,
            $input->grouponRulesId,
            $grouponPrice
        );

        $couponPrice = 0;
        if ($input->couponId > 0) {
            $coupon = CouponService::getInstance()->getCoupon($input->couponId);
            $couponUser = CouponService::getInstance()->getCouponUser($input->userCouponId);
            $is = CouponService::getInstance()->checkCouponAndPrice($coupon, $couponUser, $checkedGoodsPrice);
            if ($is) {
                $couponPrice = $coupon->discount;
            }
        }

        //运费
        $freightPrice = $this->getFreight($checkedGoodsPrice);

        //计算订单金额
        $orderTotalPrice = bcadd($checkedGoodsPrice, $freightPrice, 2);
        $orderTotalPrice = bcsub($orderTotalPrice, $couponPrice, 2);
        $orderTotalPrice = max(0, $orderTotalPrice);

        $order = Order::new();
        $order->user_id = $userId;
        $order->order_sn = $this->generateOrderSn();
        $order->order_status = OrderEnums::STATUS_CREATE;
        $order->consignee = $address->name;
        $order->mobile = $address->tel;
        $order->address = $address->province . $address->city . $address->county . " " . $address->address_detail;
        $order->message = $input->message ?? '';
        $order->goods_price = $checkedGoodsPrice;
        $order->freight_price = $freightPrice;
        $order->coupon_price = $couponPrice;
        $order->order_price = $orderTotalPrice;
        $order->actual_price = $orderTotalPrice;
        $order->integral_price = 0;
        $order->groupon_price = $grouponPrice;
        $order->save();

        //写入订单商品记录
        $this->saveOrderGoods($checkedGoodsList, $order->id);

        //清理购物车记录
        CartService::getInstance()->clearCartGoods($userId, $input->cartId);

        // 减库存
        $this->reduceProductsStock($checkedGoodsList);

        //添加团购记录
        GrouponService::getInstance()->openOrJoinGroupon(
            $userId,
            $order->id,
            $input->grouponRulesId,
            $input->grouponLinkId
        );

        // 设置超时任务
        dispatch(new OrderUnPaidTimeEndJob($userId, $order->id));
        return $order;
    }

    /**
     * @param $goodsList
     * @return void
     * @throws BusinessException
     */
    public function reduceProductsStock($goodsList)
    {
        $productIds = $goodsList->pluck('product_id')->toArray();
        $products = GoodsServices::getInstance()->getGoodsProductByIds($productIds)->keyBy('id');

        foreach ($goodsList as $cart) {
            $product = $products->get($cart->product_id);
            if (empty($product)) {
                $this->throwBadArgumentValue();
            }
            if ($product->number < $cart->number) {
                $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
            }
            $row = GoodsServices::getInstance()->reduceStock($product->id, $cart->number);
            if ($row === 0) {
                $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
            }
        }
    }

    /**
     * @param  Cart[]  $checkGoodsList
     * @param $orderId
     */
    private function saveOrderGoods($checkGoodsList, $orderId)
    {
        foreach ($checkGoodsList as $cart) {
            $orderGoods = OrderGoods::new();
            $orderGoods->order_id = $orderId;
            $orderGoods->goods_id = $cart->goods_id;
            $orderGoods->goods_sn = $cart->goods_sn;
            $orderGoods->product_id = $cart->product_id;
            $orderGoods->goods_name = $cart->goods_name;
            $orderGoods->pic_url = $cart->pic_url;
            $orderGoods->price = $cart->price;
            $orderGoods->number = $cart->number;
            $orderGoods->specifications = $cart->specifications;
            $orderGoods->save();
        }
    }

    /**
     * 生产订单编号
     * @return void
     * @throws BusinessException
     */
    public function generateOrderSn()
    {
        return retry(5, function () {
            $orderSn = date('YmdHis') . Str::random(6);
            if (!$this->isOrderSnUsed($orderSn)) {
                return $orderSn;
            }
            Log::warning('订单号获取失败, orderSn:' . $orderSn);
            $this->throwBusinessException(CodeResponse::FAIL, '订单号获取失败');
        });
    }

    public function isOrderSnUsed($orderSn)
    {
        return Order::query()->where('order_sn', $orderSn)->exists();
    }

    /**
     * 获取运费
     * @param $price
     * @return float|int
     */
    public function getFreight($price)
    {
        //运费
        $freightPrice = 0;
        $freightMin = SystemServices::getInstance()->getFreightMin();
        if (bccomp($freightMin, $price) == 1) {
            $freightPrice = SystemServices::getInstance()->getFreightValue();
        }
        return $freightPrice;
    }

    public function getOrderByUserIdAndId($userId, $orderId)
    {
        return Order::query()->where('user_id', $userId)->find($orderId);
    }

    public function getOrderGoodsList($orderId)
    {
        return OrderGoods::query()->where('order_id', $orderId)->get();
    }

    /**
     * @param $userId
     * @param $orderId
     * @return bool
     * @throws BusinessException|Throwable
     */
    public function adminCancel($userId, $orderId)
    {
        return $this->cancel($userId, $orderId, 'admin');
    }

    /**
     * @param $userId
     * @param $orderId
     * @return bool
     * @throws BusinessException|Throwable
     */
    public function userCancel($userId, $orderId)
    {
        return $this->cancel($userId, $orderId);
    }

    /**
     * @param $userId
     * @param $orderId
     * @return bool
     * @throws BusinessException|Throwable
     */
    public function systemCancel($userId, $orderId)
    {
        return $this->cancel($userId, $orderId, 'system');
    }

    /**
     * @param $userId
     * @param $orderId
     * @param  string  $role  支持: user/admin/system
     * @return true
     * @throws BusinessException|Throwable
     */
    private function cancel($userId, $orderId, string $role = 'user'): bool
    {
        return DB::transaction(function () use ($userId, $orderId, $role) {

            /** @var Order $order */
            $order = $this->getOrderByUserIdAndId($userId, $orderId);
            if (is_null($order)) {
                $this->throwBadArgumentValue();
            }
            if (!$order->canCancelHandle()) {
                $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '订单不能取消');
            }

            switch ($role) {
                case 'system':
                    $order->order_status = OrderEnums::STATUS_AUTO_CANCEL;
                    break;
                case 'admin':
                    $order->order_status = OrderEnums::STATUS_ADMIN_CANCEL;
                    break;
                default:
                    $order->order_status = OrderEnums::STATUS_CANCEL;
            }

            if ($order->cas() == 0) {
                $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
            }
            $this->returnStock($orderId);
            return true;
        });
    }

    private function returnStock($orderId)
    {
        /** @var OrderGoods $orderGoods */
        $orderGoods = $this->getOrderGoodsList($orderId);
        foreach ($orderGoods as $orderGood) {
            $row = GoodsServices::getInstance()->addStock($orderGood->product_id, $orderGood->number);
            if ($row === 0) {
                $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
            }
        }
    }

    public function payOrder(Order $order, $payId)
    {
        if (!$order->canPayHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_PAY_FAIL, '订单不能支付');
        }

        $order->refresh();
        $order->pay_id = $payId;
        $order->pay_time = now()->toDateTimeString();
        $order->order_status = OrderEnums::STATUS_PAY;
        if ($order->cas() === 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }
        //        GrouponService::getInstance()->payGrouponOrder($order->id);
        Notification::route('mail', env('MAIL_USERNAME'))->notify(new NewPaidOrderEmailNotify($order->id));

        $user = UserServices::getInstance()->getUserById($order->user_id);
        //        $user->notify(new NewPaidOrderSMSNotify());
        return true;
    }

    /**
     * @throws BusinessException
     * @throws Throwable
     */
    public function ship($userId, $orderId, $shipSn, $shipChannel)
    {
        /** @var Order $order */
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if (is_null($order)) {
            $this->throwBadArgumentValue();
        }

        if (!$order->canShipHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能发货');
        }

        $order->order_status = OrderEnums::STATUS_SHIP;
        $order->ship_sn = $shipSn;
        $order->ship_channel = $shipChannel;
        $order->pay_time = now()->toDateTimeString();
        if ($order->cas() === 0) {
            $this->throwUpdateFail();
        }
        // todo 发通知
        return $order;
    }

    public function refund($userId, $orderId)
    {
        /** @var Order $order */
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if (is_null($order)) {
            $this->throwBadArgumentValue();
        }

        if (!$order->canRefundHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能申请退款');
        }

        $order->order_status = OrderEnums::STATUS_REFUND;
        if ($order->cas() === 0) {
            $this->throwUpdateFail();
        }
        // todo 发通知
        return $order;
    }


    public function agreeRefund(Order $order, $refundType, $refundContent)
    {
        if (!$order->canAgreeRefundHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能同意退款');
        }
        $now = now()->toDateTimeString();
        $order->order_status = OrderEnums::STATUS_REFUND_CONFIRM;
        $order->end_time = $now;
        $order->refund_amount = $order->actual_price;
        $order->refund_type = $refundType;
        $order->refund_content = $refundContent;
        $order->refund_time = $now;
        if ($order->cas() === 0) {
            $this->throwUpdateFail();
        }
        $this->returnStock($order->id);
        return $order;
    }

    /**
     * 获取订单的商品数量
     * @param $orderId
     * @return int
     */
    public function countOrderGoods($orderId)
    {
        return OrderGoods::whereOrderId($orderId)->count(['id']);
    }

    public function confirm($userId, $orderId, $isAuto = false)
    {
        /** @var Order $order */
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if (empty($order)) {
            $this->throwBadArgumentValue();
        }

        if (!$order->canConfirmHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不定确认收货');
        }

        $order->comments = $this->countOrderGoods($orderId);
        $order->order_status = $isAuto ? OrderEnums::STATUS_AUTO_CONFIRM : OrderEnums::STATUS_CONFIRM;
        $order->confirm_time = now()->toDateTimeString();
        if ($order->cas() === 0) {
            $this->throwUpdateFail();
        }
        return $order;
    }

    /**
     * @param $userId
     * @param $orderId
     * @return void
     * @throws BusinessException
     */
    public function delete($userId, $orderId)
    {
        /** @var Order $order */
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if (is_null($order)) {
            $this->throwBadArgumentValue();
        }
        if (!$order->canDeleteHandle()) {
            $this->throwBusinessException(CodeResponse::ORDER_INVALID_OPERATION, '该订单不能删除');
        }
        $order->delete();
        // todo 售后删除

    }

    public function getTimeoutUnConfirmOrders()
    {
        $days = SystemServices::getInstance()->getOrderUnConfirmDays();
        return Order::query()->where('order_status', OrderEnums::STATUS_SHIP)->where(
            'ship_time',
            '<=',
            now()->subDays($days)
        )
            ->where('ship_time', '>=', now()->subDays($days + 30))
            ->get();
    }

    public function autoConfirm()
    {
        $orders = $this->getTimeoutUnConfirmOrders();
        foreach ($orders as $order) {
            try {
                $this->confirm($order->user_id, $order->id, true);
            } catch (BusinessException $e) {
            } catch (Throwable $e) {
                Log::error('Auto confirm error. Error:' . $e->getMessage());
            }
        }
    }

    public function detail($userId, $orderId)
    {
        /** @var Order $order */
        $order = $this->getOrderByUserIdAndId($userId, $orderId);
        if ($order === null) {
            $this->throwBadArgumentValue();
        }
        $detail = Arr::only($order->toArray(), [
            'id',
            'orderSn',
            'message',
            'addTime',
            'consignee',
            'mobile',
            'address',
            'goodsPrice',
            'couponPrice',
            'freightPrice',
            'actualPrice',
            'aftersaleStatus'
        ]);
        $detail['orderStatusText'] = OrderEnums::STATUS_TEXT_MAP[$order->order_status ?? ''];
        $detail['handleOption'] = $order->getCanHandleOptions();

        $goodsList = $this->getOrderGoodsList($orderId);

        $express = [];
        if ($order->isShipStatus()) {
            $detail['expCode'] = $order->ship_channel;
            $detail['expNo'] = $order->ship_sn;
            $detail['expName'] = ExpressServices::getInstance()->getExpressName($order->ship_channel);
            $express = []; //todo
        }
        return [
            'orderInfo' => $detail,
            'orderGoods' => $goodsList,
            'expressInfo' => $express
        ];
    }
}
