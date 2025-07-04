<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;


/**
 *
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
