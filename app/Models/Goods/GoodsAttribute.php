<?php

namespace App\Models\Goods;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;


/**
 * App\Models\Goods\GoodsAttribute
 *
 * @property int $id
 * @property int $goods_id 商品表的商品ID
 * @property string $attribute 商品参数名称
 * @property string $value 商品参数值
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute whereAttribute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsAttribute whereValue($value)
 * @mixin \Eloquent
 */
class GoodsAttribute extends BaseModel
{
    use Notifiable;



    protected $table = 'goods_attribute';
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
