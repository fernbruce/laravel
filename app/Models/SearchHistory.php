<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;


/**
 * App\Models\SearchHistory
 *
 * @property int $id
 * @property int $user_id 用户表的用户ID
 * @property string $keyword 搜索关键字
 * @property string $from 搜索来源，如pc、wx、app
 * @property \Illuminate\Support\Carbon|null $add_time 创建时间
 * @property \Illuminate\Support\Carbon|null $update_time 更新时间
 * @property bool|null $deleted 逻辑删除
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereAddTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereUpdateTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SearchHistory whereUserId($value)
 * @mixin \Eloquent
 */
class SearchHistory extends BaseModel
{
    use Notifiable;




    protected $table = 'search_history';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [];
    protected $fillable = [
        'user_id',
        'keyword',
        'from'
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
}
