<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
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

    public function testRegister()
    {

        $response = $this->post('/wx/auth/register', [
            'username' => 'test12',
            'password' => 'password',
            'mobile' => '13800000001',
            'code' => '123456'
        ]);
        // print_r($response);
        // print_r($response->getContent());
        // print_r($ret = $response->getOriginalContent());
        $ret = $response->getOriginalContent();
        $response->assertStatus(200);
        // $this->assertEquals(0, $ret['errno']);
        print_r($ret);
        $this->assertNotEmpty($ret['data'] ?? '');
    }

    public function testRegisterMobile()
    {
        $response = $this->json('POST', '/wx/auth/register', [
            'username' => 'test12',
            'password' => 'password',
            'mobile' => '138000000011',
            'code' => '123456'
        ]);
        $response->assertStatus(200);
        $ret = $response->getOriginalContent();
        $this->assertEquals(707, $ret['errno']);
    }

    public function testRegisterMobileExists()
    {
        $response = $this->json('POST', '/wx/auth/register', [
            'username' => 'test12',
            'password' => 'password',
            'mobile' => '13800000001',
            'code' => '123456'
        ]);
        $response->assertStatus(200);
        $ret = $response->getOriginalContent();
        $this->assertEquals(705, $ret['errno']);
    }
}
