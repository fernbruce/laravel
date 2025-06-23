<?php

namespace Tests\Feature;

use App\Services\UserServices;
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

    public function testRegisterErrCode()
    {
        $mobile = '13800000002';
        $code = '123';
        $response = $this->post('/wx/auth/register', [
            'username' => 'test12',
            'password' => 'password',
            'mobile' => $mobile,
            'code' => $code
        ]);

        $response->assertJson(['errno' => 703, 'errmsg' => '验证码错误']);
    }

    public function testRegister()
    {

        $mobile = '13800000002';
        $code = (new UserServices)->setCaptcha($mobile);
        $response = $this->post('/wx/auth/register', [
            'username' => 'test12',
            'password' => 'password',
            'mobile' => $mobile,
            'code' => $code
        ]);
        $ret = $response->getOriginalContent();
        $response->assertStatus(200);
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
        $mobile = '13800000001';
        $code = (new UserServices)->setCaptcha($mobile);
        $response = $this->json('POST', '/wx/auth/register', [
            'username' => 'test12',
            'password' => 'password',
            'mobile' => $mobile,
            'code' => $code
        ]);
        $response->assertStatus(200);
        $ret = $response->getOriginalContent();
        $this->assertEquals(705, $ret['errno']);
    }

    public function testSendMobileCode()
    {
        $mobile = '13012345672';
        $response = $this->json('POST', '/wx/auth/regCaptcha', [
            'mobile' => $mobile,
        ]);
        // $response->assertStatus(200);
        // $ret = $response->getOriginalContent();
        // $this->assertEquals(0, $ret['errno']);
        $response->assertJson([
            'errno' => 0,
            'errmsg' => '成功',
        ]);
        $response = $this->json('POST', '/wx/auth/regCaptcha', [
            'mobile' => $mobile,
        ]);
        $response->assertJson(['errno' => 702, 'errmsg' => '验证码未超时1分钟，不能发送']);
    }
}
