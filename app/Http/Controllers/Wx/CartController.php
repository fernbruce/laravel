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
     * 加入购物车
     * @param  Request  $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function add(Request $request): JsonResponse
    {

        $goodsId = $request->input('goodsId', 0);
        $productId = $request->input('productId', 0);
        $number = $request->input('number', 0);
        if ($number <= 0) {
            return $this->badArgument();
        }

        $goods = GoodsServices::getInstance()->getGoods($goodsId);
        if (is_null($goods) || !$goods->is_on_sale) {
            return $this->fail(CodeResponse::GOODS_UNSHELVE);
        }
        $product = GoodsServices::getInstance()->getGoodsProductById($productId);
        if (is_null($product)) {
            return $this->badArgument();
        }
        $cartProduct = CartService::getInstance()->getCartProduct($this->userId(), $goodsId, $productId);
        if (is_null($cartProduct)) {
            //add new cart product
            $cart = CartService::getInstance()->newCart($this->userId(), $goods, $product, $number);

        } else {
            //edit cart product number
            $num = $cartProduct->number + $number;
            if ($num > $product->number) {
                return $this->fail(CodeResponse::GOODS_NO_STOCK);
            }
            $cartProduct->number = $num;
            $cartProduct->save();
        }
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
}
