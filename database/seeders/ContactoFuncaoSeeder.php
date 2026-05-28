<?php

namespace Database\Seeders;

use App\Models\ContactoFuncao;
use Illuminate\Database\Seeder;

class ContactoFuncaoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Diretor Comercial',
            'Gestor de Compras',
            'Diretor Financeiro',
            'Administrador',
            'Técnico de Suporte',
        ] as $funcao) {
            ContactoFuncao::create([
                'nome' => $funcao,
            ]);
        }
    }
}