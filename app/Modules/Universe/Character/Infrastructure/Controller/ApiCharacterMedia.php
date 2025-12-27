<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

/**
 * @group CharacterMedia management
 */
class ApiCharacterMedia extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character\\CharacterMedia';
    }

    protected function getParentIdentificator()
    {
        return 'character_id';
    }
}
