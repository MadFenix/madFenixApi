<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group ClonePrefix management
 */
class ApiClonePrefix extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\ClonePrefix';
    }
}
