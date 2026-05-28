<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::create([
            'name' => 'Administrador',
            'email' => 'admin@inovcorp.pt',
            'password' => Hash::make('password'),
        ]);

        $tenant = Tenant::create([
            'nome' => 'INOVCORP',
            'slug' => 'inovcorp',
            'owner_id' => $owner->id,
        ]);

        $owner->tenants()->attach($tenant->id, [
            'ativo' => true,
        ]);

        session(['tenant_id' => $tenant->id]);

        $owner->assignRole('Administrador');
    }
}