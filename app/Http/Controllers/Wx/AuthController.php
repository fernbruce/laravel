<?php

namespace App\Http\Controllers\Wx;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserServices;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = 'https://objectstorageapi.eu-central-1.run.claw.cloud/gg2hxe1z-test/cat.png';
        // $user->last_login_time = Carbon::now()->toDateTimeString();
        // $user->last_login_time = date('Y-m-d H:i:s');
        $user->last_login_time = date('Y-m-d H:i:s', time());
        $user->last_login_ip = $request->getClientIp();
        $user->save();
        echo "PHP Version (function): " . phpversion() . PHP_EOL;
        $data =  [
            'token' => '',
            'userInfo' => $username,
            'avatarUrl' => $user->avatar,
        ];
        return ['errno' => 0, 'errmsg' => '注册成功', 'phpversion' => phpversion(), 'timezone' => date_default_timezone_get(), 'data' => $data];
    }
}
