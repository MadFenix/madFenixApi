<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group UndeadSuffix management
 */
class ApiUndeadSuffix extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\UndeadSuffix';
    }

    protected function getNameParameter(): string
    {
        return 'name';
    }

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Universe\\Character\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Universe\\Character\\Transformers\\' . $lastModelName;
    }
}
