<?php

namespace App\Http\Controllers\Wx;

use App\Inputs\PageInput;
use App\Models\Goods\Goods;
use App\Models\Promotion\GrouponRules;
use App\Services\Goods\GoodsServices;
use App\Services\Promotion\GrouponService;

class GrouponController extends WxController
{
    protected $except = ['test'];

    public function list()
    {
        $page = PageInput::new();
        $list = GrouponService::getInstance()->getGrouponRules($page);
        $rules = collect($list->items());
        $goodsIds = $rules->pluck('good_id')->toArray();
        $goodsList = GoodsServices::getInstance()->getGoodsListById($goodsIds)->keyBy('id');

        $voList = $rules->map(function (GrouponRules $rule) use ($goodsList) {
            /** @var Goods $goods */
            $goods = $goodsList->get($rule->goods_id);
            return [
                'id' => $goods->id,
                'name' => $goods->name,
                'brief' => $goods->brief,
                'picUrls' => $goods->pic_url,
                'counterPrice' => $goods->counter_price,
                'retailPrice' => $goods->retail_price,
                'grouponPrice' => bcsub($goods->retail_price, $rule->discount, 2),
                'grouponDiscount' => $rule->discount,
                'grouponMember' => $rule->discount_member,
                'expireTime' => $rule->expire_time,
            ];
        });
        $list = $this->paginate($list, $voList);
        return $this->success($list);
    }


    public function test()
    {
        $rules = GrouponService::getInstance()->getGrouponRulesById(1);
        $url = GrouponService::getInstance()->createGrouponShareImage($rules);
        return $url;
        return response()->make($qrCode)->header("content-type", "image/png");
    }
}
