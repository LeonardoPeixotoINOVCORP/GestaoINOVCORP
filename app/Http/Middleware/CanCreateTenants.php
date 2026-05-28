<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanCreateTenants
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->user()?->can_create_tenants) {
            abort(403, 'Não tens permissão para criar tenants.');
        }

        return $next($request);
    }
}