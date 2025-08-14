<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\Goods\GoodsSpecification;
use App\Models\Promotion\GrouponRules;
use App\Services\Goods\GoodsServices;
use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(Goods::class, function (Faker $faker) {
    return [
        "goods_sn" => $faker->word,
        "name" => "测试商品".$faker->word,
        "category_id" => 1008009,
        "brand_id" => 0,
        "gallery" => [],
        "keywords" => "",
        "brief" => "测试",
        "is_on_sale" => 1,
        "sort_order" => $faker->numberBetween(1, 999),
//        "pic_url" => $faker->imageUrl(),
        "pic_url" => 'http://laravel.test/storage/groupon/2025-08-10/h8KhU4zNpRxC1e0E.png',
        "share_url" => $faker->url,
        "is_new" => $faker->boolean,
        "is_hot" => $faker->boolean,
        "unit" => "件",
        "counter_price" => 919,
        "retail_price" => 899,
        "detail" => $faker->text
    ];
});

$factory->define(GoodsProduct::class, function (Faker $faker) {
    $goods = factory(Goods::class)->create();
    $spec = factory(GoodsSpecification::class)->create(['goods_id'=>$goods->id]);
    return [
        "goods_id" => $goods->id,
        "specifications" => [$spec->value],
        "price" => 999,
        "number" => 100,
        "url" => $faker->imageUrl(),
    ];
});

$factory->define(GoodsSpecification::class, function (Faker $faker) {
    return [
        "goods_id" => 0,
        "specification" => '规格',
        "value" => '标准'
    ];
});

$factory->define(GrouponRules::class, function (Faker $faker) {
     return [
         'goods_id' => 0,
         'goods_name'=>'',
         'pic_url'=>'',
         'discount'=>0,
         'discount_member'=>2,
         'expire_time'=>now()->addDays(10)->toDateTimeString(),
         'status'=>0
     ];
});

$factory->state(GoodsProduct::class,'groupon',function(){
   return [];
})->afterCreatingState(GoodsProduct::class, 'groupon', function (GoodsProduct $goodsProduct, Faker $faker) {
    /** @var Goods $goods */
    $goods = GoodsServices::getInstance()->getGoods($goodsProduct->goods_id);
   factory(GrouponRules::class)->create([
       'goods_id'=>$goodsProduct->goods_id,
       'goods_name'=>$goods->name,
       'pic_url'=>$goods->pic_url,
       'discount'=>1,
   ]);

});
