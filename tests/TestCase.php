<?php

namespace Tests;

use App\Models\Goods\GoodsProduct;
use App\Models\User\User;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    use CreatesApplication;

    protected $token;

    /** @var User $user */
    protected $user;
    protected function setUp():void{
        parent::setUp();
        $this->user = factory(User::class)->state('address_default')->create();
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
    public function getAuthHeader($username='user123',$password='user123')
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
    public function assertLitemallApiPost($uri,  $data = [], $ignore = [])
    {
        $this->assertLitemallApi($uri, 'post', $data, $ignore);
    }
    public function assertLitemallApi($uri, $method = 'get', $data = [], $ignore = [])
    {
        $client = new Client();
        if ($method == 'get') {
            $response1 = $this->get($uri, $this->getAuthHeader());
//            dd($response1->getContent());
            $response2 = $client->get('http://47.99.102.217:8080/' . $uri, ['headers' => ['X-Litemall-Token' => $this->token]]);
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
        $content1 = json_decode($response1->getContent(), true);
        echo "laravel=>";
        print_r($content1);
        $content2 = json_decode($response2->getBody()->getContents(), true);
        echo "litemall=>";
        print_r($content2);

        foreach ($ignore as $key) {
            unset($content1[$key]);
            unset($content2[$key]);
        }
        $this->assertEquals($content2, $content1);
    }
}
