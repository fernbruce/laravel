<?php

namespace Tests\Unit;

use App\Exceptions\BusinessException;
use App\Services\User\UserServices;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class AuthTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testCheckMobileSendCaptchaCount()
    {
        $mobile = '13111111111';
        foreach (range(1, 10) as $i) {
            $isPass = UserServices::getInstance()->checkMobileSendCaptchaCount($mobile);
            $this->assertTrue($isPass);
        }
        $isPass = UserServices::getInstance()->checkMobileSendCaptchaCount($mobile);
        $this->assertFalse($isPass);
        $countKey = 'register_captcha_count_' . $mobile;
        Cache::forget($countKey);
        $isPass = UserServices::getInstance()->checkMobileSendCaptchaCount($mobile);
        $this->assertTrue($isPass);
    }

    public function testCheckCaptcha()
    {
        $mobile = '13800000002';
        $code = UserServices::getInstance()->setCaptcha($mobile);
        $isPass = UserServices::getInstance()->checkCaptcha($mobile, $code);
        $this->assertTrue($isPass);
        $this->expectException(BusinessException::class);
        $this->expectExceptionCode(703);
        $isPass = UserServices::getInstance()->checkCaptcha($mobile, $code);
    }
}
