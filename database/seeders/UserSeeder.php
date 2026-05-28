<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();

        $user = User::create([
            'name' => 'Comercial',
            'email' => 'comercial@inovcorp.pt',
            'password' => Hash::make('password'),
        ]);

        $user->tenants()->attach($tenant->id, [
            'ativo' => true,
        ]);

        $user->assignRole('Administrador');
    }
}