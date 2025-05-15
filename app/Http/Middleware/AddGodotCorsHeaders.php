<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

// If Laravel >= 5.2 then delete 'use' and 'implements' of deprecated Middleware interface.
class AddGodotCorsHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $response = $next($request);

        if (!($next instanceof StreamedResponse)) {
            $response->header('Cross-Origin-Opener-Policy', 'same-origin');
            $response->header('Cross-Origin-Embedder-Policy', 'require-corp');
        }

        return $response;
    }
}
