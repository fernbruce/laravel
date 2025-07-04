<?php

namespace App\Models\User;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use App\Models\BaseModel;

/**
 *
 */
class Address extends BaseModel
{
    use Notifiable;



    protected $table = 'address';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    // protected $fillable = ['name', 'province', 'city', 'county', 'address_detail', 'area_code', 'postal_code', 'tel'];
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
        'is_default' => 'boolean',
        'add_time' => 'datetime',
        'update_time' => 'datetime',
    ];
}
