<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Services\Goods\GoodsServices;
use App\Services\Order\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


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

        $cart = CartService::getInstance()->fastadd($this->userId(),$goodsId, $productId, $number);

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

        CartService::getInstance()->add($this->userId(),$goodsId, $productId, $number);

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
     * @return void
     * @throws \Exception
     */
    public function delete()
    {
        $productIds = $this->verifyArrayNotEmpty('productIds', []);
        CartService::getInstance()->delete($this->userId(), $productIds);
        $list = CartService::getInstance()->list($this->userId());
        return $this->success($list);
    }

    public function checked()
    {
        $productIds = $this->verifyArrayNotEmpty('productIds', []);
        $isChecked = $this->verifyBoolean('isChecked');
        CartService::getInstance()->updateChecked($this->userId(), $productIds, $isChecked==1);
        $list = CartService::getInstance()->list($this->userId());
        return $this->success($list);
    }
}
