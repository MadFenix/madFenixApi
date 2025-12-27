<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group Category management
 */
class ApiCategory extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\Category';
    }
}
