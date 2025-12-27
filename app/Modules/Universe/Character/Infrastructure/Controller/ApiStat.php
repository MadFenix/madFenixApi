<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group Stat management
 */
class ApiStat extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\Stat';
    }
}
