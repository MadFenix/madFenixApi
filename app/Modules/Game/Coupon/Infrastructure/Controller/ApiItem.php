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

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Coupon\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Coupon\\Transformers\\' . $lastModelName;
    }
}
