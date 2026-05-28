<?php

namespace Database\Seeders;

use App\Models\Entidade;
use App\Models\Proposta;
use Illuminate\Database\Seeder;

class PropostaSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1;

        $cliente = Entidade::where('tenant_id', $tenantId)
            ->where('is_cliente', true)
            ->first();

        if (!$cliente) {
            return;
        }

        Proposta::updateOrCreate(
            ['numero' => 1],
            [
                'tenant_id'      => $tenantId,
                'entidade_id'    => $cliente->id,
                'data_proposta'  => now(),
                'validade'       => now()->addDays(15),
                'estado'         => 'rascunho',
                'observacoes'    => 'Proposta comercial inicial',
            ]
        );
    }
}