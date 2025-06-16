<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerificationCode;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Support\Facades\Validator;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Notification;
use Overtrue\EasySms\PhoneNumber;

class AuthController extends Controller
{
    public function register(Request  $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');
        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return response()->json(['errno' => '1001', 'errmsg' => '参数错误']);
        }

        $user = (new UserServices())->getByUser($username);
        if (!is_null($user)) {
            return ['errno' => 704, 'errmsg' => '用户名已经注册'];
        }

        $validator = Validator::make([
            'mobile' => $mobile,
        ], [
            'mobile' => ['regex:/^1[0-9]{10}$/'],
        ]);
        if ($validator->fails()) {
            return ['errno' => 707, 'errmsg' => '手机格式不正确'];
        }

        $userInfo = (new UserServices())->getByMobile($mobile);
        if (!is_null($userInfo)) {
            return ['errno' => 705, 'errmsg' => '手机号已经存在'];
        }
        // todo 验证验证码是否正确
        $isPass = (new UserServices())->checkCaptcha($mobile, $code);
        if (!$isPass) {
            return ['errno' => 703, 'errmsg' => '验证码错误'];
        }
        //写入用户表
        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = 'https://objectstorageapi.eu-central-1.run.claw.cloud/gg2hxe1z-test/cat.png';
        $user->last_login_time = Carbon::now()->toDateTimeString();
        $user->last_login_ip = $request->getClientIp();
        $user->save();
        $data =  [
            'token' => '',
            'userInfo' => $username,
            'avatarUrl' => $user->avatar,
        ];
        return ['errno' => 0, 'errmsg' => '注册成功', 'phpversion' => phpversion(), 'timezone' => date_default_timezone_get(), 'data' => $data];
    }

    public function regCaptcha(Request $request)
    {
        // todo 获取手机号
        $mobile = $request->input('mobile');
        // todo 验手机号是否合法
        if (empty($mobile)) {
            return ['errno' => 401, 'errmsg' => '参数不'];
        }
        // todo 验证手机号是否已经被注册
        $user = (new UserServices)->getByMobile($mobile);
        if (!is_null($user)) {
            return ['errno' => 705, 'errmsg' => '手机号已经被注册'];
        }
        // todo 保存手机号与验证码的关系
        $key = 'register_captcha_' . $mobile;
        $lock = Cache::add('register_captcha_lock' . $mobile, 1, 60);
        if (!$lock) {
            return ['errno' => 702, 'errmsg' => '验证码未超时1分钟，不能发送'];
        }
        $isPass = (new UserServices())->checkMobileSendCaptchaCount($mobile);
        if (!$isPass) {
            return ['errno' => 703, 'errmsg' => '验证码当天发送次数超过10次'];
        }

        // todo 防刷验证
        if (Cache::has($key)) {
            // return ['errno' => 706, 'errmsg' => '请勿重复发送验证码'];
        }

        // todo 随机生成6位验证码
        $code = (new UserServices())->setCaptcha($mobile);
        // todo 发送短信
        (new UserServices)->sendCaptchaMsg($mobile, $code);
        return ['errno' => 0, 'errmsg' => '发送成功', 'data' => null];
    }
}
