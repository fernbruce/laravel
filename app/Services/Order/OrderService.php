<?php

namespace App\Services\Order;


use App\CodeResponse;
use App\Enums\OrderEnums;
use App\Exceptions\BusinessException;
use App\Inputs\OrderSubmitInput;
use App\Jobs\OrderUnPaidTimeEndJob;
use App\Models\Goods\GoodsProduct;
use App\Models\Order\Cart;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\CouponService;
use App\Services\Promotion\GrouponService;
use App\Services\SystemServices;
use App\Services\User\AddressServices;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
        $checkedGoodsPrice = CartService::getInstance()->getCartPriceCutGroupon($checkedGoodsList,
            $input->grouponRulesId, $grouponPrice);

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
        $order->address = $address->province.$address->city.$address->county." ".$address->address_detail;
        $order->message = $input->message??'';
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
        GrouponService::getInstance()->openOrJoinGroupon($userId, $order->id, $input->grouponRulesId,
            $input->grouponLinkId);

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

        foreach($goodsList as $cart){
            $product = $products->get($cart->product_id);
            if(empty($product)){
                $this->throwBadArgumentValue();
            }
            if($product->number < $cart->number){
                $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
            }
            $row = GoodsServices::getInstance()->reduceStock($product->id, $cart->number);
            if($row === 0){
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
       return  retry(5, function () {
            $orderSn = date('YmdHis').Str::random(6);
            if (!$this->isOrderSnUsed($orderSn)) {
                return $orderSn;
            }
            Log::warning('订单号获取失败, orderSn:'.$orderSn);
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

    public function cancel($userId, $orderId){

    }
}

