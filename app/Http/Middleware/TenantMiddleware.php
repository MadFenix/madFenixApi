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
            $accountFromHost = self::getAccountFromHost($request);
            throw new \Exception('Host not found: ' . $accountFromHost->host . ' - Path: ' . $accountFromHost->path . ' - Account: ' . $accountFromHost->account);
        }

        return $next($request);
    }
}
