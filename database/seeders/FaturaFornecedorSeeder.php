<?php

namespace Database\Seeders;

use App\Models\Entidade;
use App\Models\Encomenda;
use App\Models\FaturaFornecedor;
use Illuminate\Database\Seeder;

class FaturaFornecedorSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = 1;

        $fornecedor = Entidade::where('tenant_id', $tenantId)
            ->where('is_fornecedor', true)
            ->first();

        $encomenda = Encomenda::where('tenant_id', $tenantId)
            ->where('tipo', 'fornecedor')
            ->first();

        if (!$fornecedor) {
            return;
        }

        FaturaFornecedor::updateOrCreate(
            ['numero' => 1],
            [
                'tenant_id'        => $tenantId,
                'fornecedor_id'    => $fornecedor->id,
                'encomenda_id'     => $encomenda?->id,
                'data_fatura'      => now()->subDays(10),
                'data_vencimento'  => now()->addDays(20),
                'valor_total'      => 1250.50,
                'estado'           => 'pendente',
            ]
        );
    }
}