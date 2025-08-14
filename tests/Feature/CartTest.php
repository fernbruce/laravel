<?php
namespace Tests\Feature;

use App\Models\Goods\GoodsProduct;
use App\Models\Promotion\Coupon;
use App\Models\User\User;
use App\Services\Goods\GoodsServices;
use App\Services\Order\CartService;
use App\Services\Promotion\CouponService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;

    /** @var GoodsProduct  $product*/
    private $product;



    public function setUp(): void
    {
        parent::setUp();
        $this->product = factory(GoodsProduct::class)->create(['number' => 10]);

    }

//   public function testAdd1(){
//       $product = factory(GoodsProduct::class)->create();
//       dd($product);
//   }
    public function testIndex()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 2,
        ], $this->authHeader);
        $response->assertJson([
            "errno" => 0,
            "errmsg" => '成功',
            "data" => 2
        ]);
        $response = $this->get('/wx/cart/index', [], $this->authHeader);
        $response->assertJson([
            'errno' => 0, 'errmsg' => '成功', 'data' => [
                "cartList" => [
                    [
                        'goodsId' => $this->product->goods_id,
                        'productId' => $this->product->id,
                    ]
                ],
                "cartTotal" => [
                    "goodsCount" => 2,
                    "goodsAmount" => 1998.00,
                    "checkedGoodsCount" => 2,
                    "checkedGoodsAmount" => 1998.00
                ]
            ]
        ]);
        $goods = GoodsServices::getInstance()->getGoods($this->product->goods_id);
        $goods->is_on_sale = false;
        $goods->save();
        $response = $this->get('/wx/cart/index', [], $this->authHeader);
        $response->assertJson([
            'errno' => 0, 'errmsg' => '成功', 'data' => [
                "cartList" => [],
                "cartTotal" => [
                    "goodsCount" => 0,
                    "goodsAmount" => 0,
                    "checkedGoodsCount" => 0,
                    "checkedGoodsAmount" => 0
                ]
            ]
        ]);
        $cart = CartService::getInstance()->getCartProduct($this->user->id, $this->product->goods_id,$this->product->id);
        $this->assertNull($cart);
    }

    public function testFastadd()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 2,
        ], $this->authHeader);
        $response->assertJson([
            "errno" => 0,
            "errmsg" => '成功',
            "data" => 2
        ]);
//        $response = $this->post('wx/cart/add', [
//            'goodsId' => $this->product->goods_id,
//            'productId' => $this->product->id,
//            'number' => 3,
//        ], $this->authHeader);
//        $response->assertJson([
//            "errno" => 0,
//            "errmsg" => '成功',
//            "data" => 5
//        ]);
        $response = $this->post('wx/cart/fastadd', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 5,
        ], $this->authHeader);
        $cart = CartService::getInstance()->getCartProduct($this->user->id, $this->product->goods_id, $this->product->id);
        $this->assertEquals(5, $cart->number);
        $response->assertJson([
            "errno" => 0,
            "errmsg" => '成功',
            "data" => $cart->id
        ]);

    }

    public function testAdd()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId' => 0,
            'productId' => 0,
            'number' => 1,
        ], $this->authHeader);
        $response->assertJson(['errno' => 402, 'errmsg' => '参数值不对']);

        $response = $this->post('wx/cart/add', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 11,
        ], $this->authHeader);
        $response->assertJson([
            "errno" => 711,
            "errmsg" => "库存不足"
        ]);
        $response = $this->post('wx/cart/add', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 2,
        ], $this->authHeader);
        $response->assertJson([
            "errno" => 0,
            "errmsg" => '成功',
            "data" => 2
        ]);
        $response = $this->post('wx/cart/add', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 3,
        ], $this->authHeader);
        $response->assertJson([
            "errno" => 0,
            "errmsg" => '成功',
            "data" => 5
        ]);

        $cart = CartService::getInstance()->getCartProduct($this->user->id, $this->product->goods_id,
            $this->product->id);
        $this->assertEquals(5, $cart->number);

        $response = $this->post('wx/cart/add', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 6,
        ], $this->authHeader);
        $response->assertJson([
            "errno" => 711,
            "errmsg" => "库存不足"
        ]);
    }

    public function testUpdate()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 2,
        ], $this->authHeader);
        $response->assertJson([
            "errno" => 0,
            "errmsg" => '成功',
            "data" => 2
        ]);

        $cart = CartService::getInstance()->getCartProduct($this->user->id, $this->product->goods_id,
            $this->product->id);

        $response = $this->post('wx/cart/update', [
            'id' => $cart->id,
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 6,
        ], $this->authHeader);
        $response->assertJson(["errno" => 0, "errmsg" => "成功"]);

        $response = $this->post('wx/cart/update', [
            'id' => $cart->id,
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 11,
        ], $this->authHeader);
        $response->assertJson(["errno" => 711, "errmsg" => "库存不足"]);

        $response = $this->post('wx/cart/update', [
            'id' => $cart->id,
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 0,
        ], $this->authHeader);
        $response->assertJson(["errno" => 402, "errmsg" => "参数值不对"]);

    }

    public function testDelete()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 2,
        ], $this->authHeader);
        $response->assertJson([
            "errno" => 0,
            "errmsg" => '成功',
            "data" => 2
        ]);
        $cart = CartService::getInstance()->getCartProduct($this->user->id, $this->product->goods_id,
            $this->product->id);
        $this->assertNotNull($cart);
        $resp = $this->post('wx/cart/delete', [
            'productIds' => [$this->product->id]
        ], $this->authHeader);
        $resp->assertJson([
            "errno" => 0,
            "errmsg" => "成功"
        ]);
        $cart = CartService::getInstance()->getCartProduct($this->user->id, $this->product->goods_id,
            $this->product->id);
        $this->assertNull($cart);

        $resp = $this->post('wx/cart/delete', [
            'productIds' => []
        ], $this->authHeader);
        $resp->assertJson([
            "errno" => 402,
            "errmsg" => "参数值不对",
        ]);
    }

    public function testChecked()
    {
        $response = $this->post('wx/cart/add', [
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number' => 2,
        ], $this->authHeader);
        $response->assertJson([
            "errno" => 0,
            "errmsg" => '成功',
            "data" => 2
        ]);
        $cart = CartService::getInstance()->getCartProduct($this->user->id, $this->product->goods_id,
            $this->product->id);
        $this->assertTrue($cart->checked);
        $resp = $this->post('wx/cart/checked', [
            'productIds' => [$this->product->id],
            'isChecked' => 0
        ]);
        $cart = CartService::getInstance()->getCartProduct($this->user->id, $this->product->goods_id,
            $this->product->id);
        $this->assertFalse($cart->checked);

        $resp = $this->post('wx/cart/checked', [
            'productIds' => [$this->product->id],
            'isChecked' => 1
        ]);
        $cart = CartService::getInstance()->getCartProduct($this->user->id, $this->product->goods_id,
            $this->product->id);
        $this->assertTrue($cart->checked);

    }

    public function testCheckout(){
//        $resp = $this->get('wx/cart/checkout',$this->getAuthHeader());

//        $id = Coupon::query()->insertGetId([
//            'name' => '活动优惠券',
//            'desc' => '活动优惠券',
//            'tag' => '满50减20',
//            'total' => 0,
//            'discount' => 20,
//            'min' => 50,
//            'limit' => 1,
//            'time_type' => 0,
//            'days' => 1
//        ]);
//        $ret = CouponService::getInstance()->receive($this->user->id, $id);
        $this->assertLitemallApiGet('wx/cart/checkout?couponId=0');
    }
}
