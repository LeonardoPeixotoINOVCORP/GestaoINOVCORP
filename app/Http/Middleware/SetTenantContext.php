<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\PermissionRegistrar;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        // Permite acesso às rotas de billing mesmo sem subscrição ativa
        if ($request->routeIs('billing.*')) {
            $tenantId = session('tenant_id');
            if ($tenantId) {
                $tenant = $user->tenants()
                    ->where('tenants.id', $tenantId)
                    ->wherePivot('ativo', true)
                    ->first();

                if ($tenant) {
                    
                    view()->share('tenantAtivo', $tenant);
                }
            }
            return $next($request);
        }

        $tenantId = session('tenant_id');

        if (!$tenantId) {
            $tenant = $user->tenants()
                ->wherePivot('ativo', true)
                ->first();

            if ($tenant) {
                session(['tenant_id' => $tenant->id]);
                $tenantId = $tenant->id;
            }
        }

        if ($tenantId) {
            $tenant = $user->tenants()
                ->where('tenants.id', $tenantId)
                ->wherePivot('ativo', true)
                ->first();

            if (!$tenant) {
                session()->forget('tenant_id');
                return redirect()
                    ->route('tenant.select')
                    ->with('erro', 'Não tens acesso a este tenant.');
            }

            if (!$tenant->isActive()) {
                return redirect()->route('billing.expirado');
            }

            app(PermissionRegistrar::class)
                        ->setPermissionsTeamId($tenant->id);
                        
            view()->share('tenantAtivo', $tenant);
        }

        return $next($request);
    }
}