<?php

namespace Tests\Unit;

use App\Exceptions\BusinessException;
use App\Models\Goods\GoodsProduct;
use App\Models\Promotion\GrouponRules;
use App\Services\Order\CartService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return void
     * @throws BusinessException
     */
    public function testGetCartPriceCutGrouponSimple(): void
    {
        /** @var GoodsProduct $product1 */
        $product1 = factory(GoodsProduct::class)->create(['price' => 11.3]);
        /** @var GoodsProduct $product2 */
        $product2 = factory(GoodsProduct::class)->create(['price' => 20.56]);
        $product3 = factory(GoodsProduct::class)->create(['price' => 10.6]);
        $cart1 = CartService::getInstance()->add($this->user->id, $product1->goods_id, $product1->id, 2);
        $cart2 = CartService::getInstance()->add($this->user->id, $product2->goods_id, $product2->id, 1);
        $cart3 = CartService::getInstance()->add($this->user->id, $product3->goods_id, $product3->id, 3);
        CartService::getInstance()->updateChecked($this->user->id, [$product3->id], false);
        $checkedGoodsList = CartService::getInstance()->getCheckedCartList($this->user->id);
        $grouponPrice = 0;
        $checkedGoodsPrice = CartService::getInstance()->getCartPriceCutGroupon($checkedGoodsList, null, $grouponPrice);
        $this->assertEquals(43.16, $checkedGoodsPrice);
    }

    public function testGetCartPriceCutGrouponGroupon(): void
    {
        /** @var GoodsProduct $product1 */
        $product1 = factory(GoodsProduct::class)->create(['price' => 11.3]);
        /** @var GoodsProduct $product2 */
        $product2 = factory(GoodsProduct::class)->state('groupon')->create(['price' => 20.56]);
        $product3 = factory(GoodsProduct::class)->create(['price' => 10.6]);
        $cart1 = CartService::getInstance()->add($this->user->id, $product1->goods_id, $product1->id, 2);
        $cart2 = CartService::getInstance()->add($this->user->id, $product2->goods_id, $product2->id, 5);
        $cart3 = CartService::getInstance()->add($this->user->id, $product3->goods_id, $product3->id, 3);
        CartService::getInstance()->updateChecked($this->user->id, [$product1->id], false);

        $grouponPrice = 0;
        $grouponRulesId = GrouponRules::whereGoodsId($product2->goods_id)->first()->id??null;

        $checkedGoodsList = CartService::getInstance()->getCheckedCartList($this->user->id);
        $checkedGoodsPrice = CartService::getInstance()->getCartPriceCutGroupon($checkedGoodsList, $grouponRulesId, $grouponPrice);
        $this->assertEquals(129.6, $checkedGoodsPrice);
    }
}
