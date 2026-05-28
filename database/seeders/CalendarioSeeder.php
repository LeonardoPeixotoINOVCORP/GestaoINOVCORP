<?php

namespace Database\Seeders;

use App\Models\CalendarioAcao;
use App\Models\CalendarioTipo;
use Illuminate\Database\Seeder;

class CalendarioSeeder extends Seeder
{
    public function run(): void
    {
        CalendarioTipo::insert([
            [
                'tenant_id' => session('tenant_id'),
                'nome' => 'Reunião',
                'cor' => '#3b82f6',
            ],
            [
                'tenant_id' => session('tenant_id'),
                'nome' => 'Visita',
                'cor' => '#22c55e',
            ],
            [
                'tenant_id' => session('tenant_id'),
                'nome' => 'Chamada',
                'cor' => '#f59e0b',
            ],
            [
                'tenant_id' => session('tenant_id'),
                'nome' => 'Entrega',
                'cor' => '#ef4444',
            ],
        ]);

        foreach ([
            'Acompanhamento',
            'Apresentação de Proposta',
            'Negociação',
            'Pós-venda',
        ] as $acao) {
            CalendarioAcao::create([
                'nome' => $acao,
            ]);
        }
    }
}