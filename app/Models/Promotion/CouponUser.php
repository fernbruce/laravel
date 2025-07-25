<?php

namespace App\Models\Promotion;

use App\Models\BaseModel;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * App\Models\Promotion\CouponUser
 *
 * @property int $id
 * @property int $user_id 用户ID
 * @property int $coupon_id 优惠券ID
 * @property int|null $status 使用状态, 如果是0则未使用；如果是1则已使用；如果是2则已过期；如果是3则已经下架；
 * @property string|null $used_time 使用时间
 * @property string|null $start_time 有效期开始时间
 * @property string|null $end_time 有效期截至时间
 * @property int|null $order_id 订单ID
 * @property Carbon|null $add_time 创建时间
 * @property Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static Builder|CouponUser newModelQuery()
 * @method static Builder|CouponUser newQuery()
 * @method static Builder|CouponUser query()
 * @method static Builder|CouponUser whereAddTime($value)
 * @method static Builder|CouponUser whereCouponId($value)
 * @method static Builder|CouponUser whereDeleted($value)
 * @method static Builder|CouponUser whereEndTime($value)
 * @method static Builder|CouponUser whereId($value)
 * @method static Builder|CouponUser whereOrderId($value)
 * @method static Builder|CouponUser whereStartTime($value)
 * @method static Builder|CouponUser whereStatus($value)
 * @method static Builder|CouponUser whereUpdateTime($value)
 * @method static Builder|CouponUser whereUsedTime($value)
 * @method static Builder|CouponUser whereUserId($value)
 * @mixin Eloquent
 */
class CouponUser extends BaseModel
{


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'coupon_id',
        'user_id',
        'start_time',
        'end_time'
    ];


    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'deleted' => 'boolean',
    ];


    protected function getStartTimeAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // 将数据库时间转为 Carbon 实例，并添加8小时
        return Carbon::parse($value)->addHours(8)->format('Y-m-d H:i:s');
    }

    protected function getEndTimeAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // 将数据库时间转为 Carbon 实例，并添加8小时
        return Carbon::parse($value)->addHours(8)->format('Y-m-d H:i:s');
    }
}
