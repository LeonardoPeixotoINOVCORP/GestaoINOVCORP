<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            'clientes',
            'fornecedores',
            'contactos',
            'propostas',
            'encomendas',
            'encomendas-fornecedor',
            'faturas-fornecedor',
            'arquivo',
            'contas-bancarias',
            'conta-corrente',
            'calendario',
            'configuracoes',
            'utilizadores',
            'permissoes',
            'logs',
        ];

        $acoes = [
            'create',
            'read',
            'update',
            'delete',
        ];

        foreach ($menus as $menu) {

            foreach ($acoes as $acao) {

                Permission::firstOrCreate([
                    'name' => "{$acao}_{$menu}",
                    'guard_name' => 'web',
                ]);
            }
        }

        $admin = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions(Permission::all());
    }
}