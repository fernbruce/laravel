<?php

namespace App\Models\User;

use App\Models\BaseModel;
use Eloquent;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * App\Models\User\User
 *
 * @property int $id
 * @property string $username 用户名称
 * @property string $password 用户密码
 * @property int $gender 性别：0 未知， 1男， 1 女
 * @property string|null $birthday 生日
 * @property string|null $last_login_time 最近一次登录时间
 * @property string $last_login_ip 最近一次登录IP地址
 * @property int|null $user_level 0 普通用户，1 VIP用户，2 高级VIP用户
 * @property string $nickname 用户昵称或网络名称
 * @property string $mobile 用户手机号码
 * @property string $avatar 用户头像图片
 * @property string $weixin_openid 微信登录openid
 * @property string $session_key 微信登录会话KEY
 * @property int $status 0 可用, 1 禁用, 2 注销
 * @property Carbon|null $add_time 创建时间
 * @property Carbon|null $update_time 更新时间
 * @property int|null $deleted 逻辑删除
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereAddTime($value)
 * @method static Builder|User whereAvatar($value)
 * @method static Builder|User whereBirthday($value)
 * @method static Builder|User whereDeleted($value)
 * @method static Builder|User whereGender($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLastLoginIp($value)
 * @method static Builder|User whereLastLoginTime($value)
 * @method static Builder|User whereMobile($value)
 * @method static Builder|User whereNickname($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User whereSessionKey($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereUpdateTime($value)
 * @method static Builder|User whereUserLevel($value)
 * @method static Builder|User whereUsername($value)
 * @method static Builder|User whereWeixinOpenid($value)
 * @mixin Eloquent
 */
class User extends BaseModel implements
    JWTSubject,
    AuthenticatableContract,
    AuthorizableContract
{
    use Authenticatable, Authorizable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'mobile',
        'nickname',
        'last_login_time',
        'last_login_ip'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'deleted',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'iss' => env('JWT_ISSUER'),
            'userId' => $this->getKey()
        ];
    }
}
