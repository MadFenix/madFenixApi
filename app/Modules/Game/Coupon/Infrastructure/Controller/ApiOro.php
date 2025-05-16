<?php


namespace App\Modules\Game\Coupon\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

class ApiOro extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\CouponGold';
    }

    protected function getNameParameter(): string
    {
        return 'coupon';
    }
}
