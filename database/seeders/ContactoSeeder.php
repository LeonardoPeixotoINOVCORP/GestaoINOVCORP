<?php

namespace Database\Seeders;

use App\Models\Contacto;
use App\Models\ContactoFuncao;
use App\Models\Entidade;
use Illuminate\Database\Seeder;

class ContactoSeeder extends Seeder
{
    public function run(): void
    {
        $cliente = Entidade::withoutGlobalScopes()
            ->where('is_cliente', true)
            ->first();

        $funcao = ContactoFuncao::withoutGlobalScopes()
            ->first();

        if (!$cliente || !$funcao) {
            return;
        }

        Contacto::create([
            'tenant_id' => $cliente->tenant_id,
            'entidade_id' => $cliente->id,
            'nome' => 'João',
            'apelido' => 'Silva',
            'funcao_id' => $funcao->id,
            'email' => 'joao@abc.pt',
            'telefone' => '212345678',
            'telemovel' => '912345678',
            'ativo' => true,
        ]);
    }
}