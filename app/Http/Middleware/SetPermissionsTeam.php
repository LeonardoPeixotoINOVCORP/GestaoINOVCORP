<?php

namespace App\Http\Middleware;

use Closure;
use Spatie\Permission\PermissionRegistrar;

class SetPermissionsTeam
{
    public function handle($request, Closure $next)
    {
        if (session()->has('tenant_id')) {

            app(PermissionRegistrar::class)
                ->setPermissionsTeamId(session('tenant_id'));

            app(PermissionRegistrar::class)
                ->forgetCachedPermissions();
        }

        return $next($request);
    }
}