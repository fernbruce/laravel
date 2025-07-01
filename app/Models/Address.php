<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 *
 */
class Address extends Model
{
    use Notifiable;

    public function __construct()
    {
        $this->attributes['addTime'] = $this->attributes['expires_at']
            ?? now()->addDays(30)->toDateTimeString();

        $this->attributes['updateTime'] = $this->attributes['expires_at']
            ?? now()->addDays(30)->toDateTimeString();
    }
    // public $timestamps = false;
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    protected $table = 'address';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

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
        'is_default' => 'boolean',
        'add_time' => 'datetime',
        'update_time' => 'datetime',
    ];


    // 2. 创建访问器（命名规范：get[字段名]Attribute）
    public function getAddTimeAttribute($value)
    {
        if (!$value) return null;

        // 将数据库时间转为 Carbon 实例，并添加8小时
        return Carbon::parse($value)->addHours(8)->format('Y-m-d H:i:s');;
    }

    public function getUpdateTimeAttribute($value)
    {
        if (!$value) return null;

        // 将数据库时间转为 Carbon 实例，并添加8小时
        return Carbon::parse($value)->addHours(8)->format('Y-m-d H:i:s');;
    }
}
