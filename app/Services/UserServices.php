<?php

namespace App\Services;

use App\Models\User;

class UserServices
{
    /**
     *
     * @param [string] $username
     * @return User|null|Model
     */
    public function getByUser($username)
    {
        return User::query()->where('username', $username)->where('deleted', 0)->first();
    }

    /**
     *
     * @param [string] $mobile
     * @return User|null|Model
     */
    public function getByMobile($mobile)
    {
        return User::query()->where('mobile', $mobile)->where('deleted', 0)->first();
    }
}
