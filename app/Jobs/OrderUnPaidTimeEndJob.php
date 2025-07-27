<?php

namespace App\Jobs;

use App\Exceptions\BusinessException;
use App\Models\System;
use App\Services\Order\OrderService;
use App\Services\SystemServices;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;

class OrderUnPaidTimeEndJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $userId;
    private $orderId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userId,$orderId)
    {
        $this->userId = $userId;
        $this->orderId = $orderId;
        $delayTime = SystemServices::getInstance()->getOrderUnpaidDelayMinutes();
        $this->delay(now()->addMinutes($delayTime));
//        $this->delay(now()->addSeconds(10));
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws BusinessException
     * @throws Throwable
     */
    public function handle()
    {
        try{
            OrderService::getInstance()->systemCancel($this->userId,$this->orderId);
        }catch (BusinessException $e){

        }
    }
}
