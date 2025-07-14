<?php

namespace Tests\Unit;

use App\CodeResponse;
use App\Exceptions\BusinessException;
use App\Models\Promotion\Coupon;
use App\Services\Order\CartService;
use App\Services\Promotion\CouponService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use DatabaseTransactions;

    public function testReceiveLimit()
    {

        $this->expectExceptionObject(new BusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券已经领取过'));
        // $this->expectException(BusinessException::class);
        CouponService::getInstance()->receive(1, 1);
    }
    public function testReceive()
    {

        $id = Coupon::query()->insertGetId([
            'name' => '活动优惠券',
            'desc' => '活动优惠券',
            'tag' => '满50减20',
            'total' => 0,
            'discount' => 20,
            'min' => 50,
            'limit' => 1,
            'time_type' => 0,
            'days' => 1
        ]);
        $ret = CouponService::getInstance()->receive(1, $id);
        $this->assertTrue($ret);
        // CouponUser::query()->where('id', $id)->delete();
    }
}
