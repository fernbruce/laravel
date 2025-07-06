<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use App\Models\BaseModel;


/**
 *
 */
class Comment extends BaseModel
{
    use Notifiable;




    protected $table = 'comment';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $fillable = [];
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
        'pic_urls' => 'array',
    ];
}
