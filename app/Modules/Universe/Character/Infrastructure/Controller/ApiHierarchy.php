<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group Hierarchy management
 */
class ApiHierarchy extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\Hierarchy';
    }
}
