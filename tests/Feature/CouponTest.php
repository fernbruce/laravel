<?php

namespace Tests\Feature;

use App\Models\Address;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use DatabaseTransactions;

    public function testList()
    {
        $this->assertLitemallApiGet('wx/coupon/list');
    }

    public function testMylist()
    {
        // $this->assertLitemallApiGet('wx/coupon/mylist');
        // $this->assertLitemallApiGet('wx/coupon/mylist?status=0');
        // $this->assertLitemallApiGet('wx/coupon/mylist?status=1');
        $this->assertLitemallApiGet('wx/coupon/mylist?status=2');
    }
}
