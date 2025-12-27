<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group SubcategoryDefinition management
 */
class ApiSubcategoryDefinition extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\SubcategoryDefinition';
    }
}
