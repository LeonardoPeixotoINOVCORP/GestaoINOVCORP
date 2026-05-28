<?php

namespace App\Http\Controllers;

use App\Models\Entidade;
use App\Models\Pais;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EntidadeController extends Controller
{
    public function index(Request $request)
    {
        $tipo = $request->route('tipo') ?? $request->get('tipo', 'cliente');

        $entidades = Entidade::with('pais')
            ->when($tipo === 'cliente', fn($q) => $q->where('is_cliente', true))
            ->when($tipo === 'fornecedor', fn($q) => $q->where('is_fornecedor', true))
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('entidades/Index', [
            'entidades' => $entidades,
            'tipo'      => $tipo,
        ]);
    }

    public function create(Request $request)
    {
        $tipo = $request->route('tipo') ?? $request->get('tipo', 'cliente');

        return Inertia::render('entidades/Form', [
            'tipo'   => $tipo,
            'paises' => Pais::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        
        $tenant  = auth()->user()->currentTenant();
        $service = new PlanLimitService($tenant);

        if ($request->boolean('is_cliente') && !$service->podeAdicionarCliente()) {
            return back()->with('erro', $service->erroLimite('cliente'))->withInput();
        }


        $validated = $request->validate([
            'is_cliente'    => 'boolean',
            'is_fornecedor' => 'boolean',
            'nif'           => 'required|digits:9',
            'nome'          => 'required|string|max:255',
            'morada'        => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|regex:/^\d{4}-\d{3}$/',
            'localidade'    => 'nullable|string|max:255',
            'pais_id'       => 'nullable|exists:paises,id',
            'telefone'      => 'nullable|string|max:20',
            'telemovel'     => 'nullable|string|max:20',
            'website'       => 'nullable|url|max:255',
            'email'         => 'nullable|email|max:255',
            'rgpd'          => 'boolean',
            'observacoes'   => 'nullable|string',
            'ativo'         => 'boolean',
        ]);

        // Verificar NIF duplicado antes de criar
        $nifExiste = Entidade::withTrashed()
            ->select(['id', 'nif'])
            ->get()
            ->first(fn($e) => $e->nif === $validated['nif']);
            
        if ($nifExiste) {
            return back()
                ->withErrors(['nif' => 'Este NIF já existe.'])
                ->withInput();
        }

        $tenant = auth()->user()->currentTenant();

        if (
            $request->boolean('is_cliente')
            && !$tenant->canCreateClientes()
        ) {
            return back()->with('erro', 'O seu plano atingiu o limite máximo de clientes. Faça upgrade para continuar.');
        }

        if (
            $request->boolean('is_fornecedor')
            && !$tenant->canCreateFornecedores()
        ) {
            return back()->with('erro', 'O seu plano atingiu o limite máximo de fornecedores. Faça upgrade para continuar.');
        }

        $ultimoNumero = Entidade::where('tenant_id', $tenant->id)
            ->max('numero');

        $validated['numero'] = ($ultimoNumero ?? 0) + 1;

        $validated['tenant_id'] = $tenant->id;

        $entidade = Entidade::create($validated);

        activity()
            ->performedOn($entidade)
            ->causedBy(auth()->user())
            ->withProperties([
                'nome' => $entidade->nome,
                'nif'  => $entidade->nif
            ])
            ->log('criou entidade');

        $routeName = $request->boolean('is_fornecedor')
            ? 'fornecedores.index'
            : 'clientes.index';

        return redirect()
            ->route($routeName)
            ->with('success', 'Entidade criada com sucesso.');
    }

    public function edit(Entidade $entidade)
    {
        return Inertia::render('entidades/Form', [
            'entidade' => $entidade->load('pais'),
            'tipo'     => $entidade->is_fornecedor ? 'fornecedor' : 'cliente',
            'paises'   => Pais::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Entidade $entidade)
    {
        $validated = $request->validate([
            'is_cliente'    => 'boolean',
            'is_fornecedor' => 'boolean',
            'nif'           => 'required|digits:9',
            'nome'          => 'required|string|max:255',
            'morada'        => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|regex:/^\d{4}-\d{3}$/',
            'localidade'    => 'nullable|string|max:255',
            'pais_id'       => 'nullable|exists:paises,id',
            'telefone'      => 'nullable|string|max:20',
            'telemovel'     => 'nullable|string|max:20',
            'website'       => 'nullable|url|max:255',
            'email'         => 'nullable|email|max:255',
            'rgpd'          => 'boolean',
            'observacoes'   => 'nullable|string',
            'ativo'         => 'boolean',
        ]);

        $nifExiste = Entidade::withTrashed()
            ->where('id', '!=', $entidade->id)
            ->select(['id', 'nif'])
            ->get()
            ->first(fn($e) => $e->nif === $validated['nif']);
            
        if ($nifExiste) {
            return back()
                ->withErrors(['nif' => 'Este NIF já existe.'])
                ->withInput();
        }

        $entidade->update($validated);

        activity()
            ->performedOn($entidade)
            ->causedBy(auth()->user())
            ->withProperties(['nome' => $entidade->nome, 'nif' => $entidade->nif, 'ativo' => $entidade->ativo])
            ->log('atualizou entidade');

        $routeName = $entidade->is_fornecedor ? 'fornecedores.index' : 'clientes.index';

        return redirect()->route($routeName)
            ->with('success', 'Entidade atualizada com sucesso.');
    }

    public function destroy(Entidade $entidade)
    {
        activity()
            ->performedOn($entidade)
            ->causedBy(auth()->user())
            ->withProperties(['nome' => $entidade->nome, 'nif' => $entidade->nif])
            ->log('removeu entidade');

        $entidade->delete();

        return back()->with('success', 'Entidade removida com sucesso.');
    }
}