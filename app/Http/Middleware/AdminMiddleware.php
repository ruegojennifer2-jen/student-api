<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = strtolower((string) optional($request->user())->role);

        if (! in_array($role, ['admin', 'administrator'])) {
            return response()->json(['message' => 'Forbidden. Administrator only.'], 403);
        }

        return $next($request);
    }
}