<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('scraping.admin_api_key');
        $given = (string) $request->header('X-Admin-Key');

        if (blank($expected) || ! hash_equals((string) $expected, $given)) {
            abort(401, 'Invalid admin key');
        }

        return $next($request);
    }
}
