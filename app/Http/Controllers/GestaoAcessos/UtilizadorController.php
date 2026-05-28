<?php

namespace App\Http\Controllers\GestaoAcessos;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class UtilizadorController extends Controller
{
    public function index()
    {
        $tenantId = session('tenant_id');

        $utilizadores = User::query()
            ->whereHas('tenants', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->with([
                'roles' => function ($q) {
                    $q->where('roles.team_id', session('tenant_id'));
                }
            ])
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('gestao-acessos/utilizadores/Index', [
            'utilizadores' => $utilizadores,
        ]);
    }

    public function create()
    {
        return Inertia::render('gestao-acessos/utilizadores/Form', [
            'grupos' => Role::query()
                ->where('team_id', session('tenant_id'))
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('roles', 'name')
                    ->where('team_id', session('tenant_id')),
            ],
            'ativo'    => 'boolean',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
        ]);

        $user->tenants()->attach(session('tenant_id'), [
            'role' => 'user',
            'ativo' => true,
        ]);

        if (!empty($validated['role'])) {
            $user->assignRole($validated['role']);
        }

        return redirect()->route('utilizadores.index')
            ->with('success', 'Utilizador criado com sucesso.');
    }

    public function edit($id)
    {
        $tenantId = session('tenant_id');

        $utilizador = User::query()
            ->whereHas('tenants', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->with([
                'roles' => function ($q) {
                    $q->where('roles.team_id', session('tenant_id'));
                }
            ])
            ->findOrFail($id);

        return Inertia::render('gestao-acessos/utilizadores/Form', [
            'utilizador' => $utilizador,

            'grupos' => Role::query()
                ->where('team_id', $tenantId)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenantId = session('tenant_id');

        $utilizador = User::query()
            ->whereHas('tenants', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->findOrFail($id);
            
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $utilizador->id,
            'phone'    => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'role' => [
                'nullable',
                \Illuminate\Validation\Rule::exists('roles', 'name')
                    ->where('team_id', session('tenant_id')),
            ],
        ]);

        $utilizador->update([
            'name'  => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $utilizador->update(['password' => Hash::make($validated['password'])]);
        }

        $utilizador->syncRoles($validated['role'] ? [$validated['role']] : []);

        return redirect()->route('gestao-acessos.utilizadores.index')
            ->with('success', 'Utilizador atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $tenantId = session('tenant_id');

        $utilizador = User::query()
            ->whereHas('tenants', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
            ->findOrFail($id);
        if ($utilizador->id === auth()->id()) {
            return back()->with('error', 'Não podes remover o teu próprio utilizador.');
        }

        if ($utilizador->tenants()->count() === 1) {
            $utilizador->delete();
        } else {
            $utilizador->tenants()->detach($tenantId);
        }

        return back()->with('success', 'Utilizador removido com sucesso.');
    }
}