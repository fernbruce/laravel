<?php

namespace App\Models\Promotion;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;
use Illuminate\Support\Carbon;

/**
 *
 */
class CouponUser extends BaseModel
{
    use Notifiable;




    protected $table = 'coupon_user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [];

    protected $fillable = [
        'coupon_id',
        'user_id',
        'start_time',
        'end_time'
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

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
        if (!$value) return null;

        // 将数据库时间转为 Carbon 实例，并添加8小时
        return Carbon::parse($value)->addHours(8)->format('Y-m-d H:i:s');;
    }

    protected function getEndTimeAttribute($value)
    {
        if (!$value) return null;

        // 将数据库时间转为 Carbon 实例，并添加8小时
        return Carbon::parse($value)->addHours(8)->format('Y-m-d H:i:s');;
    }
}
