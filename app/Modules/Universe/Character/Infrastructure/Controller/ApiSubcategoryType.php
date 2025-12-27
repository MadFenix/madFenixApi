<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group SubcategoryType management
 */
class ApiSubcategoryType extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\SubcategoryType';
    }
}
