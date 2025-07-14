<?php


use App\Models\Goods\GoodsProduct;
use App\Models\User\User;
use App\Services\Order\CartService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CartTest extends TestCase
{
    use DatabaseTransactions;

    /** @var User $user */
    private $user;
    /** @var GoodsProduct $product */
    private $product;

    private $authHeader;
    public function setUp():void{
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->product = factory(GoodsProduct::class)->create(['number'=>10]);
        $this->authHeader = $this->getAuthHeader($this->user->username,'123456');

    }
    public function testAdd(){
        $response = $this->post('wx/cart/add',[
            'goodsId' => 0,
            'productId' => 0,
            'number'=>1,
        ],$this->authHeader);
        $response->assertJson(['errno'=>402,'errmsg'=>'参数值不对']);

        $response = $this->post('wx/cart/add',[
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'=>11,
        ],$this->authHeader);
        $response->assertJson([
            "errno"=>711,
            "errmsg"=>"库存不足"
            ]);
        $response = $this->post('wx/cart/add',[
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'=>2,
        ],$this->authHeader);
        $response->assertJson([
            "errno" => 0,
            "errmsg"=>'成功',
            "data"=>2
        ]);
        $response = $this->post('wx/cart/add',[
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'=>3,
        ],$this->authHeader);
        $response->assertJson([
            "errno" => 0,
            "errmsg"=>'成功',
            "data"=>5
        ]);

        $cart = CartService::getInstance()->getCartProduct($this->user->id,$this->product->goods_id,$this->product->id);
        $this->assertEquals(5,$cart->number);

        $response = $this->post('wx/cart/add',[
            'goodsId' => $this->product->goods_id,
            'productId' => $this->product->id,
            'number'=>6,
        ],$this->authHeader);
        $response->assertJson([
            "errno"=>711,
            "errmsg"=>"库存不足"
        ]);
    }

}
