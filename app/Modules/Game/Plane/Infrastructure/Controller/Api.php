<?php


namespace App\Modules\Game\Plane\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Plane';
    }
}
