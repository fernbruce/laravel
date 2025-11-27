<?php

namespace App\Http\Controllers\Wx;

use App\CodeResponse;
use App\Constant;
use App\Exceptions\BusinessException;
use App\Inputs\GoodsListInput;
use App\Models\Comment;
use App\Services\CollectServices;
use App\Services\CommentServices;
use App\Services\Goods\BrandServices;
use App\Services\Goods\CatalogServices;
use App\Services\Goods\GoodsServices;
use App\Services\SearchHistoryServices;
use App\Services\SystemServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GoodsController extends WxController
{
    protected $only = [];


    public function count()
    {
        $count = GoodsServices::getInstance()->countGoodsOnSale();
        return $this->success($count);
    }


    public function category()
    {
        $id = $this->verifyId('id');
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


    /**
     *
     * @return JsonResponse
     * @throws BusinessException
     */
    public function list()
    {
        // version 1
        // $categoryId = $request->input('categoryId');
        // $brandId = $request->input('brandId');
        // $keyword = $request->input('keyword');
        // $isNew = $request->input('isNew');
        // $isHot = $request->input('isHot');
        // $page = $request->input('page', 1);
        // $limit = $request->input('limit', 10);
        // $sort = $request->input('sort', 'add_time');
        // $order = $request->input('order', 'desc');

        //参数验证器
//        Validator::make();
        //验证不通过会抛一个异常  ValidationException      在Handler.php 接一下
        //一方面接错误，一方面接正确的值
//        $input = $request->validate([
//            'categoryId' => 'integer|digits_between:1,20',
//            'brandId' => 'integer|digits_between:1,20',
//            'keyword' => 'string',
//            'isNew' => 'boolean',
//            'isHot' => 'boolean',
//            'page' => 'integer',
//            'limit' => 'integer',
//            'sort' => ['required',Rule::in(['add_time', 'retail_price', 'name'])],
//            'order' => Rule::in(['desc', 'asc']),
//        ]);
//        dd($input);
//         $input = new GoodsListInput();
        // $input->fill();
        // version 2
//         $categoryId = $this->verifyId('categoryId');
//         $brandId = $this->verifyId('brandId');
//         $keyword = $this->verifyString('keyword');
//         $isNew = $this->verifyBoolean('isNew');
//         $isHot = $this->verifyBoolean('isHot');
//         $page = $this->verifyInteger('page', 1);
//         $limit = $this->verifyInteger('limit', 10);
//         $sort = $this->verifyEnum('sort','add_time', ['add_time', 'retail_price', 'name']);
//         $order = $this->verifyEnum('order', 'desc', ['desc', 'asc']);

        // version 3
        $input = GoodsListInput::new();
        if ($this->isLogin() && !empty($input->keyword)) {
            SearchHistoryServices::getInstance()->save($this->userId(), $input->keyword,
                Constant::SEARCH_HISTORY_FROM_WX);
        }
        // todo 优化参数的传递
        $columns = ['id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price'];

        $goodsList = GoodsServices::getInstance()->listGoods(
            $input,
            $columns
        );

        $categoryList = GoodsServices::getInstance()->listL2Category($input);

        $goodsList = $this->paginate($goodsList);
        $goodsList['filterCategoryList'] = $categoryList;
        return $this->success($goodsList);
    }

    /**
     * 商品详情 为了后期的可维护性，做好封装做好组织,提高接口的质量
     * @param  Request  $request
     * @return JsonResponse
     */
    public function detail(Request $request)
    {
        $id = $this->verifyId('id');
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
//        $brand = $info->brand_id ? BrandServices::getInstance()->getBrand($info->brand_id) : (object) [];//new \stdClass()
        $brand = $info->brand_id ? BrandServices::getInstance()->getBrand($info->brand_id) : collect();
        $comment = CommentServices::getInstance()->getCommentWithUserInfo($id);
        $userHasCollect = 0;
        if ($this->isLogin()) {
            $userHasCollect = CollectServices::getInstance()->countByGoodsId($this->userId(), $id);
            GoodsServices::getInstance()->saveFootprint($this->userId(), $id);
        }
        // todo 团购信息
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
            'share' => SystemServices::getInstance()->getWxShare(),
            'shareImage' => $info->share_url
        ]);
    }
}
