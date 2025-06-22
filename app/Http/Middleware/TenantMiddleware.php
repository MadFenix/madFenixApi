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
            $host = explode(':', $request->getHost())[0];
            $path = parse_url($request->url(), PHP_URL_PATH);
            if (empty($path)) {
                $path = $request->header('X-Current-Path');
            }
            throw new \Exception('Host not found: ' . $host . ' - Path: ' . $path);
        }

        return $next($request);
    }
}
