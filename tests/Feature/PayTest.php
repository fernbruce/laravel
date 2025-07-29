<?php
namespace Tests\Feature;

use App\Services\BaseServices;
use App\Services\Order\ExpressServices;
use App\Services\Order\OrderService;
use App\Services\SystemServices;
use Faker\Provider\Base;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class PayTest extends TestCase
{
//    use DatabaseTransactions;
//      public function testWxPay(){
//          $order1 = $this->getSimpleOrder([1.01,1]);
//          $this->post('wx/order/h5pay',['orderId'=>$order1->id]);

//          $order2 = $this->getSimpleOrder([1.02,1]);
//          $this->post('wx/order/h5pay',['orderId'=>$order2->id]);
//          $order3 = $this->getSimpleOrder([1.3,1]);
//          $this->post('wx/order/h5pay',['orderId'=>$order3->id]);
//          $order4 = $this->getSimpleOrder([1.31,1]);
//          $this->post('wx/order/h5pay',['orderId'=>$order4->id]);
//          $order5 = $this->getSimpleOrder([1.32,1]);
//          $this->post('wx/order/h5pay',['orderId'=>$order5->id]);
//          $order1 = $this->getSimpleOrder([1.01,1]);
//          $this->post('wx/order/h5pay',['orderId'=>$order1->id]);
//          $order1 = $this->getSimpleOrder([1.01,1]);
//          $this->post('wx/order/h5pay',['orderId'=>$order1->id]);
//          $order1 = $this->getSimpleOrder([1.01,1]);
//          $this->post('wx/order/h5pay',['orderId'=>$order1->id]);
//      }

//      public function testMock(){
// //         SystemServices::getMockInstance()->shouldReceive('getFreightValue')->andReturn(0);
//          OrderService::getMockInstance()->shouldReceive('getFreight')
//              ->with(2)
//              ->andReturn(77);
//          $v = OrderService::getInstance()->getFreight(3);
//          dd($v);
//          $order = $this->getSimpleOrder([[1.01,1]]);
//          dd($order->toArray());
// //         $v = SystemServices::getInstance()->getFreightValue();
// //         $mock = \Mockery::mock(SystemServices::class);
//          SystemServices::getMockInstance()->shouldReceive('getFreightValue')->andReturn(1)
//          ->shouldReceive('getFreightMin')->andReturn(999);
//          $v = SystemServices::getInstance()->getFreightValue();
//          $v1 = SystemServices::getInstance()->getFreightMin();
//          $this->assertEquals(1,$v);
//      }

//      public function testSingle(){
//          $this->getSimpleOrder([[12.3,2]]);
//          dd(BaseServices::getInstances());
//      }




     public function testAlipay(){
         $order = $this->getSimpleOrder();
         $token = Auth::login($this->user);
         echo url('wx/order/h5alipay?').Arr::query(['orderId'=>$order->id,'token'=>$token]);
     }



}
