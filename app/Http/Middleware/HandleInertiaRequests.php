<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Models\Artigo;
use App\Models\Entidade;


class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        $tenant = $user?->currentTenant();

        $tenants = $user?->tenants()
            ->wherePivot('ativo', true)
            ->get(['tenants.id', 'tenants.nome', 'tenants.slug']);


        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $user,
                'permissions' => $user?->getAllPermissions()->pluck('name')->toArray() ?? [],
                'roles' => $user?->getRoleNames()->toArray() ?? [],
                'temSubscricao' => $tenant
                    && in_array($tenant->subscription_status, [
                        'active',
                        'trial',
                        'trialing',
                    ]),
            ],
            'tenants' => $tenants ?? [],
            'tenant' => fn () => $user?->currentTenant(),
            'flash' => [
                'sucesso' => fn () => session('sucesso'),
                'erro' => fn () => session('erro'),
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'limites' => function () use ($user) {
                $tenant = $user?->currentTenant();
                if (!$tenant || !$tenant->plan) { return []; }

                $plan = $tenant->plan;
                $avisos = [];

                $pctUtilizadores = $tenant->users()->count() / max(1, $plan->max_utilizadores) * 100;
                $pctClientes     = Entidade::where('is_cliente', true)->count() / max(1, $plan->max_clientes) * 100;
                $pctArtigos      = Artigo::count() / max(1, $plan->max_artigos) * 100;

                if ($pctUtilizadores >= 80) { $avisos[] = "Utilizadores: {$tenant->users()->count()}/{$plan->max_utilizadores}"; }
                if ($pctClientes >= 80)     { $avisos[] = "Clientes: " . Entidade::where('is_cliente', true)->count() . "/{$plan->max_clientes}"; }
                if ($pctArtigos >= 80)      { $avisos[] = "Artigos: " . Artigo::count() . "/{$plan->max_artigos}"; }

                return $avisos;
            },
            'notificacoes' => function () use ($user) {
                if (!$user) { return []; }

                return $user->unreadNotifications()
                    ->latest()
                    ->take(5)
                    ->get()
                    ->map(fn($n) => [
                        'id'        => $n->id,
                        'tipo'      => $n->data['tipo'] ?? 'geral',
                        'mensagem'  => match($n->data['tipo'] ?? '') {
                            'trial_ending' => "Trial de {$n->data['tenant_nome']} termina em {$n->data['dias_restantes']} dias",
                            default        => $n->data['mensagem'] ?? '',
                        },
                        'created_at' => $n->created_at,
                    ]);
            },
        ]);
    }
}
