<?php

namespace Tests\Feature;

use App\Models\User\Address;
use GuzzleHttp\Client;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GoodsTest extends TestCase
{
    use DatabaseTransactions;

    public function testCount()
    {
        $this->assertLitemallApiGet('wx/goods/count');
    }

    public function testCategory()
    {
        $this->assertLitemallApiGet('wx/goods/category?id=1008009');
        $this->assertLitemallApiGet('wx/goods/category?id=1005000');
    }

    public function testList()
    {
        $this->assertLitemallApiGet('wx/goods/list?keyword=四件套');
        $this->assertLitemallApiGet('wx/goods/list');
        $this->assertLitemallApiGet('wx/goods/list?categoryId=1008009');
        $this->assertLitemallApiGet('wx/goods/list?brandIdId=1001000');
        $this->assertLitemallApiGet('wx/goods/list?isNew=1');
        $this->assertLitemallApiGet('wx/goods/list?isHot=1');
        $this->assertLitemallApiGet('wx/goods/list?page=2&limit=5');
        $this->assertLitemallApiGet('wx/goods/list?categoryId=abc');//类型值必须是整型
        $this->assertLitemallApiGet('wx/goods/list?isNew=0');
        $this->assertLitemallApiGet('wx/goods/list?isNew=a');
        $this->assertLitemallApiGet('wx/goods/list?page=a&limit=5');
        $this->assertLitemallApiGet('wx/goods/list?page=1&limit=a');
        $this->assertLitemallApiGet('wx/goods/list?sort=id&order=asc', ['errmsg']);

//        $this->assertLitemallApiGet('wx/goods/list?sort=name&order=abc', ['errmsg']);
//        $this->assertLitemallApiGet('wx/goods/list?sort=name&order=abc');
    }

    public function testDetail()
    {
         $this->assertLitemallApiGet('wx/goods/detail?id=1181000');
//        $this->assertLitemallApiGet('wx/goods/detail?id=1009009');
//        $this->assertLitemallApiGet('wx/goods/detail?id=1036013');
    }
}
