<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group CategoryField management
 */
class ApiCategoryField extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\CategoryField';
    }
}
