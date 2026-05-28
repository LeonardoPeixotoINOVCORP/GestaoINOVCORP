<?php

namespace App\Http\Controllers;

use App\Models\Artigo;
use App\Models\Encomenda;
use App\Models\EncomendaLinha;
use App\Models\Entidade;
use App\Models\Proposta;
use App\Models\PropostaLinha;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use Inertia\Inertia;

use Barryvdh\DomPDF\Facade\Pdf;

class PropostaController extends Controller
{
    public function index()
    {
        $propostas = Proposta::with(['entidade', 'linhas'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('propostas/Index', [
            'propostas' => $propostas,
        ]);
    }

    public function create()
    {
        return Inertia::render('propostas/Form', [
            'clientes'     => Entidade::where('is_cliente', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'nif']),
            'fornecedores' => Entidade::where('is_fornecedor', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'artigos'      => Artigo::where('ativo', true)->orderBy('nome')->get(['id', 'referencia', 'nome', 'preco', 'iva']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entidade_id'            => 'required|exists:entidades,id',
            'validade'               => 'nullable|date',
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

        $proposta = Proposta::create([
            'entidade_id'   => $validated['entidade_id'],
            'validade'      => $validated['validade'] ?? Carbon::now()->addDays(30),
            'observacoes'   => $validated['observacoes'] ?? null,
            'estado'        => $validated['estado'],
            'data_proposta' => $validated['estado'] === 'fechado' ? Carbon::now() : null,
        ]);

        foreach ($validated['linhas'] as $linha) {
            PropostaLinha::create([
                'proposta_id'   => $proposta->id,
                'artigo_id'     => $linha['artigo_id'],
                'fornecedor_id' => $linha['fornecedor_id'] ?? null,
                'quantidade'    => $linha['quantidade'],
                'preco_venda'   => $linha['preco_venda'],
                'preco_custo'   => $linha['preco_custo'] ?? 0,
                'iva'           => $linha['iva'],
            ]);
        }

        activity()
            ->performedOn($proposta)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $proposta->numero, 'estado' => $proposta->estado])
            ->log('criou proposta');

        return redirect()->route('propostas.index')
            ->with('success', 'Proposta criada com sucesso.');
    }

    public function edit(Proposta $proposta)
    {
        return Inertia::render('propostas/Form', [
            'proposta'     => $proposta->load(['linhas.artigo', 'linhas.fornecedor']),
            'clientes'     => Entidade::where('is_cliente', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'nif']),
            'fornecedores' => Entidade::where('is_fornecedor', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'artigos'      => Artigo::where('ativo', true)->orderBy('nome')->get(['id', 'referencia', 'nome', 'preco', 'iva']),
        ]);
    }

    public function update(Request $request, Proposta $proposta)
    {
        $validated = $request->validate([
            'entidade_id'            => 'required|exists:entidades,id',
            'validade'               => 'nullable|date',
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

        $proposta->update([
            'entidade_id'   => $validated['entidade_id'],
            'validade'      => $validated['validade'],
            'observacoes'   => $validated['observacoes'] ?? null,
            'estado'        => $validated['estado'],
            'data_proposta' => $validated['estado'] === 'fechado' && !$proposta->data_proposta
                ? Carbon::now() : $proposta->data_proposta,
        ]);

        $proposta->linhas()->delete();

        foreach ($validated['linhas'] as $linha) {
            PropostaLinha::create([
                'proposta_id'   => $proposta->id,
                'artigo_id'     => $linha['artigo_id'],
                'fornecedor_id' => $linha['fornecedor_id'] ?? null,
                'quantidade'    => $linha['quantidade'],
                'preco_venda'   => $linha['preco_venda'],
                'preco_custo'   => $linha['preco_custo'] ?? 0,
                'iva'           => $linha['iva'],
            ]);
        }

        activity()
            ->performedOn($proposta)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $proposta->numero, 'estado' => $proposta->estado])
            ->log('atualizou proposta');

        return redirect()->route('propostas.index')
            ->with('success', 'Proposta atualizada com sucesso.');
    }

    public function destroy(Proposta $proposta)
    {
        activity()
            ->performedOn($proposta)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $proposta->numero])
            ->log('removeu proposta');

        $proposta->delete();

        return back()->with('success', 'Proposta removida com sucesso.');
    }

    public function converterEmEncomenda(Proposta $proposta)
    {
        $encomenda = Encomenda::create([
            'entidade_id'    => $proposta->entidade_id,
            'proposta_id'    => $proposta->id,
            'data_encomenda' => null,
            'tipo'           => 'cliente',
            'estado'         => 'rascunho',
        ]);

        foreach ($proposta->linhas as $linha) {
            EncomendaLinha::create([
                'encomenda_id'  => $encomenda->id,
                'artigo_id'     => $linha->artigo_id,
                'fornecedor_id' => $linha->fornecedor_id,
                'quantidade'    => $linha->quantidade,
                'preco_venda'   => $linha->preco_venda,
                'preco_custo'   => $linha->preco_custo,
                'iva'           => $linha->iva,
            ]);
        }

        activity()
            ->performedOn($proposta)
            ->causedBy(auth()->user())
            ->withProperties(['proposta_numero' => $proposta->numero, 'encomenda_numero' => $encomenda->numero])
            ->log('converteu proposta em encomenda');

        return redirect()->route('encomendas.edit', $encomenda->id)
            ->with('success', 'Proposta convertida em encomenda.');
    }

    public function pdf(Proposta $proposta)
    {
        $proposta->load(['entidade', 'linhas.artigo']);
        $empresa = $this->empresaFromTenant();

        $pdf = Pdf::loadView('pdf.proposta', [
            'documento' => $proposta,
            'empresa'   => $empresa,
            'tipo'      => 'Proposta',
        ])->setPaper('a4');

        return $pdf->download("proposta-{$proposta->numero}.pdf");
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