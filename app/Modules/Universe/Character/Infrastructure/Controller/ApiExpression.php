<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group Expression management
 */
class ApiExpression extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\Expression';
    }

    protected function getParentIdentificator()
    {
        return 'character_id';
    }
}
