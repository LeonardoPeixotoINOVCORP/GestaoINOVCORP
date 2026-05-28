<?php

namespace App\Http\Controllers\GestaoAcessos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rule;

class PermissaoController extends Controller
{
    // Menus e ações disponíveis
    const MENUS = [
        'clientes', 'fornecedores', 'contactos', 'propostas',
        'encomendas', 'encomendas-fornecedor', 'faturas-fornecedor',
        'arquivo', 'contas-bancarias', 'conta-corrente', 'calendario',
        'utilizadores', 'permissoes', 'configuracoes',
    ];

    const ACOES = ['create', 'read', 'update', 'delete'];

    public function index()
    {
        $grupos = Role::query()
            ->where('team_id', session('tenant_id'))
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->paginate(20);

        return Inertia::render('gestao-acessos/permissoes/Index', [
            'grupos' => $grupos,
        ]);
    }

    public function create()
    {
        return Inertia::render('gestao-acessos/permissoes/Form', [
            'menus' => self::MENUS,
            'acoes' => self::ACOES,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')
                    ->where('team_id', session('tenant_id')),
            ],
            'permissoes'  => 'array',
            'ativo'       => 'boolean',
        ]);

        $role = Role::create([
            'name' => $request->nome,
            'team_id' => session('tenant_id'),
        ]);

        $this->syncPermissoes($role, $request->permissoes ?? []);

        return redirect()->route('gestao-acessos.permissoes.index')
            ->with('success', 'Grupo criado com sucesso.');
    }

    public function edit($id)
    {
        $permissao = Role::query()
            ->where('team_id', session('tenant_id'))
            ->findOrFail($id);

        return Inertia::render('gestao-acessos/permissoes/Form', [
            'grupo' => $permissao->load('permissions'),
            'menus' => self::MENUS,
            'acoes' => self::ACOES,
        ]);
    }

    public function update(Request $request, $id)
    {
        $permissao = Role::query()
            ->where('team_id', session('tenant_id'))
            ->findOrFail($id);

        $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->ignore($permissao->id)
                    ->where('team_id', session('tenant_id')),
            ],
            'permissoes' => 'array',
        ]);

        $permissao->update([
            'name' => $request->nome,
        ]);

        $this->syncPermissoes($permissao, $request->permissoes ?? []);

        return redirect()
            ->route('gestao-acessos.permissoes.index')
            ->with('success', 'Grupo atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $permissao = Role::query()
            ->where('team_id', session('tenant_id'))
            ->findOrFail($id);

        $permissao->delete();

        return back()->with('success', 'Grupo removido com sucesso.');
    }

    private function syncPermissoes(Role $role, array $permissoes): void
    {
        $nomes = [];

        foreach (self::MENUS as $menu) {
            foreach (self::ACOES as $acao) {
                $nome = "{$acao}_{$menu}";
                Permission::firstOrCreate(['name' => $nome]);

                if (in_array($nome, $permissoes)) {
                    $nomes[] = $nome;
                }
            }
        }

        $role->syncPermissions($nomes);
    }
}