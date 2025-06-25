<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\VerificationCode;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Support\Facades\Validator;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Notification;
use Overtrue\EasySms\PhoneNumber;

class AuthController extends WxController
{
    protected $only = ['user'];

    public function user()
    {
        $user = Auth::guard('wx')->user();
        return $this->success($user);
    }
    public function login(Request $request)
    {
        //获取账号密码
        $username = $request->input('username');
        $password = $request->input('password');
        //数据验证
        if (empty($username) || empty($password)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        //验证账号是否存在
        $user = UserServices::getInstance()->getByUsername($username);
        if (is_null($user)) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT);
        }
        //对密码进行验证
        $isPass = Hash::check($password, $user->getAuthPassword());
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT, '账号密码不对');
        }
        //更新登录的信息
        $user->last_login_time = now()->toDateTimeString();
        $user->last_login_ip = $request->getClientIp();
        if (!$user->save()) {
            return $this->fail(CodeResponse::UPDATED_FAIL);
        }
        //获取token
        $token = Auth::guard('wx')->login($user);
        //组装数据并返回
        $data =  [
            'token' => $token,
            'userInfo' => $username,
            'avatarUrl' => $user->avatar,
        ];
        return $this->success($data);
    }
    /**
     * 用户注册
     *
     * @param Request $request
     * @return jsonResponse
     */
    public function register(Request  $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $mobile = $request->input('mobile');
        $code = $request->input('code');
        if (empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }

        $user = UserServices::getInstance()->getByUsername($username);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_NAME_REGISTERED);
        }

        $validator = Validator::make([
            'mobile' => $mobile,
        ], [
            'mobile' => ['regex:/^1[0-9]{10}$/'],
        ]);
        if ($validator->fails()) {
            return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);
        }

        $userInfo = UserServices::getInstance()->getByMobile($mobile);
        if (!is_null($userInfo)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }
        // todo 验证验证码是否正确
        UserServices::getInstance()->checkCaptcha($mobile, $code);
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
        return $this->success($data);
    }

    public function regCaptcha(Request $request)
    {
        // todo 获取手机号
        $mobile = $request->input('mobile');
        // todo 验手机号是否合法
        if (empty($mobile)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        // todo 验证手机号是否已经被注册
        $user = UserServices::getInstance()->getByMobile($mobile);
        if (!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }
        // todo 保存手机号与验证码的关系
        $key = 'register_captcha_' . $mobile;
        $lock = Cache::add('register_captcha_lock' . $mobile, 1, 60);
        if (!$lock) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY);
        }
        $isPass = UserServices::getInstance()->checkMobileSendCaptchaCount($mobile);
        if (!$isPass) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY, "验证码当前发送不应该超过10次");
        }

        // todo 防刷验证
        if (Cache::has($key)) {
            // return ['errno' => 706, 'errmsg' => '请勿重复发送验证码'];
        }

        // todo 随机生成6位验证码
        $code = UserServices::getInstance()->setCaptcha($mobile);
        // todo 发送短信
        // UserServices::getInstance()->sendCaptchaMsg($mobile, $code);
        // $data =  ['errno' => 0, 'errmsg' => '发送成功', 'data' => null];
        return $this->success();
    }
}
