<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BaseModel extends \Illuminate\Database\Eloquent\Model
{

    // public $timestamps = false;
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

    public $defaultCasts = ['deleted' => 'boolean'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        // parent::mergeCasts($this->defaultCasts);
    }

    public static function new()
    {
        return new static();
    }
    public function getTable()
    {
        return $this->table ?? Str::snake(class_basename($this));
    }


    // 2. 创建访问器（命名规范：get[字段名]Attribute）
    protected function getAddTimeAttribute($value)
    {
        if (!$value) return null;

        // 将数据库时间转为 Carbon 实例，并添加8小时
        return Carbon::parse($value)->addHours(8)->format('Y-m-d H:i:s');;
    }

    protected function getUpdateTimeAttribute($value)
    {
        if (!$value) return null;

        // 将数据库时间转为 Carbon 实例，并添加8小时
        return Carbon::parse($value)->addHours(8)->format('Y-m-d H:i:s');;
    }

    public  function toArray()
    {
        $items = parent::toArray();
        $items = array_filter($items, function ($item) {
            return !is_null($item);
        });
        $keys = array_keys($items);
        $keys = array_map(function ($item) {
            return Str::camel($item);
        }, $keys);
        $values = array_values($items);
        return array_combine($keys, $values);
    }

    //     public function serializeDate(DateTimeInterface $date)
    //     {
    //         return Carbon::instance($date)->toDateString();
    //     }
}
