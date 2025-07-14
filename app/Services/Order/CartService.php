<?php

namespace App\Services\Order;


use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Order\Cart;
use App\Services\BaseServices;

class CartService extends BaseServices
{
    /**
     * @param $userId
     * @param $goodsId
     * @param $productId
     * @return Cart|null
     */
    public function getCartProduct($userId, $goodsId, $productId)
    {
        return Cart::query()->where('user_id', $userId)
            ->where('goods_id', $goodsId)
            ->where('product_id', $productId)
            ->first();
    }

    public function countCartProduct($userId)
    {
        return Cart::query()->where('user_id', $userId)->sum('number');
    }

    /**
     * @param $userId
     * @param  Goods  $goods
     * @param  GoodsProduct  $product
     * @param $number
     * @return Cart
     * @throws BusinessException
     */
    public function newCart($userId, Goods $goods, GoodsProduct $product, $number): Cart
    {
        if ($number > $product->number) {
            $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
        }
        $cart = Cart::new();
        $cart->goods_sn = $goods->goods_sn;
        $cart->goods_name = $goods->name;
        $cart->pic_url = $product->url ?: $goods->pic_url;
        $cart->price = $product->price;
        $cart->user_id = $userId;
        $cart->checked = true;
        $cart->number = $number;
        $cart->save();
        return $cart;
    }
}
