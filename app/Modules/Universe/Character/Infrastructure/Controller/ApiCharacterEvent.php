<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group CharacterEvent management
 */
class ApiCharacterEvent extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\CharacterEvent';
    }
}
