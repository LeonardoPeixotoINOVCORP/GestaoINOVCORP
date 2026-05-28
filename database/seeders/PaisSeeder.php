<?php

namespace Database\Seeders;

use App\Models\Pais;
use Illuminate\Database\Seeder;

class PaisSeeder extends Seeder
{
    public function run(): void
    {
        Pais::insert([
            [
                'tenant_id' => session('tenant_id'),
                'nome' => 'Portugal',
                'codigo' => 'PT',
            ],
            [
                'tenant_id' => session('tenant_id'),
                'nome' => 'Espanha',
                'codigo' => 'ES',
            ],
            [
                'tenant_id' => session('tenant_id'),
                'nome' => 'França',
                'codigo' => 'FR',
            ],
            [
                'tenant_id' => session('tenant_id'),
                'nome' => 'Alemanha',
                'codigo' => 'DE',
            ],
        ]);
    }
}