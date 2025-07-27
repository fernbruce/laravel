<?php

namespace App\Models;

use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Throwable;

/**
 * App\Models\BaseModel
 *
 * @method static Builder|BaseModel newModelQuery()
 * @method static Builder|BaseModel newQuery()
 * @method static Builder|BaseModel query()
 * @mixin Eloquent
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 */
class BaseModel extends Model
{
    use Notifiable;
    use BooleanSoftDeletes;

    // public $timestamps = false;
    public const CREATED_AT = 'add_time';
    public const UPDATED_AT = 'update_time';

//    public $defaultCasts = ['deleted' => 'boolean'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
//         parent::mergeCasts($this->defaultCasts);
    }

    public static function new()
    {
        return new static();
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table ?? Str::snake(class_basename($this));
    }


    // 2. 创建访问器（命名规范：get[字段名]Attribute）
    protected function getAddTimeAttribute($date)
    {
        if (!$date) {
            return null;
        }

        // 将数据库时间转为 Carbon 实例，并添加8小时
        return Carbon::parse($date)->addHours(8)->format('Y-m-d H:i:s');
    }

    protected function getUpdateTimeAttribute($date)
    {
        if (!$date) {
            return null;
        }
        // 将数据库时间转为 Carbon 实例，并添加8小时
//        return Carbon::parse($date)->format('Y-m-d H:i:s');
        return Carbon::parse($date)->addHours(8)->format('Y-m-d H:i:s');
    }

    public function toArray()
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

         public function serializeDate(\DateTimeInterface $date)
         {
//             return Carbon::instance($date)->toDateString();
             return Carbon::instance($date)->format('Y-m-d H:i:s');
//             return Carbon::parse($date)->format('Y-m-d H:i:s');
         }
//
    /**
     * 乐观锁更新 compare and save
     * @return int
     * @throws Exception
     * @throws Throwable
     */
    public function cas(){
//        if(!$this->exists){
//           throw new Exception('model not exists when cas!') ;
//        }
        throw_if(!$this->exists, Exception::class, 'model not exists when cas!');
        $dirty = $this->getDirty();

        if(empty($dirty)){
            return 0;
        }
        if($this->usesTimestamps()){
            $this->updateTimestamps();
            $dirty = $this->getDirty();
        }
        $diff = array_diff(array_keys($dirty),array_keys($this->original));
        throw_if(!empty($diff), Exception::class, 'key ['.implode(',',$diff).'] not exists when cas!');

        if($this->fireModelEvent('casing') === false){
            return 0;
        }
        $updateAt = $this->getUpdatedAtColumn();
//        $query = self::query()->where($this->getKeyName(), $this->getKey());
        $query = $this->newModelQuery()->where($this->getKeyName(), $this->getKey());

        if($this->usesTimestamps()){
            unset($dirty[$updateAt]);
        }
        foreach($dirty as $key => $value){
            $query = $query->where($key, $this->getOriginal($key));
        }
//        $query->where($updateAt,Carbon::parse($this->{$updateAt})->subHours(8)->format('Y-m-d H:i:s'));
        $query->where($updateAt,Carbon::parse($this->getOriginal($updateAt))->subHours(8)->format('Y-m-d H:i:s'));
//        dd($dirty);
//        dd($query->toSql(),$query->getBindings());
//        dd($query->update($dirty));
        $row = $query->update($dirty);
        if($row>0){
            $this->syncChanges();
            $this->fireModelEvent('cased', false);
            $this->syncOriginal();
        }
        return $row;
    }

    public static function casing($callback){
          static::registerModelEvent('casing', $callback);
    }

    public static function cased($callback){
         static::registerModelEvent('cased', $callback);
    }
}
