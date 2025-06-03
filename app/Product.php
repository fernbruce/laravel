<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes; // protected $table = 'products';

    // protected $connection = 'mysql';

    // protected $primaryKey = 'id';

    // public $timestamps = true;
    //约定大于配置

    // const CREATED_AT = '';
    // const UPDATED_AT = '';
    protected $casts = [
        'attr' => 'array',
    ];

    protected $fillable = [
        'title', 'category_id', 'is_on_sale', 'price', 'pic_url', 'attr',
    ];

    // protected $guarded = [
        // 'id', // 主键不需要在这里定义，Eloquent 默认会处理
    // ];
    protected $hidden = [
        // 'created_at', 'updated_at', 'deleted_at', // 如果不想在序列化时返回这些字段，可以取消注释
    ];
    protected $dates = [
        'deleted_at', // 软删除时间戳
    ];

    protected $dateFormat = 'U'; // 时间戳格式化为 Unix 时间戳

    protected $appends = [
        'formatted_price', // 添加一个虚拟属性
    ];

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price / 100, 2); // 假设价格以分为单位存储
    }

}
