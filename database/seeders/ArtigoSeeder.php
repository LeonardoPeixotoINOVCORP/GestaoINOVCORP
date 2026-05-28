<?php

namespace Database\Seeders;

use App\Models\Artigo;
use Illuminate\Database\Seeder;

class ArtigoSeeder extends Seeder
{
    public function run(): void
    {
        Artigo::updateOrCreate(
            ['referencia' => 'MON-001'],
            [
                'tenant_id' => 1,
                'nome' => 'Monitor 27 4K',
                'preco' => 450,
                'descricao' => 'Artigo profissional',
                'iva' => 23,
                'ativo' => true,
            ]
        );

        Artigo::updateOrCreate(
            ['referencia' => 'TEC-001'],
            [
                'tenant_id' => 1,
                'nome' => 'Teclado Mecânico',
                'preco' => 120,
                'descricao' => 'Switch blue',
                'iva' => 23,
                'ativo' => true,
            ]
        );

        Artigo::updateOrCreate(
            ['referencia' => 'RAT-001'],
            [
                'tenant_id' => 1,
                'nome' => 'Rato Wireless',
                'preco' => 60,
                'descricao' => 'Bluetooth',
                'iva' => 23,
                'ativo' => true,
            ]
        );
    }
}