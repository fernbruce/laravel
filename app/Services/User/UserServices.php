<?php

namespace App\Services\User;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Models\User\User;
use App\Notifications\VerificationCode;
use App\Services\BaseServices;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class UserServices extends BaseServices
{
    public function getUserById($id){
        return User::find($id);
    }
    public function getUsers(array $userIds)
    {
        if (empty($userIds)) {
            return collect([]);
        }
        return User::query()->whereIn('id', $userIds)->get();
    }

    /**
     * 根据用户名获取用户
     * @param [string] $username
     * @return User|null|Model
     */
    public function getByUsername($username)
    {
        return User::query()->where('username', $username)->first();
    }

    /**
     *
     * @param [string] $mobile
     * @return User|null|Model
     */
    public function getByMobile($mobile)
    {
        return User::query()->where('mobile', $mobile)->first();
    }

    /**
     * 检查手机号一天之内是否发送查过10次
     *
     * @param [string] $mobile
     * @return boolean
     */
    public function checkMobileSendCaptchaCount($mobile)
    {
        $countKey = 'register_captcha_count_'.$mobile;
        if (Cache::has($countKey)) {
            $count = Cache::increment($countKey);
            if ($count > 10) {
                return false;
            }
        } else {
            Cache::put($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        }
        return true;
    }


    /**
     * 发送验证码短信
     *
     * @param  string  $mobile
     * @param  string  $code
     * @return void
     */
    public function sendCaptchaMsg(string $mobile, string $code)
    {
        if (app()->environment('testing')) {
            return;
        }
        Notification::route(EasySmsChannel::class, new PhoneNumber($mobile, 86))
            ->notify(new VerificationCode($code));
    }

    /**
     * 验证短信验证码
     *
     * @param  string  $mobile
     * @param  string  $code
     * @return bool
     */
    public function checkCaptcha(string $mobile, string $code)
    {

        $key = 'register_captcha_'.$mobile;
        $isPass = Cache::get($key) === $code;
        if ($isPass) {
            Cache::forget($key);
            return true;
        } else {
            throw new BusinessException(CodeResponse::AUTH_CAPTCHA_UNMATCH);
        }
    }

    /**
     * 设置手机验证码
     *
     * @param  string  $mobile
     * @return string
     */
    public function setCaptcha(string $mobile): string
    {
        $key = 'register_captcha_'.$mobile;
        $code = strval(rand(100000, 999999));
        if (!app()->environment('production')) {
            $code = '111111';
        }
        Cache::put($key, $code, 600);
        return $code;
    }
}
