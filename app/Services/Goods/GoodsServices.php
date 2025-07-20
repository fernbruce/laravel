<?php

namespace App\Services\Goods;

use App\Inputs\GoodsListInput;
use App\Models\Goods\Footprint;
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
        return Goods::query()->where('id', $id)->first();
    }

    public function getGoodsListById($Ids)
    {
        return Goods::query()->whereIn('id', $Ids)->get();
    }

    public function countGoodsOnSale()
    {

        return Goods::query()->where('is_on_sale', 1)->count("id");
    }

    // public function listGoods($categoryId, $brandId, $isNew, $isHot, $keyword, $columns = ['*'], $sort = 'add_time', $order = 'desc', $page = 1, $limit = 10)
    public function listGoods(GoodsListInput $input, $columns = ['*'])
    {
        // $query = $this->getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword);
        $query = $this->getQueryByGoodsFilter($input);
        if (!empty($input->categoryId)) {
            $query = $query->where('category_id', $input->categoryId);
        }


        return $query->orderBy($input->sort, $input->order)->paginate($input->limit, $columns, 'page', $input->page);
    }

    // public function listL2Category($brandId, $isNew, $isHot, $keyword)

    private function getQueryByGoodsFilter(GoodsListInput $input)
    {
        $query = Goods::query()->where('is_on_sale', 1)->orderByDesc('add_time');
        if (!empty($input->brandId)) {
            $query = $query->where('brand_id', $input->brandId);
        }

        if (!is_null($input->isNew)) {
            $query = $query->where('is_new', $input->isNew);
        }

        if (!is_null($input->isHot)) {
            $query = $query->where('is_hot', $input->isHot);
        }
        if (!empty($input->keyword)) {
            $query = $query->where(function (Builder $query) use ($input) {
                $query->where('keywords', 'like', '%'.$input->keyword.'%')
                    ->orWhere('name', 'like', '%'.$input->keyword.'%');
            });
        }
        return $query;
    }

    // private function getQueryByGoodsFilter($brandId, $isNew, $isHot, $keyword)

    public function listL2Category($input)
    {
        $query = $this->getQueryByGoodsFilter($input);
        // $query->toSql();
        $categoryIds = $query->select(['category_id'])->pluck('category_id')->unique()->toArray();

        return CatalogServices::getInstance()->getL2ListByIds($categoryIds);
    }

    public function getGoodsAttribute(int $goodsId)
    {
        return GoodsAttribute::query()->where('goods_id', $goodsId)->get();
    }

    public function getGoodsSpecification(int $goodsId)
    {
        $spec = GoodsSpecification::query()->where('goods_id', $goodsId)
            ->get()->groupBy('specification');
        return $spec->map(function ($v, $k) {
            return ['name' => $k, 'valueList' => $v->toArray()];
        })->values();
    }

    public function getGoodsProduct(int $goodsId)
    {
        return GoodsProduct::query()->where('goods_id', $goodsId)->get();
    }

    public function getGoodsProductById(int $Id)
    {
        return GoodsProduct::query()->where('id', $Id)->first();

    }

    public function getGoodsProductByIds(array $Ids){
        if(empty($Ids)){
            return collect([]);
        }
       return GoodsProduct::query()->whereIn('id', $Ids)->get();
    }

    public function getGoodsIssue(int $page = 1, int $limit = 4)
    {
        return Issue::query()->forPage($page, $limit)->get();
    }

    public function saveFootprint(int $userId, int $goodsId)
    {
        $footprint = new Footprint();
        $footprint->fill(['user_id' => $userId, 'goods_id' => $goodsId]);
        return $footprint->save();
    }

    public function reduceStock($productId, $num){
        return GoodsProduct::query()->where('id',$productId)
            ->where('number', '>=', $num)
            ->decrement('number',$num);
    }
}
