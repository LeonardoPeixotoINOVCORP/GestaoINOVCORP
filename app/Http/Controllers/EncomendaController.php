<?php

namespace App\Http\Controllers;

use App\Models\Artigo;
use App\Models\Encomenda;
use App\Models\EncomendaLinha;
use App\Models\Entidade;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use Inertia\Inertia;

use Barryvdh\DomPDF\Facade\Pdf;

class EncomendaController extends Controller
{
    public function index(Request $request)
    {
        $tipo = $request->route('tipo') ?? $request->get('tipo', 'cliente');

        $encomendas = Encomenda::with(['entidade', 'linhas'])
            ->where('tipo', $tipo)
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('encomendas/Index', [
            'encomendas' => $encomendas,
            'tipo'       => $tipo,
        ]);
    }

    public function create(Request $request)
    {
        $tipo = $request->route('tipo') ?? $request->get('tipo', 'cliente');

        return Inertia::render('encomendas/Form', [
            'tipo'         => $tipo,
            'clientes'     => Entidade::where('is_cliente', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'nif']),
            'fornecedores' => Entidade::where('is_fornecedor', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'artigos'      => Artigo::where('ativo', true)->orderBy('nome')->get(['id', 'referencia', 'nome', 'preco', 'iva']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entidade_id'            => 'required|exists:entidades,id',
            'tipo'                   => 'required|in:cliente,fornecedor',
            'observacoes'            => 'nullable|string',
            'estado'                 => 'required|in:rascunho,fechado',
            'linhas'                 => 'required|array|min:1',
            'linhas.*.artigo_id'     => 'required|exists:artigos,id',
            'linhas.*.fornecedor_id' => 'nullable|exists:entidades,id',
            'linhas.*.quantidade'    => 'required|integer|min:1',
            'linhas.*.preco_venda'   => 'required|numeric|min:0',
            'linhas.*.preco_custo'   => 'nullable|numeric|min:0',
            'linhas.*.iva'           => 'required|numeric|min:0',
        ]);

        $encomenda = Encomenda::create([
            'entidade_id'    => $validated['entidade_id'],
            'data_encomenda' => $validated['estado'] === 'fechado' ? Carbon::now() : null,
            'tipo'           => $validated['tipo'],
            'estado'         => $validated['estado'],
            'observacoes'    => $validated['observacoes'] ?? null,
        ]);

        foreach ($validated['linhas'] as $linha) {
            EncomendaLinha::create([
                'encomenda_id'  => $encomenda->id,
                'artigo_id'     => $linha['artigo_id'],
                'fornecedor_id' => $linha['fornecedor_id'] ?? null,
                'quantidade'    => $linha['quantidade'],
                'preco_venda'   => $linha['preco_venda'],
                'preco_custo'   => $linha['preco_custo'] ?? 0,
                'iva'           => $linha['iva'],
            ]);
        }

        activity()
            ->performedOn($encomenda)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $encomenda->numero, 'tipo' => $encomenda->tipo, 'estado' => $encomenda->estado])
            ->log('criou encomenda');

