<?php

namespace App\Http\Middleware;

use App\Modules\Base\Infrastructure\Service\AccountManager;
use Closure;

class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        AccountManager::connectToAccount($request);

        return $next($request);
    }
}
