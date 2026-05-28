<?php

namespace Database\Seeders;

use App\Models\ContaBancaria;
use Illuminate\Database\Seeder;

class ContaBancariaSeeder extends Seeder
{
    public function run(): void
    {
        ContaBancaria::create([
            'banco' => 'CGD',
            'iban' => 'PT50003501230001234567890',
            'swift' => 'CGDIPTPL',
            'titular' => 'INOVCORP Lda',
            'ativo' => true,
        ]);
    }
}