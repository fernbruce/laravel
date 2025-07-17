<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Services\Goods\GoodsServices;
use App\Services\Order\CartService;
use App\Services\Promotion\CouponService;
use App\Services\Promotion\GrouponService;
use App\Services\SystemServices;
use App\Services\User\AddressServices;
use Exception;
use Illuminate\Http\JsonResponse;


class CartController extends WxController
{
    /**
     * 立即购买
     * @return JsonResponse
     * @throws BusinessException
     */
    public function fastadd(): JsonResponse
    {

        $goodsId = $this->verifyId('goodsId', 0);
        $productId = $this->verifyId('productId', 0);
        $number = $this->verifyPositiveInteger('number', 0);

        $cart = CartService::getInstance()->fastadd($this->userId(), $goodsId, $productId, $number);

        return $this->success($cart->id);
    }

    /**
     * 加入购物车
     * @return JsonResponse
     * @throws BusinessException
     */
    public function add(): JsonResponse
    {

        $goodsId = $this->verifyId('goodsId', 0);
        $productId = $this->verifyId('productId', 0);
        $number = $this->verifyPositiveInteger('number', 0);

        CartService::getInstance()->add($this->userId(), $goodsId, $productId, $number);

        $count = CartService::getInstance()->countCartProduct($this->userId());
        return $this->success($count);
    }

    /**
     * 获取购物车商品件数
     * @return JsonResponse
     */
    public function goodsCount(): JsonResponse
    {
        $count = CartService::getInstance()->countCartProduct($this->userId());
        return $this->success($count);
    }

    /**
     * 更新购物车数量
     * @return JsonResponse
     */
    public function update(): JsonResponse
    {
        $id = $this->verifyId('id', '0');
        $goodsId = $this->verifyId('goodsId', 0);
        $productId = $this->verifyId('productId', 0);
        $number = $this->verifyPositiveInteger('number', 0);
        $cart = CartService::getInstance()->getCartById($this->userId(), $id);
        if (is_null($cart)) {
            return $this->badArgumentValue();
        }
        if ($cart->goods_id !== $goodsId || $cart->product_id !== $productId) {
            return $this->badArgumentValue();
        }
        $goods = GoodsServices::getInstance()->getGoods($goodsId);
        if (is_null($goods) || !$goods->is_on_sale) {
            return $this->fail(CodeResponse::GOODS_UNSHELVE);
        }
        $product = GoodsServices::getInstance()->getGoodsProductById($productId);
        if (is_null($product) || $product->number < $number) {
            return $this->fail(CodeResponse::GOODS_NO_STOCK);
        }

        $cart->number = $number;
        $ret = $cart->save();
        return $this->failOrSuccess($ret, CodeResponse::UPDATED_FAIL);


    }

    /**
     * @throws Exception
     */
    public function delete()
    {
        $productIds = $this->verifyArrayNotEmpty('productIds', []);
        CartService::getInstance()->delete($this->userId(), $productIds);
        $list = CartService::getInstance()->list($this->userId());
        return $this->index();
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public function index(): JsonResponse
    {
        $list = CartService::getInstance()->getValidCartList($this->userId());
        $goodsCount = 0;
        $goodsAmount = 0;
        $checkedGoodsCount = 0;
        $checkedGoodsAmount = 0;
        foreach ($list as $cart) {
            $goodsCount += $cart->number;
            $amount = bcmul($cart->price, $cart->number, 2);
            $goodsAmount = bcadd($goodsAmount, $amount, 2);
            if ($cart->checked) {
                $checkedGoodsCount += $cart->number;
                $checkedGoodsAmount = bcadd($checkedGoodsAmount, $amount, 2);
            }
        }
        return $this->success([
            'cartList' => $list->toArray(),
            'cartTotal' => [
                'goodsCount' => $goodsCount,
                'goodsAmount' => (double) $goodsAmount,
                'checkedGoodsCount' => $checkedGoodsCount,
                'checkedGoodsAmount' => (double) $checkedGoodsAmount,
            ]
        ]);

    }

    public function checked()
    {
        $productIds = $this->verifyArrayNotEmpty('productIds', []);
        $isChecked = $this->verifyBoolean('isChecked');
        CartService::getInstance()->updateChecked($this->userId(), $productIds, $isChecked == 1);
        return $this->index();
    }

    /**
     * 下单前信息确认
     * @return JsonResponse
     */
    public function checkout()
    {
        $cartId = $this->verifyInteger('cartId');
        $addressId = $this->verifyInteger('addressId');
        $couponId = $this->verifyInteger('couponId');
        $grouponRulesId = $this->verifyInteger('grouponRulesId');

        //获取地址
        $address = AddressServices::getInstance()->getAddressOrDefault($this->userId(),$addressId);
        $addressId = $address->id??0;
        //获取购物车的商品列表
        $checkedGoodsList = CartService::getInstance()->getCheckedCartList($this->userId(),$cartId);

        //计算订单总额
        $grouponPrice = 0;
        $checkedGoodsPrice = CartService::getInstance()->getCartPriceCutGroupon($checkedGoodsList, $grouponRulesId,$grouponPrice);

        // 获取适合当前价格的优惠券列表, 并根据优惠折扣进行降序排序->获取最合适的优惠券
        $couponPrice = 0;
        $availableCouponLength=0;
        $couponUser = CouponService::getInstance()->getMostMeetPriceCoupon($this->userId(), $couponId,  $checkedGoodsPrice,$availableCouponLength);
        if(is_null($couponUser)){
            $couponId = -1;
            $userCouponId = -1;
        }else{
            $couponId = $couponUser->coupon_id ?? 0;
            $userCouponId = $couponUser->coupon_id ?? 0;
            $couponPrice = CouponService::getInstance()->getCoupon($couponId)->discount??0;
        }

        //运费
        $freightPrice = 0;
        $freightMin = SystemServices::getInstance()->getFreightMin();
        if (bccomp($freightMin, $checkedGoodsPrice) == 1) {
            $freightPrice = SystemServices::getInstance()->getFreightValue();
        }

        // 计算订单金额
        $orderPrice = bcadd($checkedGoodsPrice, $freightPrice, 2);
        $orderPrice = bcsub($orderPrice, $couponPrice, 2);

        return $this->success([
            "addressId" => $addressId,
            "couponId" => $couponId,
            "userCouponId" => $userCouponId,
            "cartId" => $cartId,
            "grouponRulesId" => $grouponRulesId,
            "grouponPrice" => $grouponPrice,
            "checkedAddress" => $address,
            "availableCouponLength" => $availableCouponLength,
            "goodsTotalPrice" => (double)$checkedGoodsPrice,
            "freightPrice" => (double)$freightPrice,
            "couponPrice" => (double)$couponPrice,
            "orderTotalPrice" => (double)$orderPrice,
            "actualPrice" => (double)$orderPrice,
            "checkedGoodsList" => $checkedGoodsList->toArray(),
        ]);
    }

}
