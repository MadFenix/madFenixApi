<?php
namespace App\Modules\Universe\Character\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use Illuminate\Http\Request;

/**
 * @group Character management
 *
 * APIs for managing characters
 */
class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Universe\\Character';
    }
}
