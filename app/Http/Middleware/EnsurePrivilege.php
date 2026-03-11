<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrivilege
{
    public function handle(Request $request, Closure $next, string ...$privileges): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        foreach ($privileges as $privilege) {
            if ($user->hasPrivilege($privilege)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
