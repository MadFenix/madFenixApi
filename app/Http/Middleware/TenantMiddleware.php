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
            $host = explode(':', $request->header('Referer'))[0];
            $path = '';
            if ($request->header('Referer') == 'our.welore.io' || $host == 'localhost') {
                $path = $request->header('X-Current-Path');
                $segments = explode('/', trim($path, '/'));
                if (empty($segments[0])) {
                    throw new \Exception('Invalid account');
                }
                $account = $segments[0];
            } else {
                $account = explode('.', $host)[0];
                $account = explode('/', $account);
                $account = $account[count($account) - 1];
            }
            throw new \Exception('Host not found: ' . $host . ' - Path: ' . $path . ' - Account: ' . $account);
        }

        return $next($request);
    }
}
