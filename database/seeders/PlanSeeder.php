<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(
            ['slug' => 'free'],
            [
                'nome' => 'Free',
                'preco' => 0,
                'intervalo' => 'month',
                'max_utilizadores' => 2,
                'max_clientes' => 25,
                'max_artigos' => 25,
                'arquivo_digital' => false,
                'calendario' => true,
                'financeiro' => false,
                'trial_dias' => 14,
                'ativo' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'nome' => 'Pro',
                'stripe_price_id' => 'price_1TbMoe8TVezXqt0fQLkUTYMw',
                'preco' => 29.90,
                'intervalo' => 'month',
                'max_utilizadores' => 10,
                'max_clientes' => 500,
                'max_artigos' => 1000,
                'arquivo_digital' => true,
                'calendario' => true,
                'financeiro' => true,
                'trial_dias' => 14,
                'ativo' => true,
            ]
        );

        Plan::updateOrCreate(
            ['slug' => 'business'],
            [
                'nome' => 'Business',
                'stripe_price_id' => 'price_1TbMtT8TVezXqt0fGz2GPc1x',
                'preco' => 99.90,
                'intervalo' => 'month',
                'max_utilizadores' => 999,
                'max_clientes' => 999999,
                'max_artigos' => 999999,
                'arquivo_digital' => true,
                'calendario' => true,
                'financeiro' => true,
                'trial_dias' => 14,
                'ativo' => true,
            ]
        );
    }
}