<?php

namespace App\Models\Order;

use App\Enums\OrderEnums;
use Exception;
use Illuminate\Support\Str;

/**
 * Trait OrderStatusTrait
 * @package App\Models\Order
 * @method bool canCancelHandle()
 * @method bool canDeleteHandle()
 * @method bool canPayHandle()
 * @method bool canCommentHandle()
 * @method bool canConfirmHandle()
 * @method bool canRefundHandle()
 * @method bool canRebuyHandle()
 * @method bool canAftersaleHandle()
 * @method bool canAgreeRefundHandle()
 * @method bool isCreateStatus()
 * @method bool isPayStatus()
 * @method bool isShipStatus()
 * @method bool isConfirmStatus()
 * @method bool isCancelStatus()
 * @method bool isAutoCancelStatus()
 * @method bool isRefundStatus()
 * @method bool isRefundConfirmStatus()
 * @method bool isAutoConfirmStatus()
 */
trait OrderStatusTrait
{

//    public function canCancelHandle(): bool
//    {
//        return $this->order_status === OrderEnums::STATUS_CREATE;
//    }
//
//    public function canPayHandle(): bool
//    {
//        return $this->order_status === OrderEnums::STATUS_CREATE;
//    }
//
//    public function canShipHandle(): bool
//    {
//        return $this->order_status === OrderEnums::STATUS_PAY;
//    }
//
//    public function canRefundHandle(): bool
//    {
//        return $this->order_status === OrderEnums::STATUS_PAY;
//    }
//
//    public function canAgreeRefundHandle(): bool
//    {
//        return $this->order_status === OrderEnums::STATUS_REFUND;
//    }
//
//    public function canConfirmHandle(): bool
//    {
//        return $this->order_status === OrderEnums::STATUS_SHIP;
//    }
//
//    public function canCommentHandle(): bool
//    {
//        return in_array($this->order_status, [OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM], true);
//    }
//
//    public function canRebuyHandle(): bool
//    {
//        return in_array($this->order_status, [OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM], true);
//    }
//
//
//    public function canAfterSaleHandle(): bool
//    {
//        return in_array($this->order_status, [OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM], true);
//    }
//
//
//    public function canDeleteHandle(): bool
//    {
//        return in_array($this->order_status, [
//            OrderEnums::STATUS_CANCEL,
//            OrderEnums::STATUS_AUTO_CANCEL,
//            OrderEnums::STATUS_ADMIN_CANCEL,
//            OrderEnums::STATUS_REFUND_CONFIRM,
//            OrderEnums::STATUS_CONFIRM,
//            OrderEnums::STATUS_AUTO_CONFIRM
//        ], true);
//    }
    public function __call($name, $arguments)
    {
        if (Str::is('can*Handle', $name)) {
            if (is_null($this->order_status)) {
                throw new Exception("order status is null when call method[$name]");
            }
            $key = Str::of($name)->replaceFirst('can', '')
                ->replaceLast('Handle', '')
                ->lower();
            return in_array($this->order_status, $this->canHandleMap[(string) $key]);
        } elseif (Str::is('is*Status', $name)) {
            if (is_null($this->order_status)) {
                throw new Exception("order status is null when call method[$name]");
            }
            $key = Str::of($name)->replaceFirst('is', '')
                ->replaceLast('Status', '')
                ->snake()->upper()->prepend('STATUS_');
            $status = (new \ReflectionClass(OrderEnums::class))->getConstant($key);
            return $this->order_status == $status;
        }
        return parent::__call($name, $arguments);
    }

    public function getCanHandleOptions()
    {
        return [
            'cancel' => $this->canCancelHandle(),
            'delete' => $this->canDeleteHandle(),
            'pay' => $this->canPayHandle(),
            'comment' => $this->canCommentHandle(),
            'confirm' => $this->canConfirmHandle(),
            'refund' => $this->canRefundHandle(),
            'rebuy' => $this->canRebuyHandle(),
            'aftersale' => $this->canAfterSaleHandle(),
        ];
    }

    private $canHandleMap = [
        'cancel' => [OrderEnums::STATUS_CREATE],
        'pay' => [OrderEnums::STATUS_CREATE],
        'rebuy' => [OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM],
        'delete' => [
            OrderEnums::STATUS_CANCEL,
            OrderEnums::STATUS_AUTO_CANCEL,
            OrderEnums::STATUS_ADMIN_CANCEL,
            OrderEnums::STATUS_REFUND_CONFIRM,
            OrderEnums::STATUS_CONFIRM,
            OrderEnums::STATUS_AUTO_CONFIRM
        ],
        'comment' => [OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM],
        'confirm' => [OrderEnums::STATUS_SHIP],
        'refund' => [OrderEnums::STATUS_PAY,OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM],
        'agreerefund' => [OrderEnums::STATUS_REFUND],
        'ship'=>[OrderEnums::STATUS_PAY],
        'aftersale' => [OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM],

    ];

//    public function isShipStatus(): bool
//    {
//        return $this->order_status === OrderEnums::STATUS_SHIP;
//    }
//
//    public function isPayStatus(): bool
//    {
//        return $this->order_status === OrderEnums::STATUS_PAY;
//    }
//
    public function isHadPaid(): bool
    {
        return !in_array($this->order_status, [
            OrderEnums::STATUS_CREATE,
            OrderEnums::STATUS_ADMIN_CANCEL,
            OrderEnums::STATUS_CANCEL,
            OrderEnums::STATUS_AUTO_CANCEL
        ], true);
    }
}
