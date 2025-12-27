<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group Archetype management
 */
class ApiArchetype extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\Archetype';
    }
}
