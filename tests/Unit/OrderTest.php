<?php


use App\Inputs\OrderSubmitInput;
use App\Jobs\OrderUnPaidTimeEndJob;
use App\Models\Goods\GoodsProduct;
use App\Models\Order\OrderGoods;
use App\Models\Promotion\GrouponRules;
use App\Services\Goods\GoodsServices;
use App\Services\Order\CartService;
use App\Services\Order\OrderService;
use App\Services\User\AddressServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    public function testReduceStock(){
        /** @var GoodsProduct $product1 */
        $product1 = factory(GoodsProduct::class)->create(['price' => 11.3]);
        /** @var GoodsProduct $product2 */
        $product2 = factory(GoodsProduct::class)->state('groupon')->create(['price' => 20.56]);
        $product3 = factory(GoodsProduct::class)->create(['price' => 10.6]);
        $cart1 = CartService::getInstance()->add($this->user->id, $product1->goods_id, $product1->id, 2);
        $cart2 = CartService::getInstance()->add($this->user->id, $product2->goods_id, $product2->id, 5);
        $cart3 = CartService::getInstance()->add($this->user->id, $product3->goods_id, $product3->id, 3);
        CartService::getInstance()->updateChecked($this->user->id, [$product1->id], false);

        $checkedGoodsList = CartService::getInstance()->getCheckedCartList($this->user->id);

        OrderService::getInstance()->reduceProductsStock($checkedGoodsList);
//        $goodsProduct1 = GoodsProduct::query()->where('id',$product1->id)->first();
//        $goodsProduct2 = GoodsProduct::query()->where('id',$product2->id)->first();
//        $goodsProduct3 = GoodsProduct::query()->where('id',$product3->id)->first();
//        $this->assertEquals(100,$goodsProduct1->number);
//        $this->assertEquals(95,$goodsProduct2->number);
//        $this->assertEquals(97,$goodsProduct3->number);
        $this->assertEquals($product1->number,$product1->refresh()->number);
        $this->assertEquals($product2->number-5,$product2->refresh()->number);
        $this->assertEquals($product3->number-3,$product3->refresh()->number);

    }
    public function testSubmit()
    {
        $address = AddressServices::getInstance()->getDefaultAddress($this->user->id);

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
        $grouponRulesId = GrouponRules::whereGoodsId($product2->goods_id)->first()->id ?? null;
        $checkedGoodsList = CartService::getInstance()->getCheckedCartList($this->user->id);
        $checkedGoodsPrice = CartService::getInstance()->getCartPriceCutGroupon($checkedGoodsList, $grouponRulesId,
            $grouponPrice);
        $this->assertEquals(129.6, $checkedGoodsPrice);
        $input = OrderSubmitInput::new([
            'addressId' => $address->id,
            'cartId' => 0,
            'grouponRulesId' => $grouponRulesId,
            'couponId' =>0,
            'message'=>'备注',
        ]);
        $order = OrderService::getInstance()->submit($this->user->id, $input);

        $this->assertNotEmpty($order->id);
        $this->assertEquals($checkedGoodsPrice, $order->goods_price);
        $this->assertEquals($checkedGoodsPrice, $order->actual_price);
        $this->assertEquals($checkedGoodsPrice, $order->order_price);
        $this->assertEquals($grouponPrice, $order->groupon_price);
        $this->assertEquals('备注', $order->message);

        $list = OrderGoods::whereOrderId($order->id)->get();
        $this->assertEquals(2, $list->count());

        $productId = CartService::getInstance()->getCartList($this->user->id)->pluck('product_id')->toArray();

        $this->assertEquals([$product1->id], $productId);
    }

    public function testJob(){
        dispatch(new OrderUnPaidTimeEndJob(1,2));
    }
}
