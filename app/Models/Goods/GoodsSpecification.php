<?php

namespace App\Models\Goods;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;


/**
 * App\Models\Goods\GoodsSpecification
 *
 * @property int $id
 * @property int $goods_id 商品表的商品ID
 * @property string $specification 商品规格名称
 * @property string $value 商品规格值
 * @property string $pic_url 商品规格图片
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification wherePicUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification whereSpecification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsSpecification whereValue($value)
 * @mixin \Eloquent
 */
class GoodsSpecification extends BaseModel
{
    use Notifiable;



    protected $table = 'goods_specification';
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
