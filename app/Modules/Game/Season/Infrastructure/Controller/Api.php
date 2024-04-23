<?php


namespace App\Modules\Game\Season\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\Season\Domain\Season;
use App\Modules\User\Domain\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Season';
    }
}
