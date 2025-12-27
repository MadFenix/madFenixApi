<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group ActionResult management
 */
class ApiActionResult extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\ActionResult';
    }
}
