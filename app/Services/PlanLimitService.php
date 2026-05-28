<?php

namespace App\Services;

use App\Models\Artigo;
use App\Models\Entidade;
use App\Models\Tenant;

class PlanLimitService
{
    public function __construct(private Tenant $tenant) {}

    public function podeAdicionarUtilizador(): bool
    {
        $plan = $this->tenant->plan;
        if (!$plan) { return false; }
        return $this->tenant->users()->count() < $plan->max_utilizadores;
    }

    public function podeAdicionarCliente(): bool
    {
        $plan = $this->tenant->plan;
        if (!$plan) { return false; }
        return Entidade::where('is_cliente', true)->count() < $plan->max_clientes;
    }

    public function podeAdicionarArtigo(): bool
    {
        $plan = $this->tenant->plan;
        if (!$plan) { return false; }
        return Artigo::count() < $plan->max_artigos;
    }

    public function temArquivoDigital(): bool
    {
        return $this->tenant->plan?->arquivo_digital ?? false;
    }

    public function temCalendario(): bool
    {
        return $this->tenant->plan?->calendario ?? false;
    }

    public function temFinanceiro(): bool
    {
        return $this->tenant->plan?->financeiro ?? false;
    }

    public function erroLimite(string $recurso): string
    {
        $plan = $this->tenant->plan;
        return match($recurso) {
            'utilizador' => "O teu plano {$plan?->nome} permite no máximo {$plan?->max_utilizadores} utilizadores. Faz upgrade para adicionar mais.",
            'cliente'    => "O teu plano {$plan?->nome} permite no máximo {$plan?->max_clientes} clientes. Faz upgrade para adicionar mais.",
            'artigo'     => "O teu plano {$plan?->nome} permite no máximo {$plan?->max_artigos} artigos. Faz upgrade para adicionar mais.",
            'arquivo'    => "O teu plano {$plan?->nome} não inclui Arquivo Digital. Faz upgrade para aceder.",
            'calendario' => "O teu plano {$plan?->nome} não inclui Calendário. Faz upgrade para aceder.",
            'financeiro' => "O teu plano {$plan?->nome} não inclui o módulo Financeiro. Faz upgrade para aceder.",
            default      => "Limite do plano atingido. Faz upgrade para continuar.",
        };
    }
}