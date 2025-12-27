<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group Role management
 */
class ApiRole extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\Role';
    }
}
