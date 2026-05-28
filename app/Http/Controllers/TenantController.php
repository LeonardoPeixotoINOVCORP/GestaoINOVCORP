<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class TenantController extends Controller
{
    public function select()
    {
        $tenants = auth()->user()->tenants()->wherePivot('ativo', true)->get();

        if ($tenants->count() === 1) {
            session(['tenant_id' => $tenants->first()->id]);
            return redirect()->intended(route('dashboard'));
        }

        return Inertia::render('tenants/Select', [
            'tenants' => $tenants,
        ]);
    }

    public function switch(Tenant $tenant)
    {
        $user = auth()->user();

        $hasAccess = $user->tenants()
            ->where('tenants.id', $tenant->id)
            ->wherePivot('ativo', true)
            ->exists();

        if (!$hasAccess) {
            abort(403);
        }

        session(['tenant_id' => $tenant->id]);

        return redirect()->back()
            ->with('sucesso', "Trocou para {$tenant->nome}.");
    }

    public function create()
    {
        $plans = Plan::where('ativo', true)->orderBy('preco')->get();

        return Inertia::render('tenants/Create', [
            'plans' => $plans,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        while (Tenant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $plan = Plan::find($request->plan_id) ?? Plan::where('ativo', true)->orderBy('preco')->first();

        $tenant = Tenant::create([
            'nome'                => $request->name,
            'slug'                => $slug,
            'owner_id'            => auth()->id(),
            'plan_id'             => $plan->id,
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->addDays($plan->trial_dias ?? 14),
        ]);

        $tenant->users()->attach(auth()->id(), [
            'role' => 'owner',
            'ativo' => true
        ]);

        session(['tenant_id' => $tenant->id]);

        app(PermissionRegistrar::class)
            ->setPermissionsTeamId($tenant->id);

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $user = auth()->user()->fresh();

        $adminRole = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
            'team_id' => $tenant->id,
        ]);

        $adminRole->syncPermissions(Permission::all());

        $user->assignRole($adminRole);

        $user->load('roles');

        // Limpa cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        session(['tenant_id' => $tenant->id]);

        return redirect()->route('onboarding.wizard')
            ->with('sucesso', 'Tenant criado com sucesso.');
    }
}