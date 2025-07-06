<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Models\Comment;
use App\Models\Goods\Goods;
use App\Models\SearchHistory;
use App\Services\CollectServices;
use App\Services\CommentServices;
use App\Services\Goods\BrandServices;
use App\Services\Goods\CatalogServices;
use App\Services\Goods\GoodsServices;
use App\Services\SearchHistoryServices;
use Illuminate\Http\Request;

class GoodsController extends WxController
{
    protected $only = [];


    public function count()
    {
        $count = GoodsServices::getInstance()->countGoodsOnSale();
        return $this->success($count);
    }


    public function category(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }
        $cur = CatalogServices::getInstance()->getCategory($id);

        if (empty($cur)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }

        $parent = null;
        $children = null;
        if ($cur->pid == 0) {
            $parent = $cur;
            $children = CatalogServices::getInstance()->getL2ListByPid($cur->id);
            $cur = $children->first() ?? $cur;
        } else {
            $parent = CatalogServices::getInstance()->getL1ById($cur->pid);
            $children = CatalogServices::getInstance()->getL2ListByPid($cur->pid);
        }
        return $this->success([
            'currentCategory' => $cur,
            'parentCategory' => $parent,
            'brotherCategory' => $children

        ]);
    }


    public function list(Request $request)
    {
        $categoryId = $request->input('categoryId');
        $brandId = $request->input('brandId');
        $keyword = $request->input('keyword');
        $isNew = $request->input('isNew');
        $isHot = $request->input('isHot');
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'add_time');
        $order = $request->input('order', 'desc');

        if ($this->isLogin() && !empty($keyword)) {
            SearchHistoryServices::getInstance()->save($this->userId(), $keyword, Constant::SEARCH_HISTORY_FROM_WX);
        }
        $columns = ['id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price'];
        $goodsList = GoodsServices::getInstance()->listGoods(
            $categoryId,
            $brandId,
            $isNew,
            $isHot,
            $keyword,
            $columns,
            $sort,
            $order,
            $page,
            $limit
        );

        $categoryList = GoodsServices::getInstance()->listL2Category($brandId, $isNew, $isHot, $keyword);

        $goodsList = $this->paginate($goodsList);
        $goodsList['filterCategoryList'] = $categoryList;
        return $this->success($goodsList);
    }

    public function detail(Request $request)
    {
        $id = $request->input('id', 0);
        if (empty($id)) {
            return $this->fail(CodeResponse::PARAM_ILLEGAL);
        }
        $info = GoodsServices::getInstance()->getGoods($id);
        if (empty($info)) {
            return $this->fail(CodeResponse::PARAM_VALUE_ILLEGAL);
        }

        $attr = GoodsServices::getInstance()->getGoodsAttribute($id);
        $spec = GoodsServices::getInstance()->getGoodsSpecification($id);
        $product = GoodsServices::getInstance()->getGoodsProduct($id);
        $issue = GoodsServices::getInstance()->getGoodsIssue();
        $brand = $info->brand_id ? BrandServices::getInstance()->getBrand($info->brand_id) : (object)[];
        $comment = CommentServices::getInstance()->getCommentWithUserInfo($id);
        $userHasCollect = 0;
        if ($this->isLogin()) {
            $userHasCollect = CollectServices::getInstance()->countByGoodsId($this->userId(), $id);
            GoodsServices::getInstance()->saveFootprint($this->userId(), $id);
        }
        //todo 团购信息
        //todo 系统配置
        return $this->success([
            'info' => $info,
            'userHasCollect' => $userHasCollect,
            'issue' => $issue,
            'comment' => $comment,
            'specificationList' => $spec,
            'productList' => $product,
            'attribute' => $attr,
            'brand' => $brand,
            'groupon' => [],
            'share' => false,
            'shareImage' => $info->share_url
        ]);
    }
}
