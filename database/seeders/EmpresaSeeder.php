<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        Empresa::create([
            'nome' => 'INOVCORP Lda',
            'morada' => 'Rua da Inovação, 123',
            'codigo_postal' => '1000-001',
            'localidade' => 'Lisboa',
            'nif' => '500123456',
            'telefone' => '210000001',
            'email' => 'geral@inovcorp.pt',
            'website' => 'https://www.inovcorp.pt',
        ]);
    }
}