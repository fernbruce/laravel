<?php

namespace App\Models\Goods;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;


/**
 * App\Models\Goods\Footprint
 *
 * @property int $id
 * @property int $user_id 用户表的用户ID
 * @property int $goods_id 浏览商品ID
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint query()
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Footprint whereUserId($value)
 * @mixin \Eloquent
 */
class Footprint extends BaseModel
{
    use Notifiable;



    protected $table = 'footprint';
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
    ];
}
