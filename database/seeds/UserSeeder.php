<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => 'admin',
            'email' => 'xxx@163.com',
            'email_verified_at' => now(),
            'password' => bcrypt('admin123'), // 密码加密
            'remember_token' => str_random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
