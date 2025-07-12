<?php

namespace App\Models\Goods;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;


/**
 * App\Models\Goods\GoodsProduct
 *
 * @property int $id
 * @property int $goods_id 商品表的商品ID
 * @property array $specifications 商品规格值列表，采用JSON数组格式
 * @property float $price 商品货品价格
 * @property int $number 商品货品数量
 * @property string|null $url 商品货品图片
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereGoodsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereSpecifications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GoodsProduct whereUrl($value)
 * @mixin \Eloquent
 */
class GoodsProduct extends BaseModel
{
    use Notifiable;



    protected $table = 'goods_product';
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
        'specifications' => 'array',
        'price' => 'float',
    ];
}
