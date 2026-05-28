<?php

namespace App\Actions\Fortify;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\CreatesNewUsers;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        Validator::make($input, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ])->validate();

        $user = User::create([
            'name'     => $input['name'],
            'email'    => $input['email'],
            'password' => Hash::make($input['password']),
            'can_create_tenants' => true,
        ]);

        // Cria slug único
        $slug = Str::slug($input['name']);
        $original = $slug;
        $counter = 1;

        while (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $original . '-' . $counter++;
        }

        $plan = Plan::where('ativo', true)
            ->orderBy('preco')
            ->first();

        // Cria tenant
        $tenant = Tenant::create([
            'nome'                => $input['name'],
            'slug'                => $slug,
            'owner_id'            => $user->id,
            'plan_id'             => $plan?->id,
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->addDays($plan?->trial_dias ?? 14),
        ]);

        // Associa user à tenant
        $tenant->users()->attach($user->id, [
            'role'  => 'owner',
            'ativo' => true,
        ]);

        // Define tenant atual
        session(['tenant_id' => $tenant->id]);

        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->id);

        // Cria role Administrador se não existir
        $adminRole = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
            'team_id' => $tenant->id,
        ]);

        // Dá todas as permissões
        $adminRole->syncPermissions(
            Permission::all()
        );

        // Atribui role ao user
        $user->assignRole($adminRole);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return $user;
    }
}