<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group Rank management
 */
class ApiRank extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\Rank';
    }
}
