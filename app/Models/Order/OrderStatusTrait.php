<?php

namespace App\Models\Order;

use App\Enums\OrderEnums;

trait OrderStatusTrait
{
    /**
     * @return bool
     */
    public function canCancelHandle(): bool
    {
        return $this->order_status === OrderEnums::STATUS_CREATE;
    }

    public function canPayHandle(): bool
    {
        return $this->order_status === OrderEnums::STATUS_CREATE;
    }

    public function canShipHandle(): bool
    {
        return $this->order_status === OrderEnums::STATUS_PAY;
    }

    public function canRefundHandle(): bool
    {
        return $this->order_status === OrderEnums::STATUS_PAY;
    }

    public function canAgreeRefundHandle(): bool
    {
        return $this->order_status === OrderEnums::STATUS_REFUND;
    }

    public function canConfirmHandle(): bool
    {
        return $this->order_status ===  OrderEnums::STATUS_SHIP;
    }

    public function canCommentHandle(): bool
    {
        return in_array($this->order_status, [OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM], true);
    }

    public function canRebuyHandle(): bool
    {
        return in_array($this->order_status, [OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM], true);
    }


    public function canAfterSaleHandle(): bool
    {
        return in_array($this->order_status, [OrderEnums::STATUS_CONFIRM, OrderEnums::STATUS_AUTO_CONFIRM], true);
    }
    /**
     * @return bool
     */
    public function canDeleteHandle(): bool
    {
        return in_array($this->order_status, [
            OrderEnums::STATUS_CANCEL,
            OrderEnums::STATUS_AUTO_CANCEL,
            OrderEnums::STATUS_ADMIN_CANCEL,
            OrderEnums::STATUS_REFUND_CONFIRM,
            OrderEnums::STATUS_CONFIRM,
            OrderEnums::STATUS_AUTO_CONFIRM
        ], true);
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

    public function isShipStatus(): bool
    {
        return $this->order_status === OrderEnums::STATUS_SHIP;
    }

    public function isPayStatus(): bool
    {
        return $this->order_status === OrderEnums::STATUS_PAY;
    }
}
