<?php


namespace App\Modules\Game\Coupon\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

class ApiItem extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\CouponItem';
    }

    protected function getNameParameter(): string
    {
        return 'coupon';
    }
}
