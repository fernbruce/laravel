<?php

namespace Tests;

use App\Inputs\OrderSubmitInput;
use App\Models\Goods\GoodsProduct;
use App\Models\System;
use App\Models\User\User;
use App\Services\Order\CartService;
use App\Services\Order\OrderService;
use App\Services\SystemServices;
use App\Services\User\AddressServices;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    use CreatesApplication;

    protected $token;

    /** @var User $user */
    protected $user;

    protected $authHeader;

    protected function setUp(): void
    {
        parent::setUp();
        //         $this->user = factory(User::class)->state('address_default')->create();
        // //        $this->user = User::find(465);
        // //        $this->user = User::find(1);
        //         if($this->user->id == 1){
        //             $this->authHeader = $this->getAuthHeader();
        //         }else{
        //             $this->authHeader = $this->getAuthHeader($this->user->username,'123456');
        //         }
    }

    //    /** @var User $user */
    //    protected $user;
    //    /** @var GoodsProduct $product */
    //    protected $product;
    //
    //    protected $authHeader;

    //    public function setUp(): void
    //    {
    //        parent::setUp();
    //        $this->user = factory(User::class)->create();
    //        $this->product = factory(GoodsProduct::class)->create(['number' => 10]);
    //        $this->authHeader = $this->getAuthHeader($this->user->username, '123456');
    //
    //    }
    public function getAuthHeader($username = 'user123', $password = 'user123')
    {
        $response = $this->post('/wx/auth/login', [
            'username' => $username,
            'password' => $password
        ]);
        $this->token = $response->getOriginalContent()['data']['token'] ?? '';
        return ['Authorization' => 'Bearer ' . $this->token];
    }


    public function assertLitemallApiGet($uri, $ignore = [])
    {
        $this->assertLitemallApi($uri, 'get', [], $ignore);
    }

    public function assertLitemallApiPost($uri, $data = [], $ignore = [])
    {
        $this->assertLitemallApi($uri, 'post', $data, $ignore);
    }

    public function assertLitemallApi($uri, $method = 'get', $data = [], $ignore = [])
    {
        $client = new Client();
        if ($method == 'get') {
            $response1 = $this->get($uri, $this->getAuthHeader());
            //            dd($response1->getContent());
            $response2 = $client->get(
                'http://47.99.102.217:8080/' . $uri,
                ['headers' => ['X-Litemall-Token' => $this->token]]
            );
        } else {
            $response1 = $this->post($uri, $data, $this->getAuthHeader());
            $response2 = $client->post(
                'http://47.99.102.217:8080/' . $uri,
                [
                    'headers' => ['X-Litemall-Token' => $this->token],
                    'json' => $data
                ]
            );
        }

        // $content = $response2->getBody()->getContents();
        // $content = json_decode($content, true);
        // $response1->assertJson($content);

        //        echo "laravel=>";
        //        print_r($content1);
        $content1 = $response1->getContent();
        echo "laravel=>" . json_encode(json_decode($content1), JSON_UNESCAPED_UNICODE) . PHP_EOL;
        $content1 = json_decode($content1, true);
        //        echo "litemall=>";
        //        print_r($content2);
        $content2 = $response2->getBody()->getContents();
        echo "litemall=>$content2" . PHP_EOL;
        $content2 = json_decode($content2, true);
        foreach ($ignore as $key) {
            unset($content1[$key]);
            unset($content2[$key]);
        }
        $this->assertEquals($content2, $content1);
    }

    public function getSimpleOrder($options = [[11.3, 2], [2.3, 1], [81.4, 4]])
    {
        $this->getAuthHeader($this->user->username, '123456');
        $address = AddressServices::getInstance()->getDefaultAddress($this->user->id);

        foreach ($options as list($price, $num)) {
            $product = factory(GoodsProduct::class)->create(['price' => $price]);
            CartService::getInstance()->add($this->user->id, $product->goods_id, $product->id, $num);
        }
        $input = OrderSubmitInput::new([
            'addressId' => $address->id,
            'cartId' => 0,
            'couponId' => 0,
            'grouponRulesId' => 0,
            'message' => '备注'
        ]);

        $order = OrderService::getInstance()->submit($this->user->id, $input);
        //      $order->actual_price -= $order->freight_price;
        $order->save();
        return $order;
    }
}
