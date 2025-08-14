<?php

namespace Tests\Feature;

use App\Services\User\UserServices;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

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
        $mobile = '13811111111';
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
        $code = UserServices::getInstance()->setCaptcha($mobile);
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
        $mobile = '13776774445';
        $code = UserServices::getInstance()->setCaptcha($mobile);
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

    public function testLogin()
    {
        $response = $this->json('POST', '/wx/auth/login', [
            'username' => 'tanfan',
            'password' => '123456'
        ]);
        dump($response->getOriginalContent());
        $response->assertJson(['errno' => 0, 'errmsg' => '成功']);
        dump($response->getOriginalContent()['data']['token'] ?? '');
        $this->assertNotEmpty($response->getOriginalContent()['data']['token'] ?? '');
    }

    public function testInfo()
    {
        $response = $this->post('/wx/auth/login', [
            'username' => 'user123',
            'password' => 'user123'
        ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $response2 = $this->get('wx/auth/info', ['Authorization' => "Bearer {$token}"]);
        $user = UserServices::getInstance()->getByUsername('user123');
        $response2->assertJson([
            'data' =>
            [
                'nickName' => $user->nickname,
                'gender' => $user->gender,
                'avatar' => $user->avatar,
                'mobile' => $user->mobile
            ]
        ]);
    }

    public function testLogout()
    {
        $response = $this->post('/wx/auth/login', [
            'username' => 'user123',
            'password' => 'user123'
        ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $response2 = $this->get('wx/auth/info', ['Authorization' => "Bearer {$token}"]);
        $user = UserServices::getInstance()->getByUsername('user123');
        $response2->assertJson([
            'data' =>
            [
                'nickName' => $user->nickname,
                'gender' => $user->gender,
                'avatar' => $user->avatar,
                'mobile' => $user->mobile
            ]
        ]);
        $response3  = $this->post('wx/auth/logout', ['Authorization' => "Bearer {$token}"]);
        $response3->assertJson(['errno' => 0]);
        $response4 = $this->get('wx/auth/info', ['Authorization' => "Bearer {$token}"]);
        // dd($response4->getOriginalContent());
        $response4->assertJson([
            'errno' => 501,
            'errmsg' => '请登录'
        ]);
    }


    public function testReset()
    {
        $mobile = '13776774445';
        $code = UserServices::getInstance()->setCaptcha($mobile);
        $response = $this->post('/wx/auth/reset', [
            'mobile' => '13776774445',
            'password' => 'user1234',
            'code' => $code
        ]);
        $response->assertJson([
            'errno' => 0,
        ]);
        $user = UserServices::getInstance()->getByMobile($mobile);
        $this->assertTrue(Hash::Check('user1234', $user->password));
    }

    public function testProfile()
    {
        $response = $this->post('/wx/auth/login', [
            'username' => 'user123',
            'password' => 'user123'
        ]);
        $token = $response->getOriginalContent()['data']['token'] ?? '';
        $response = $this->post('/wx/auth/profile', [
            'avatar' => '',
            'gender' => '1',
            'nickname' => 'user1234'
        ], ['Authorization' => 'Bearer ' . $token]);
        $response->assertJson(['errno' => 0]);
        $user = UserServices::getInstance()->getByUsername('user123');
        $this->assertEquals($user->nickname, 'user1234');
    }


}
