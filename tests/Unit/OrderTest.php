<?php

namespace Tests\Unit;

use App\Enums\OrderEnums;
use App\Exceptions\BusinessException;
use App\Inputs\OrderSubmitInput;
use App\Jobs\OrderUnPaidTimeEndJob;
use App\Models\Goods\GoodsProduct;
use App\Models\Order\Order;
use App\Models\Order\OrderGoods;
use App\Models\Promotion\GrouponRules;
use App\Models\User\User;
use App\Services\Goods\GoodsServices;
use App\Services\Order\CartService;
use App\Services\Order\OrderService;
use App\Services\User\AddressServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Throwable;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    public function testReduceStock()
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

        $checkedGoodsList = CartService::getInstance()->getCheckedCartList($this->user->id);

        OrderService::getInstance()->reduceProductsStock($checkedGoodsList);
        //        $goodsProduct1 = GoodsProduct::query()->where('id',$product1->id)->first();
        //        $goodsProduct2 = GoodsProduct::query()->where('id',$product2->id)->first();
        //        $goodsProduct3 = GoodsProduct::query()->where('id',$product3->id)->first();
        //        $this->assertEquals(100,$goodsProduct1->number);
        //        $this->assertEquals(95,$goodsProduct2->number);
        //        $this->assertEquals(97,$goodsProduct3->number);
        $this->assertEquals($product1->number, $product1->refresh()->number);
        $this->assertEquals($product2->number - 5, $product2->refresh()->number);
        $this->assertEquals($product3->number - 3, $product3->refresh()->number);
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
        $checkedGoodsPrice = CartService::getInstance()->getCartPriceCutGroupon(
            $checkedGoodsList,
            $grouponRulesId,
            $grouponPrice
        );
        $this->assertEquals(129.6, $checkedGoodsPrice);
        $input = OrderSubmitInput::new([
            'addressId' => $address->id,
            'cartId' => 0,
            'grouponRulesId' => $grouponRulesId,
            'couponId' => 0,
            'message' => '备注',
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

    public function testJob()
    {
        dispatch(new OrderUnPaidTimeEndJob(1, 2));
    }

    public function getOrder()
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
        $checkedGoodsPrice = CartService::getInstance()->getCartPriceCutGroupon(
            $checkedGoodsList,
            $grouponRulesId,
            $grouponPrice
        );
        $this->assertEquals(129.6, $checkedGoodsPrice);
        $input = OrderSubmitInput::new([
            'addressId' => $address->id,
            'cartId' => 0,
            'grouponRulesId' => $grouponRulesId,
            'couponId' => 0,
            'message' => '备注',
        ]);
        $order = OrderService::getInstance()->submit($this->user->id, $input);
        return $order;
    }

    /**
     * @throws Throwable
     * @throws BusinessException
     */
    public function testCancel()
    {

        $order = $this->getOrder();
        OrderService::getInstance()->userCancel($this->user->id, $order->id);
        $this->assertEquals(OrderEnums::STATUS_CANCEL, $order->refresh()->order_status);
        $goodsList = OrderService::getInstance()->getOrderGoodsList($order->id);
        $productIds = $goodsList->pluck('product_id')->toArray();
        $products = GoodsServices::getInstance()->getGoodsProductByIds($productIds);
        $this->assertEquals([100, 100], $products->pluck('number')->toArray());
    }

    public function testCas()
    {
        //        $user = User::first(['id','nickname','mobile','update_time']);
        $user = User::query()->where('id', $this->user->id)->first(['id', 'nickname', 'mobile', 'update_time']);
        $user->nickname = 'test1';
        $user->mobile = '15000000000';
        $is = $user->cas();
        $this->assertEquals(1, $is);
        $this->assertEquals('test1', User::find($this->user->id)->nickname);
        User::query()->where('id', $this->user->id)->update(['nickname' => 'test2']);
        $is = $user->cas();
        $this->assertEquals(0, $is);
        $this->assertEquals('test2', User::find($this->user->id)->nickname);
        $user->save();
    }

    //    public function testpayOrder(){
    //        $order = $this->getOrder()->refresh();
    //        OrderService::getInstance()->payOrder($order,'payid_test');
    //        dd($order->refresh()->toArray());
    //
    //    }

    /**
     * 主流程
     * @return void
     * @throws BusinessException
     * @throws Throwable
     */
    public function testBaseProcess()
    {
        $order = $this->getOrder()->refresh();
        OrderService::getInstance()->payOrder($order, 'payid_test');
        $this->assertEquals(OrderEnums::STATUS_PAY, $order->refresh()->order_status);
        $this->assertEquals('payid_test', $order->pay_id);

        $shipSn = '1234567';
        $shipChannel = 'shunfeng';
        OrderService::getInstance()->ship($this->user->id, $order->id, $shipSn, $shipChannel);
        $order->refresh();
        $this->assertEquals(OrderEnums::STATUS_SHIP, $order->order_status);
        $this->assertEquals($shipSn, $order->ship_sn);
        $this->assertEquals($shipChannel, $order->ship_channel);

        OrderService::getInstance()->confirm($this->user->id, $order->id);
        $order->refresh();
        $this->assertEquals(2, $order->comments);
        $this->assertEquals(OrderEnums::STATUS_CONFIRM, $order->order_status);

        OrderService::getInstance()->delete($this->user->id, $order->id);
        $this->assertNull(Order::find($order->id));
    }

    /**
     * 退款流程 - 简易售后流程
     * @return void
     * @throws BusinessException
     * @throws Throwable
     */
    public function testRefundProcess()
    {
        $order = $this->getOrder();
        OrderService::getInstance()->payOrder($order, 'payid_test');
        $this->assertEquals(OrderEnums::STATUS_PAY, $order->refresh()->order_status);
        $this->assertEquals('payid_test', $order->pay_id);

        OrderService::getInstance()->refund($this->user->id, $order->id);
        $order->refresh();
        $this->assertEquals(OrderEnums::STATUS_REFUND, $order->order_status);

        $refundType = '微信退款接口';
        $refundContent = '1234567';
        OrderService::getInstance()->agreeRefund($order, $refundType, $refundContent);
        $order->refresh();
        $this->assertEquals(OrderEnums::STATUS_REFUND_CONFIRM, $order->order_status);
        $this->assertEquals($refundType, $order->refund_type);
        $this->assertEquals($refundContent, $order->refund_content);

        OrderService::getInstance()->delete($this->user->id, $order->id);
        $this->assertNull(Order::find($order->id));
    }

    public function testOrderStatusTrait()
    {
        $order = $this->getOrder();
        $this->assertEquals(true, $order->isCreateStatus());
        $this->assertEquals(false, $order->isCancelStatus());
        $this->assertEquals(false, $order->isPayStatus());

        $this->assertEquals(true, $order->canCancelHandle());
        $this->assertEquals(true, $order->canPayHandle());
        $this->assertEquals(false, $order->canDeleteHandle());
        $this->assertEquals(false, $order->canConfirmHandle());
    }

    public function testArr()
    {
        $array = [
          'product-one'=>['name'=>'Desk 1', 'price'=>100],
          'product-two'=>['name'=>'Desk 2', 'price'=>200]
        ];
        $array = data_get($array,'*.name');
        dd($array);
    }
}
