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
        return 'Universe\\Character\\UndeadSuffix';
    }
}
