<?php


namespace App\Modules\Blockchain\Block\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

class ApiNft extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Blockchain\\Nft';
    }

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Blockchain\\Block\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Blockchain\\Block\\Transformers\\' . $lastModelName;
    }
}
