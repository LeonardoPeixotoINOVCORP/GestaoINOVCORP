<?php

namespace Database\Seeders;

use App\Models\Entidade;
use App\Models\Encomenda;
use App\Models\Proposta;
use Illuminate\Database\Seeder;

class EncomendaSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1;

        $cliente = Entidade::where('tenant_id', $tenantId)
            ->where('is_cliente', true)
            ->first();

        $proposta = Proposta::first();

        if (!$cliente) {
            return;
        }

        Encomenda::updateOrCreate(
            ['numero' => 1],
            [
                'tenant_id'       => $tenantId,
                'entidade_id'     => $cliente->id,
                'proposta_id'     => $proposta?->id,
                'data_encomenda'  => now(),
                'tipo'            => 'cliente',
                'estado'          => 'rascunho',
                'observacoes'     => 'Encomenda criada automaticamente',
            ]
        );
    }
}