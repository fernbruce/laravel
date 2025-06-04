<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    public function __construct()
    {
        // $this->middleware('benchmark:test1,test2');
        // $this->middleware('admin1:test1,test2');
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return 'hello world';
    }

    /**
     * Show the application welcome page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function welcome()
    {
        return view('welcome');
    }

    public function getOrder(Request $request, $id = 10, $default = 'default')
    {
        // $id = $request->route('id');
        // return $id;
        // return 'Order ID: '.$id;
        // return 'Order ID: '.$id.' and default: '.$default;
        return ['id' => $id, 'default' => $default];
    }

    public function getUser(Request $request)
    {
        // $res  = DB::select('select * from users');
        // $res = DB::insert('insert into users (name, email, password) values (?, ?, ?)', ['tanfan', 'aaa@email.com','123']);
        // $res = DB::update('update users set name = ? where id = ?', ['tanfan11', 1]);
        // $res = DB::delete('delete from users where id = ?', [2]);
        // $res = DB::table('users')->where('id', 1)->get();
        // $res = DB::table('users')->get();
        // $res = DB::table('users')->where('id', 1)->first();
        // $res = DB::table('users')->find(1);
        // $res = DB::table('users')->where('id',1)->value('name');
        // $res = DB::table('users')->pluck('name')->toArray();
        // $res = DB::table('users')->paginate(2);
        // $res = DB::table('users')->simplePaginate(2);
        // $res = DB::table('users')->max('id');
        // $res = DB::table('users')->min('id');
        // $res = DB::table('users')->average('id');
        // $res = DB::table('users')->count();
        // $res = DB::table('users')->sum('id');
        // $res = DB::table('users')->where('id', 4)->exists();
        // $res = DB::table('users')->where('id', 4)->doesntExist();
        // $res = DB::table('users')->where('id','>',1)->dump();
        // $res = DB::table('users')->where('id','<>',1)->get();;
        // $res = DB::table('users')->where('id','!=',1)->first();;
        // $res = DB::table('users')->where('name','like','tan%')->get();
        // $res = DB::table('users')->where('id', '>',1)->orWhere('name', 'like','tan%')->get();
        // $res = DB::table('users')->where('id', '>',1)->where(function(Builder $query){
        // $query->where('name', 'like', 'tan%')
        //   ->orWhere('email', 'like', '%@email.com');
        // })->get();
        // $res = DB::table('users')->whereIn('id', [1, 2, 3])->get();
        // $res = DB::table('users')->whereNotIn('id', [1, 2, 3])->get();
        // $res = DB::table('users')->whereBetween('id', [1, 3])->get();
        // $res = DB::table('users')->whereNull('created_at')->get();
        // $res = DB::table('users')->whereNotNull('created_at')->get();
        // $res = DB::table('users')->whereColumn('name', 'email')->get();

        // $res = DB::table('users')->insert([
        //     'name' => 'name1',
        //     'email' => 't@a.com',
        //     'password' => bcrypt('123456'),
        // ]);
        // $res = DB::table('users')->insertGetId([
        //     [
        //         'name' => 'name5',
        //         'email' => 'name5@223.com',
        //         'password' => Hash::make('123456'),
        //     ],
        //     [
        //         'name' => 'name6',
        //         'email' => 'name6@34.com',
        //         'password' => Hash::make('123436'),
        //     ]
        // ]);
        //  $res = DB::table('users')->insertOrIgnore([
        //     'name' => 'name4',
        //     'email' => 'name4@a.com',
        //     'password' => bcrypt('123456'),
        // ]);
        // $res = DB::table('users')->where('id', 17)->updateOrInsert([
        //     'name' => 'name7',
        //     'email' => 'name7@123.com',
        //     'password' => Hash::make('123456'),
        // ]);

        // $res = DB::table('users')->updateOrInsert(['id'=>18],[
        //     'name' => 'name8',
        //     'email' => 'name8@123.com',
        //     'password' => Hash::make('123456'),
        // ]);
        // $res = DB::table('users')->where('id', 7)->increment('score');
        // $res = DB::transaction(function () {
        //     //     DB::table('users')->where('id', 7)->increment('score');
        //     //     DB::table('users')->where('id', 8)->decrement('score');
        //     //     throw new \Exception('Transaction failed'); // Uncomment to test transaction rollback
        //     $res = DB::table('users')->insert([
        //         'name' => 'name9',
        //         'email' => 'name9@a.com',
        //         'password' => bcrypt('123456'),
        //     ]);
        //     $res = DB::table('users')->insert([
        //         'name' => 'name10',
        //         'email' => 'name10@a.com',
        //         'password' => bcrypt('123456'),
        //     ]);
        // });
        try {
            DB::beginTransaction();
            DB::table('users')->where('id', 7)->increment('score');
            DB::table('users')->where('id', 8)->decrement('score');
            // throw new \Exception('Transaction failed'); // Uncomment to test transaction rollback
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }
        // dd($res);
    }

    public function modelTest(){
        // $product = Product::query()->create([
        //     'title'=>'水杯',
        //     'category_id'=>1,
        //     'is_on_sale'=>1,
        //     'price'=>'1200',
        //     'pic_url'=>'https://example.com/image.jpg',
        //     'attr'=>[
        //         'color' => 'red',
        //         'size' => 'large'
        //     ],
        // ]);
        // $product = Product::query()->insert([
        //     'title' => '水杯',
        //     'category_id' => 1,
        //     'is_on_sale' => 1,
        //     'price' => '1200',
        //     'pic_url' => 'https://example.com/image.jpg',
        //     'attr' => json_encode([
        //         'color' => 'red',
        //         'size' => 'large'
        //     ]),
        // ]);
        $product = new Product();
        // $product->title = '水杯';
        // $product->category_id = 1;
        // $product->is_on_sale = 1;
        // $product->price = '1200';
        // $product->pic_url = 'https://example.com/image.jpg';
        // $product->attr = [
        //     'color' => 'red',
        //     'size' => 'large'
        // ];
        // $res = $product->save();
        // $res = $product->fill([
        //     'title' => '水杯',
        //     'category_id' => 1,
        //     'is_on_sale' => 1,
        //     'price' => '1200',
        //     'pic_url' => 'https://example.com/image.jpg',
        //     'attr' => [
        //         'color' => 'red',
        //         'size' => 'large'
        //     ]
        // ])->save();
        // var_dump($res);
        // dd($product);

        // $res = Product::all();
        // $res = Product::get();
        // $res = Product::query()->get();
        $product = Product::withTrashed()->find(1);
        // $res = Product::withTrashed()->find(1);
        // $res = Product::withoutTrashed()->find(1);
        $res = $product->restore();
        // $product->title = '新水杯';
        // $res = $product->delete();
        dd($res);


    }

    public function testCollection(){
        //  $collect = collect(['k1'=>1, 'k2'=>2, 3, 4, 5]);
        // $product = Product::all();
        // $res = $product->pluck('title')->implode(',');
        // var_dump($res);
        // $product->plunk('title')->dump();
        // $products = Product::query()->get()->pluck('price');
        // var_dump($products->count(),
        // $products->sum(),
        // $products->average(),
        // $products->max(),
        // $products->min());
        $collection = collect(['v1','v2','v3']);
        $exists = collect(['v1','v2','v3'])->contains('v4');
        $diff = collect(['v1','v2','v3'])->diff('v3','v4');
        $collection->isEmpty();
        $products = Product::all();
        // $pro = $products->where('id',3);
        // $products->each(function($item){
            //   var_dump($item);
            //   var_dump($item->id);
        // });
        // $keyBy = $products->keyBy('id')->toArray();
        // $keyBy = $products->groupBy('category_id');
        // $pro = $products->filter(function($item){
            // return $item->id >3;
        // });
        // $collection = collect([12,4,5,2,77]);
        // $collection->sort()->dump();
        $products->sortBy('price')->dump();
        $products->sortByDesc('price')->dump();
        // collect(['k1','k2'])->combine(['v1','v2'])->dd();
        collect(['k1','k2'])->crossJoin(['v1','v2'])->dd();

    }
}
