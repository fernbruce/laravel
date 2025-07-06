<?php

namespace App\Services\Goods;

use App\Models\Goods\Goods;
use App\Models\Goods\GoodsAttribute;
use App\Models\Goods\GoodsProduct;
use App\Models\Goods\GoodsSpecification;
use App\Models\Goods\Issue;
use App\Services\BaseServices;
use Illuminate\Database\Eloquent\Builder;

class GoodsServices extends BaseServices
{
    public function getGoods(int $id)
    {
        return Goods::query()->where('id', $id)->where('deleted', 0)->first();
    }
    public function countGoodsOnSale()
    {

        return Goods::query()->where('is_on_sale', 1)->where('deleted', 0)->count("id");
    }

    public function listGoods($categoryId, $brandId, $isNew, $isHot, $keyword, $columns = ['*'], $sort = 'add_time', $order = 'desc', $page = 1, $limit = 10)
    {
        $query = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);
        if (!empty($categoryId)) {
            $query = $query->where('category_id', $categoryId);
        }


        return $query->orderBy($sort, $order)->paginate($limit, $columns, 'page', $page);
    }

    public function listL2Category($brandId, $isNew, $isHot, $keyword)
    {
        $query = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);
        // $query->toSql();
        $categoryIds = $query->select(['category_id'])->pluck('category_id')->unique()->toArray();

        return CatalogServices::getInstance()->getL2ListByIds($categoryIds);
    }

    private function getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword)
    {
        $query = Goods::query()->where('is_on_sale', 1)->where('deleted', 0)->orderByDesc('add_time');
        if (!empty($brandId)) {
            $query = $query->where('brand_id', $brandId);
        }

        if (!empty($isNew)) {
            $query = $query->where('is_new', $isNew);
        }

        if (!empty($isHot)) {
            $query = $query->where('is_hot', $isHot);
        }
        if (!empty($keyword)) {
            $query = $query->where(function (Builder $query) use ($keyword) {
                $query->where('keywords', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%');
            });
        }
        return $query;
    }


    public function getGoodsAttribute(int $goodsId)
    {
        return GoodsAttribute::query()->where('goods_id', $goodsId)->where("deleted", 0)->get();
    }

    public function getGoodsSpecification(int $goodsId)
    {
        $spec = GoodsSpecification::query()->where('goods_id', $goodsId)->where("deleted", 0)->get()->groupBy('specification');
        return $spec->map(function ($v, $k) {
            return ['name' => $k, 'valueList' => $v->toArray()];
        })->values();
    }

    public function getGoodsProduct(int $goodsId)
    {
        return GoodsProduct::query()->where('goods_id', $goodsId)->where("deleted", 0)->get();
    }

    public function getGoodsIssue(int $page = 1, int $limit = 4)
    {
        return Issue::query()->where("deleted", 0)->forPage($page, $limit)->get();
    }

    public function saveFootprint(int $userId, int $goodsId)
    {
        $footprint = new Footprint();
        $footprint->fill(['user_id' => $userId, 'goods_id' => $goodsId]);
        return $footprint->save();
    }
}
