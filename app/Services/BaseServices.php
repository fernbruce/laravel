<?php

namespace App\Services;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Models\User;
use App\Notifications\VerificationCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class BaseServices
{
    protected static $instance;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!static::$instance instanceof static) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct() {}

    private function __clone() {}

    public function throwBusinessException(array $codeResponse)
    {
        throw new BusinessException($codeResponse);
    }
}
