<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Goods\Goods;
use App\Models\Goods\GoodsProduct;
use App\Models\System;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
//        $this->middleware(Benchmark::class,['only'=>['index','show']]);
//        $this->middleware('benchmark:test1,test2', ['except' => ['index', 'show']]);
    }

    public function index()
    {
        echo 'hello world';
        return '你好';
    }
    use SoftDeletes;
    public function dbTest(){
//        $res = DB::select('select * from litemall_user where id = ?', [1]);
//        $res = DB::select('select * from litemall_user where id = :id', ['id'=>1]);
//          Goods::query()->create();//fillable
//          Goods::query()->insert([]);//没有经过模型  用的是查询构造器
//          $product = new Goods();
//          $product->name = '';
//          $product->pic_url='';
//          $product->fill([]);
//          $product->save();
//          Goods::all();
//          Goods::query()->get();
//        GoodsProduct::query()->where('id',1)->update([]);
//        $product = GoodsProduct::query()->where('id',1)->first();
//        $product->delete();
//        $product = GoodsProduct::withTrashed()->where('id',1)->first();

        $collect = collect([1,2,3]);
//        dd($collect->toArray());
//        dd($collect->all());
//          dd($collect->keys());
//            dd($collect->first());
        $system = System::all();
//        dd($system->all());
//        dd($system->toArray());
//        dd($system->take(2)->toJson());
        $res = $system->pluck('id');
        dump($res->count());
        dump($res->sum());
        dump($res->average());
        dump($res->max());
        dump($res->min());
        dump($res->contains(9999));
        $collect = collect(['k1'=>'v1','k2'=>'v2','k3'=>'v3']);
        dump($collect->flip()->reverse()->toArray());
//        dump($collect->has('k1'));
//        $collect = collect([]);
//        dump(empty($collect));
//        dump($collect->isEmpty());
//        $pro = $collect->where('k1','v1');
//        dd($pro);
        $products = GoodsProduct::all();
        $products->sortBy('price')->dump();
//        dump($products->pluck('price')->implode(','));
//        collect(['k1','k2'])->combine(['v1','v2'])->dd();
        collect([1,2,3])->diff([2,3])->dd();
        collect(['k1','k2'])->crossJoin(['v1','v2'])->crossJoin(['z1','z2'])->dd();
//        $products->each(function($item){
//            dump($item->id);
//        });
//        $prod = $products->map(function($item){
//            return $item->id;
//        });
//        dd($prod);
//        $products = $products->keyBy('id');
//        dd($products);
//        $productsG = $products->groupBy('category_id');
//        dd($productsG);


    }
}
