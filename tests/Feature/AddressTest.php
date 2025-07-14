<?php

namespace Tests\Feature;

use App\Models\User\Address;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');
        print_r($response->getContent());

        $response->assertStatus(200);
    }

    public function testList()
    {
        $this->assertLitemallApiGet('wx/address/list', ['postalCode']);
        // $response = $this->get('wx/address/list', $this->getAuthHeader());
        // // dd($this->getAuthHeader());
        // // dd($response->getOriginalContent());
        // $client = new Client();
        // $response2 = $client->get('http://47.99.102.217:8080/wx/address/list', ['headers' => ['X-Litemall-Token' => $this->token]]);
        // $list = json_decode($response2->getBody()->getContents(), true);
        // print($this->token);
        // dd($list);
        // $response->assertJson($list);
    }

    public function testDelete()
    {
        $address = Address::query()->first();
        $this->assertNotNull($address);
        $response = $this->post('wx/address/delete', ['id' => $address->id], $this->getAuthHeader());
        $response->assertJson(['errno' => 0]);
        $address = Address::query()->find($address->id);
        $this->assertNull($address);
    }

    public function testSave()
    {
        $address = Address::query()->where('id', 3)->first();
        $this->assertNotNull($address);
        $data = [
            'id' => $address->id,
            "name" => "李四",
            "province" => "广东省",
            "city" => "深圳市",
            "county" => "宝安区",
            "address_detail" => "深圳宝安大道",
            "area_code" => "44111",
            "tel" => "13811111111"

        ];
        $response = $this->post('wx/address/save', $data, $this->getAuthHeader());
        $response->assertJson(['errno' => 0]);
        $address = Address::query()->find($address->id);
        $this->assertEquals($data['name'], $address->name);
    }
}
