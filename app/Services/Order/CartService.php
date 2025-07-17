<?php

namespace App\Services\Order;


use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Order\Cart;
use App\Services\BaseServices;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\GrouponService;
use Exception;
use Illuminate\Support\Collection;

class CartService extends BaseServices
{
    /**
     * @param $userId
     * @return Cart[]|Collection
     */
    public function getCheckedCarts($userId)
    {
        return Cart::query()->where('user_id', $userId)
            ->where('checked', 1)->get();
    }

    /**
     * @param $userId
     * @param $cartId
     * @return Cart[]|Collection
     * @throws BusinessException
     */
    public function getCheckedCartList($userId, $cartId = null)
    {
        if (empty($cartId)) {
            $checkedGoodsList = $this->getCheckedCarts($userId);
        } else {
            $cart = $this->getCartById($userId, $cartId);
            if ($cart === null) {
                $this->throwBadArgumentValue();
            }
            $checkedGoodsList = collect([$cart]);
        }
        return $checkedGoodsList;
    }

    public function getCartPriceCutGroupon($checkedGoodsList,$grouponRulesId, &$grouponPrice){
        $grouponRules = GrouponService::getInstance()->getGrouponRulesById($grouponRulesId);
        $checkedGoodsPrice = 0;
        $grouponPrice = 0;
        foreach ($checkedGoodsList as $cart) {
            if ($grouponRules && $grouponRules->goods_id === $cart->goods_id) {
                $grouponPrice = bcmul($grouponRules->discount, $cart->number, 2);
                $price = bcsub($cart->price, $grouponRules->discount, 2);
            } else {
                $price = $cart->price;
            }
            $price = bcmul($price, $cart->number, 2);
            $checkedGoodsPrice = bcadd($checkedGoodsPrice, $price, 2);
        }
        return $checkedGoodsPrice;
    }

    /**
     * @param $userId
     * @param $id
     * @return Cart|null
     */
    public function getCartById($userId, $id): ?Cart
    {
        return Cart::query()->where('user_id', $userId)->where('id', $id)->first();
    }

    /**
     * @param $userId
     * @return object
     * @throws Exception
     */
    public function getValidCartList($userId)
    {
        $list = $this->getCartList($userId);
        $goodsId = $list->pluck('goods_id')->toArray();
        $goodsList = Goods::query()->whereIn('id', $goodsId)->get()->keyBy('id');
        $invalidCartIds = [];
        $list = $list->filter(function (Cart $cart) use ($goodsList, &$invalidCartIds) {
            /** @var Goods $goods */
            $goods = $goodsList->get($cart->goods_id);
            $isValid = $goods !== null && $goods->is_on_sale;
            if (!$isValid) {
                $invalidCartIds[] = $cart->id;
            }
            return $isValid;
        });
        $this->deleteCartList($invalidCartIds);
        return $list;
    }

    public function getCartList($userId)
    {
        return Cart::query()->where('user_id', $userId)->get();
    }

    /**
     * @param $ids
     * @return void
     * @throws Exception
     */
    public function deleteCartList($ids)
    {
        if (empty($ids)) {
            return;
        }
        Cart::query()->whereIn('id', $ids)->delete();
    }

    /**
     * 删除购物车
     * @param $userId
     * @param $productIds
     * @return bool|int|mixed|null
     * @throws Exception
     */
    public function delete($userId, $productIds)
    {
        return Cart::query()->where('user_id', $userId)->whereIn('product_id', $productIds)->delete();
    }

    public function countCartProduct($userId)
    {
        return Cart::query()->where('user_id', $userId)->sum('number');
    }

    /**
     * 添加购物车
     * @param $userId
     * @param $goodsId
     * @param $productId
     * @param $number
     * @return Cart
     * @throws BusinessException
     */
    public function add($userId, $goodsId, $productId, $number): Cart
    {
        [$goods, $product] = $this->getGoodsInfo($goodsId, $productId);
        $cartProduct = $this->getCartProduct($userId, $goodsId, $productId);
        if (is_null($cartProduct)) {
            return $this->newCart($userId, $goods, $product, $number);
        }

        $number = $cartProduct->number + $number;
        return $this->editCart($cartProduct, $product, $number);
    }

    /**
     * @param $goodsId
     * @param $productId
     * @return array
     * @throws BusinessException
     */
    public function getGoodsInfo($goodsId, $productId): array
    {
        $goods = GoodsServices::getInstance()->getGoods($goodsId);
        if (is_null($goods) || !$goods->is_on_sale) {
            $this->throwBusinessException(CodeResponse::GOODS_UNSHELVE);
        }
        $product = GoodsServices::getInstance()->getGoodsProductById($productId);
        if (is_null($product)) {
            $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
        }

        return [$goods, $product];
    }

    /**
     * 通过用户Id,商品Id,货品Id查询购物车记录
     * @param $userId
     * @param $goodsId
     * @param $productId
     * @return Cart|null
     */
    public function getCartProduct($userId, $goodsId, $productId): ?Cart
    {
        return Cart::query()->where('user_id', $userId)
            ->where('goods_id', $goodsId)
            ->where('product_id', $productId)
            ->first();
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
        $cart->goods_id = $goods->id;
        $cart->product_id = $product->id;
        $cart->save();
        return $cart;
    }

    /**
     * @param  Cart  $existCart
     * @param  GoodsProduct  $product
     * @param  int  $num
     * @return Cart
     * @throws BusinessException
     */
    public function editCart(Cart $existCart, GoodsProduct $product, int $num): Cart
    {
        if ($num > $product->number) {
            $this->throwBusinessException(CodeResponse::GOODS_NO_STOCK);
        }
        $existCart->number = $num;
        $existCart->save();
        return $existCart;
    }

    public function fastadd($userId, $goodsId, $productId, $number)
    {
        [$goods, $product] = $this->getGoodsInfo($goodsId, $productId);
        $cartProduct = $this->getCartProduct($userId, $goodsId, $productId);
        if (is_null($cartProduct)) {
            return $this->newCart($userId, $goodsId, $productId, $number);
        }

        return $this->editCart($cartProduct, $product, $number);
    }

    public function updateChecked($userId, $productIds, $isChecked)
    {
        return Cart::query()->where('user_id', $userId)
            ->whereIn('product_id', $productIds)
            ->update(['checked' => $isChecked]);

    }


}
