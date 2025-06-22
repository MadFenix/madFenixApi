<?php

namespace App\Http\Middleware;

use App\Modules\Base\Infrastructure\Service\AccountManager;
use Closure;

class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        $connectedToNewAccount = AccountManager::connectToAccount($request);

        if (!$connectedToNewAccount && $request->route('account') == 'host') {
            throw new \Exception('Host not found.');
        }

        return $next($request);
    }
}
