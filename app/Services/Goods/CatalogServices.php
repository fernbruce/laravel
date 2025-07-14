<?php

namespace App\Services\Goods;

use App\Models\Goods\Category;
use App\Services\BaseServices;

class CatalogServices extends BaseServices
{
    /**
     * 获取一级分类列表
     * @return Category[]|Collection
     *
     */
    public function getL1List()
    {
        return Category::query()->where('level', 'L1')->get();
    }

    /**
     * 获取二级分类列表
     * @param  int  $pid
     * @return Category[]|Collection
     *
     */
    public function getL2ListByPid(int $pid)
    {
        return Category::query()->where('level', 'L2')->where('pid', $pid)->get();
    }

    /**
     * 根据id获取一级类目
     *
     * @param  integer  $id
     * @return Category|null|Collection
     */
    public function getL1ById(int $id)
    {
        return Category::query()->where('level', 'L1')->where('id', $id)->first();
    }

    public function getCategory(int $id)
    {
        return Category::query()->where('id', $id)->first();
    }

    public function getL2ListByIds($ids)
    {
        if (empty($ids)) {
            return collect([]);
        }
        return Category::query()->whereIn('id', $ids)->get();
    }
}
