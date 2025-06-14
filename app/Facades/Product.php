<?php
namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Product
 * @package App\Facades
 * @method static getProduct($id)
 */
class Product extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'product';
    }
}
