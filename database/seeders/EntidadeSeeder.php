<?php

namespace Database\Seeders;

use App\Models\Entidade;
use App\Models\Pais;
use Illuminate\Database\Seeder;

class EntidadeSeeder extends Seeder
{
    public function run(): void
    {
        $pt = Pais::where('codigo', 'PT')->first();

        Entidade::create([
            'is_cliente' => true,
            'nome' => 'Empresa ABC',
            'nif' => '500100001',
            'email' => 'geral@abc.pt',
            'pais_id' => $pt->id,
            'ativo' => true,
        ]);

        Entidade::create([
            'is_fornecedor' => true,
            'nome' => 'TechSupply',
            'nif' => '500000001',
            'email' => 'geral@techsupply.pt',
            'pais_id' => $pt->id,
            'ativo' => true,
        ]);
    }
}