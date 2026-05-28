<?php

namespace App\Http\Controllers;

use App\Models\Artigo;
use App\Services\PlanLimitService;
use Illuminate\Http\Request;
use Inertia\Inertia;


class ArtigoController extends Controller
{
    public function index(Request $request)
    {
        $artigos = Artigo::query()
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('configuracoes/artigos/Index', [
            'artigos' => $artigos,
        ]);
    }

    public function create()
    {
        return Inertia::render('configuracoes/artigos/Form');
    }

    public function store(Request $request)
    {
        $tenant  = auth()->user()->currentTenant();
        $service = new PlanLimitService($tenant);

        if (!$service->podeAdicionarArtigo()) {
            return back()->with('erro', $service->erroLimite('artigo'))->withInput();
        }

        $validated = $request->validate([
            'referencia' => 'required|string|max:50',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'ativo' => 'boolean',
        ]);

        $tenant = auth()->user()->currentTenant();

        if (!$tenant->canCreateArtigos()) {
            return back()->with(
                'erro',
                'O seu plano atingiu o limite máximo de artigos. Faça upgrade para continuar.'
            );
        }

        $validated['tenant_id'] = $tenant->id;

        $artigo = Artigo::create($validated);

        activity()
            ->performedOn($artigo)
            ->causedBy(auth()->user())
            ->withProperties([
                'nome' => $artigo->nome,
                'referencia' => $artigo->referencia,
                'preco' => $artigo->preco,
            ])
            ->log('criou artigo');

        return redirect()
            ->route('configuracoes.artigos.index')
            ->with('success', 'Artigo criado com sucesso.');
    }

    public function edit(Artigo $artigo)
    {
        return Inertia::render('configuracoes/artigos/Form', [
            'artigo' => $artigo,
        ]);
    }

    public function update(Request $request, Artigo $artigo)
    {
        $validated = $request->validate([
            'referencia' => 'required|string|max:50',
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'preco' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'iva' => 'required|numeric|min:0|max:100',
            'foto' => 'nullable|image|max:2048',
            'observacoes' => 'nullable|string',
            'ativo' => 'boolean',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('artigos', 'public');
            $validated['foto'] = $path;
        } else {
            unset($validated['foto']);
        }

        $artigo->update($validated);

        activity()
            ->performedOn($artigo)
            ->causedBy(auth()->user())
            ->withProperties([
                'nome' => $artigo->nome,
                'referencia' => $artigo->referencia,
                'preco' => $artigo->preco,
            ])
            ->log('atualizou artigo');

        return redirect()
            ->route('configuracoes.artigos.index')
            ->with('success', 'Artigo atualizado com sucesso.');
    }

    public function destroy(Artigo $artigo)
    {
        activity()
            ->performedOn($artigo)
            ->causedBy(auth()->user())
            ->withProperties([
                'nome' => $artigo->nome,
                'referencia' => $artigo->referencia,
                'preco' => $artigo->preco,
            ])
            ->log('removeu artigo');

        $artigo->delete();

        return back()->with('success', 'Artigo removido com sucesso.');
    }
}