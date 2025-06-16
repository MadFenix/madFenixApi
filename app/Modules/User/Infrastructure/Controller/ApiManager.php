<?php


namespace App\Modules\Store\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

class ApiManager extends ResourceController
{

    protected function getModelName(): string
    {
        return 'User\\User';
    }

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\User\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\User\\Transformers\\UserSummary';
    }
}
