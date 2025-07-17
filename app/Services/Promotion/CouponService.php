<?php

namespace App\Services\Promotion;

use App\CodeResponse;
use App\Constant;
use App\Enums\CouponEnums;
use App\Enums\CouponUserEnums;
use App\Exceptions\BusinessException;
use App\Inputs\PageInput;
use App\Models\Promotion\Coupon;
use App\Models\Promotion\CouponUser;
use App\Services\BaseServices;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class CouponService extends BaseServices
{

    public function list(PageInput $page, $columns = ['*'])
    {
        return Coupon::query()->where('type', Constant::COUPON_TYPE_COMMON)
            ->where('status', Constant::COUPON_STATUS_NORMAL)
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }

    public function mylist($userId, $status, PageInput $page, $columns = ['*'])
    {
        return CouponUser::query()
            ->when(!is_null($status), function (Builder $query) use ($status) {
                return $query->where('status', $status);
            })
            ->where('user_id', $userId)
            ->orderBy($page->sort, $page->order)
            ->paginate($page->limit, $columns, 'page', $page->page);
    }

    /**
     * @param [type] $userId
     * @param [type] $couponId
     * @return bool
     * @throws BusinessException
     */
    public function receive($userId, $couponId)
    {
        $coupon = CouponService::getInstance()->getCoupon($couponId);
        if (is_null($coupon)) {
            $this->throwBusinessException(CodeResponse::PARAM_ILLEGAL);
        }
        if ($coupon->total > 0) {
            $fetchedCount = CouponService::getInstance()->countCoupon($couponId);
            if ($fetchedCount >= $coupon->total) {
                $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT);
            }
        }

        if ($coupon->limit > 0) {
            $userFetchCount = CouponService::getInstance()->countCouponByUserId($userId, $couponId);
            if ($userFetchCount >= $coupon->limit) {
                $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT, '优惠券已经领取过');
            }
        }

        if ($coupon->type != Constant::COUPON_TYPE_COMMON) {
            $this->throwBusinessException(CodeResponse::COUPON_RECEIVE_FAIL, '优惠券类型不支持');
        }

        if ($coupon->status == Constant::COUPON_STATUS_OUT) {
            $this->throwBusinessException(CodeResponse::COUPON_EXCEED_LIMIT);
        }
        if ($coupon->status == Constant::COUPON_STATUS_EXPIRED) {
            $this->throwBusinessException(CodeResponse::COUPON_RECEIVE_FAIL, '优惠券已经过期');
        }

//        $couponUser = new CouponUser();
        $couponUser = CouponUser::new();
        if ($coupon->time_type == Constant::COUPON_TIME_TYPE_TIME) {
            $startTime = $coupon->start_time;
            $endTime = $coupon->end_time;
        } else {
            $startTime = Carbon::now();
            $endTime = $startTime->copy()->addDays($coupon->days);
        }
        $couponUser->fill([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
        return $couponUser->save();
    }

    public function getCoupon($id, $columns = ['*'])
    {
        return Coupon::query()
            ->find($id, $columns);
    }

    public function countCoupon($couponId)
    {
        return CouponUser::query()
            ->where('coupon_id', $couponId)
            ->count('id');
    }

    public function countCouponByUserId($userId, $couponId)
    {
        return CouponUser::query()
            ->where('coupon_id', $couponId)
            ->where('user_id', $userId)
            ->count('id');
    }

    public function getCouponUserByCouponId($userId, $couponId){
        return CouponUser::query()->where('coupon_id', $couponId)->where('user_id', $userId)
            ->orderBy('id')->first();
    }
    public function getMostMeetPriceCoupon($userId, $couponId, $price, &$availableCouponLength = 0)
    {
        $couponUsers = $this->getMeetPriceCouponAndSort($userId, $price);
        $availableCouponLength = $couponUsers->count();
        if (is_null($couponId) || $couponId == -1) {
            return null;
        }

        if (!empty($couponId)) {
            $coupon = $this->getCoupon($couponId);
            $couponUser = $this->getCouponUserByCouponId($userId, $couponId);
            $is = $this->checkCouponAndPrice($coupon, $couponUser, $price);
            if ($is) {
                return $couponUser;
            }
        }
        return $couponUsers->first();
    }

    public function getMeetPriceCouponAndSort($userId, $price)
    {
        $couponUsers = CouponService::getInstance()->getUsableCoupons($userId);
        $couponIds = $couponUsers->pluck('coupon_id')->toArray();
        $coupons = CouponService::getInstance()->getCoupons($couponIds)->keyBY('id');
        return $couponUsers->filter(function (CouponUser $couponUser) use ($coupons, $price) {
            /** @var Coupon $coupon */
            $coupon = $coupons->get($couponUser->coupon_id);
            return CouponService::getInstance()->checkCouponAndPrice($coupon, $couponUser, $price);


        })->sortByDesc(function (CouponUser $couponUser) use ($coupons) {
            /** @var Coupon $coupon */
            $coupon = $coupons->get($couponUser->coupon_id);
            return $coupon->discount;
        });
    }

    public function getUsableCoupons($userId)
    {
        return CouponUser::query()->where('user_id', $userId)
            ->where('status', CouponUserEnums::STATUS_USABLE)
            ->get();
    }

    public function getCoupons($ids, $columns = ['*'])
    {
        return Coupon::query()->whereIn('id', $ids)
            ->get($columns);
    }

    /**
     * @param  Coupon  $coupon
     * @param  CouponUser  $couponUser
     * @param  double  $price
     */
    public function checkCouponAndPrice($coupon, $couponUser, $price)
    {
        if (empty($couponUser)) {
            return false;
        }
        if (empty($coupon)) {
            return false;
        }
        if ($coupon->id != $couponUser->coupon_id) {
            return false;
        }
        if ($coupon->status != CouponEnums::STATUS_NORMAL) {
            return false;
        }
        if ($coupon->type != CouponEnums::GOODS_TYPE_ALL) {
            return false;
        }
        if (bccomp($coupon->min, $price) == 1) {
            return false;
        }
        $now = now();
        switch ($coupon->time_type) {
            case CouponEnums::TIME_TYPE_TIME;
                $start = Carbon::parse($coupon->start_time);
                $end = Carbon::parse($coupon->end_time);
                if ($now->isBefore($start) || $now->isAfter($end)) {
                    return false;
                }
                break;
            case CouponEnums::TIME_TYPE_DAYS;
                $expired = Carbon::parse($couponUser->add_time)->addDays($coupon->days);
                if ($now->isAfter($expired)) {
                    return false;
                }
                break;
            default:
                return false;
        }
        return true;
    }

    public function getCouponUser($id, $columns = ['*'])
    {
        return CouponUser::query()->find($id, $columns);
    }
}