        return redirect()->route(
            $validated['tipo'] === 'fornecedor' ? 'encomendas.fornecedor.index' : 'encomendas.index'
        )->with('success', 'Encomenda criada com sucesso.');
    }

    public function edit(Encomenda $encomenda)
    {
        return Inertia::render('encomendas/Form', [
            'encomenda'    => $encomenda->load(['linhas.artigo', 'linhas.fornecedor']),
            'tipo'         => $encomenda->tipo,
            'clientes'     => Entidade::where('is_cliente', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'nif']),
            'fornecedores' => Entidade::where('is_fornecedor', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'artigos'      => Artigo::where('ativo', true)->orderBy('nome')->get(['id', 'referencia', 'nome', 'preco', 'iva']),
        ]);
    }

    public function update(Request $request, Encomenda $encomenda)
    {
        $validated = $request->validate([
            'entidade_id'            => 'required|exists:entidades,id',
            'observacoes'            => 'nullable|string',
            'estado'                 => 'required|in:rascunho,fechado',
            'linhas'                 => 'required|array|min:1',
            'linhas.*.artigo_id'     => 'required|exists:artigos,id',
            'linhas.*.fornecedor_id' => 'nullable|exists:entidades,id',
            'linhas.*.quantidade'    => 'required|integer|min:1',
            'linhas.*.preco_venda'   => 'required|numeric|min:0',
            'linhas.*.preco_custo'   => 'nullable|numeric|min:0',
            'linhas.*.iva'           => 'required|numeric|min:0',
        ]);

        $encomenda->update([
            'entidade_id'    => $validated['entidade_id'],
            'estado'         => $validated['estado'],
            'observacoes'    => $validated['observacoes'] ?? null,
            'data_encomenda' => $validated['estado'] === 'fechado' && !$encomenda->data_encomenda
                ? Carbon::now() : $encomenda->data_encomenda,
        ]);

        $encomenda->linhas()->delete();

        foreach ($validated['linhas'] as $linha) {
            EncomendaLinha::create([
                'encomenda_id'  => $encomenda->id,
                'artigo_id'     => $linha['artigo_id'],
                'fornecedor_id' => $linha['fornecedor_id'] ?? null,
                'quantidade'    => $linha['quantidade'],
                'preco_venda'   => $linha['preco_venda'],
                'preco_custo'   => $linha['preco_custo'] ?? 0,
                'iva'           => $linha['iva'],
            ]);
        }

        activity()
            ->performedOn($encomenda)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $encomenda->numero, 'estado' => $encomenda->estado])
            ->log('atualizou encomenda');

        return redirect()->route('encomendas.index', ['tipo' => $encomenda->tipo])
            ->with('success', 'Encomenda atualizada com sucesso.');
    }

    public function destroy(Encomenda $encomenda)
    {
        activity()
            ->performedOn($encomenda)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $encomenda->numero, 'tipo' => $encomenda->tipo])
            ->log('removeu encomenda');

        $encomenda->delete();

        return back()->with('success', 'Encomenda removida com sucesso.');
    }

    public function converterEmEncomendasFornecedor(Encomenda $encomenda)
    {
        if ($encomenda->estado !== 'fechado') {
            return back()->with('error', 'A encomenda tem de estar fechada para converter.');
        }

        $linhasPorFornecedor = $encomenda->linhas
            ->whereNotNull('fornecedor_id')
            ->groupBy('fornecedor_id');

        foreach ($linhasPorFornecedor as $fornecedorId => $linhas) {
            $novaEncomenda = Encomenda::create([
                'entidade_id'    => $fornecedorId,
                'proposta_id'    => null,
                'data_encomenda' => Carbon::now(),
                'tipo'           => 'fornecedor',
                'estado'         => 'rascunho',
            ]);

            foreach ($linhas as $linha) {
                EncomendaLinha::create([
                    'encomenda_id'  => $novaEncomenda->id,
                    'artigo_id'     => $linha->artigo_id,
                    'fornecedor_id' => $fornecedorId,
                    'quantidade'    => $linha->quantidade,
                    'preco_venda'   => $linha->preco_venda,
                    'preco_custo'   => $linha->preco_custo,
                    'iva'           => $linha->iva,
                ]);
            }

            activity()
                ->performedOn($novaEncomenda)
                ->causedBy(auth()->user())
                ->withProperties(['encomenda_origem' => $encomenda->numero, 'encomenda_nova' => $novaEncomenda->numero])
                ->log('converteu encomenda cliente em encomenda fornecedor');
        }

        return redirect()->route('encomendas.fornecedor.index')
            ->with('success', 'Encomendas de fornecedor criadas com sucesso.');
    }

    public function pdf(Encomenda $encomenda)
    {
        $encomenda->load(['entidade', 'linhas.artigo']);

        $tenant = view()->getShared()['tenantAtivo'];
        $empresa = $this->empresaFromTenant();

        $pdf = Pdf::loadView('pdf.proposta', [
            'documento' => $encomenda,
            'empresa'   => $empresa,
            'tipo'      => 'Encomenda',
        ])->setPaper('a4');

        return $pdf->download("encomenda-{$encomenda->numero}.pdf");
    }
    
    private function empresaFromTenant(): array
    {
        $tenant = auth()->user()->tenants()
            ->where('tenants.id', session('tenant_id'))
            ->first();

        return [
            'nome'          => $tenant->nome ?? config('app.name'),
            'morada'        => $tenant->morada ?? '',
            'codigo_postal' => $tenant->codigo_postal ?? '',
            'localidade'    => $tenant->localidade ?? '',
            'nif'           => $tenant->nif ?? '',
            'telefone'      => $tenant->telefone ?? '',
            'email'         => $tenant->email ?? '',
            'website'       => $tenant->website ?? '',
            'logotipo'      => $tenant->logotipo 
                                ? Storage::disk('public')->path($tenant->logotipo) 
                                : null,
        ];
    }
}