<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group Ability management
 */
class ApiAbility extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\Ability';
    }

    protected function getParentIdentificator()
    {
        return 'character_id';
    }
}
