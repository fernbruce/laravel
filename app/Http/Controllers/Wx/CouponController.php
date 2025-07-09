<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Http\Controllers\Wx\WxController;
use App\Services\Promotion\CouponService;
use App\Inputs\PageInput;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use Illuminate\Support\Carbon;

class CouponController extends WxController
{
    protected $except = ['list'];
    /**
     * 优惠券列表
     * @return \Illuminate\Http\JsonResponse
     * @throws BusinessException
     */
    public function list()
    {

        $page = PageInput::new();
        $columns = ['id', 'name', 'desc', 'tag', 'discount', 'min', 'days', 'start_time', 'end_time'];
        $list = CouponService::getInstance()->list($page, $columns);
        return $this->successPaginate($list);
    }

    /**
     * 我的优惠券列表
     * @return \Illuminate\Http\JsonResponse
     * @throws BusinessException
     */
    public function mylist()
    {
        $status = $this->verifyInteger('status', 0);
        $page = PageInput::new();
        $list = CouponService::getInstance()->mylist($this->userId(), $status, $page);
        $couponUserList = collect($list->items());
        $couponIds =  $couponUserList->pluck('coupon_id')->toArray();
        $coupons = CouponService::getInstance()->getCoupons($couponIds)->keyBy('id');
        $mylist = $couponUserList->map(function (CouponUser $item) use ($coupons) {
            $coupon = $coupons->get($item->coupon_id);
            return [
                'id' => $item->id,
                'cid' => $coupon->id,
                'name' => $coupon->name,
                'desc' => $coupon->desc,
                'tag' => $coupon->tag,
                'min' => $coupon->min,
                'discount' => $coupon->discount,
                'startTime' => $item->start_time,
                'endTime' => $item->end_time,
                'available' => false
            ];
        });
        $list = $this->paginate($list, $mylist);
        return $this->success($list);
    }

    /**
     * 领取优惠券
     * @return JsonResponse
     * @throws BusinessException
     */
    public function receive()
    {
        $couponId = $this->verifyId('couponId', 0);
        CouponService::getInstance()->receive($this->userId(), $couponId);
        return $this->success();
    }
}
