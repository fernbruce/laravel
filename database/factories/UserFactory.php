<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\User\Address;
use App\Models\User\User;
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

$factory->define(User::class, function (Faker $faker) {
    $name = $faker->name;
    return [
        'username' => $name,
        'password' => \Illuminate\Support\Facades\Hash::make('123456'), // password
        'gender' => $faker->randomKey([0, 1, 2]),
        'mobile' => $faker->phoneNumber,
//        'avatar' => $faker->imageUrl(),
        'avatar' => 'https://objectstorageapi.eu-central-1.run.claw.cloud/gg2hxe1z-test/cat.png',
        'nickname'=>'nicknameOf'.$name
    ];
});

$factory->define(Address::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'user_id' => 0,
        'province' => '浙江省',
        'city' => '杭州市',
        'county' => '西湖区',
        'address_detail' => $faker->streetAddress,
        'area_code' => '',
        'postal_code' => $faker->postcode,
        'tel' => $faker->phoneNumber,
        'is_default' => 0,
    ];
});

$factory->state(User::class,'address_default', function(){
       return [];
})->afterCreatingState(User::class, 'address_default', function($user){
    factory(Address::class, 1)->create([
        'user_id' => $user->id,
        'is_default' => 1
    ]);
});
