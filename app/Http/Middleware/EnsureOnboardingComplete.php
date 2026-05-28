<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOnboardingComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $tenantId = session('tenant_id');

        if (!$user || !$tenantId) {
            return $next($request);
        }

        $tenant = $user->tenants()
            ->where('tenants.id', $tenantId)
            ->first();

        if (!$tenant) {
            return $next($request);
        }

        // Só o owner tem de passar pelo onboarding
        $isOwner = $tenant->owner_id === $user->id;

        if ($isOwner && !$tenant->onboarding_completo) {
            return redirect()->route('onboarding.wizard');
        }

        return $next($request);
    }
}