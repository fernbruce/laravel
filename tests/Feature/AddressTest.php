<?php

namespace Tests\Feature;

use App\Models\Address;
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

    public function testAddress()
    {
        $response = $this->get('wx/address/list', $this->getAuthHeader());
        dd($this->getAuthHeader());
        // dd($response->getOriginalContent());
        $client = new Client();
        $response2 = $client->get('http://47.99.102.217:8080/wx/address/list', ['headers' => ['X-Litemall-Token' => $this->token]]);
        $list = json_decode($response2->getBody()->getContents(), true);
        // dd($list);
        $response->assertJson($list);
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
}
