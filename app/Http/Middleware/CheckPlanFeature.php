<?php

namespace App\Http\Middleware;

use App\Services\PlanLimitService;
use Closure;
use Illuminate\Http\Request;

class CheckPlanFeature
{
    public function handle(Request $request, Closure $next, string $feature): mixed
    {
        $tenant = auth()->user()?->currentTenant();

        if (!$tenant) {
            return $next($request);
        }

        $service = new PlanLimitService($tenant);

        $temAcesso = match($feature) {
            'arquivo'    => $service->temArquivoDigital(),
            'calendario' => $service->temCalendario(),
            'financeiro' => $service->temFinanceiro(),
            default      => true,
        };

        if (!$temAcesso) {
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return redirect()->route('billing.planos')
                    ->with('erro', $service->erroLimite($feature));
            }
            return redirect()->route('billing.planos')
                ->with('erro', $service->erroLimite($feature));
        }

        return $next($request);
    }
}