<?php

namespace App\Http\Controllers;

use App\Models\Encomenda;
use App\Models\Entidade;
use App\Models\FaturaFornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class FaturaFornecedorController extends Controller
{
    public function index()
    {
        $faturas = FaturaFornecedor::with(['fornecedor', 'encomenda'])
            ->orderByDesc('data_fatura')
            ->paginate(20);

        return Inertia::render('financeiro/faturas/Index', [
            'faturas' => $faturas,
        ]);
    }

    public function create()
    {
        return Inertia::render('financeiro/faturas/Form', [
            'fornecedores' => Entidade::where('is_fornecedor', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'encomendas'   => Encomenda::where('tipo', 'fornecedor')->where('estado', 'fechado')->orderByDesc('created_at')->get(['id', 'numero', 'entidade_id']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_fatura'     => 'required|date',
            'data_vencimento' => 'nullable|date',
            'fornecedor_id'   => 'required|exists:entidades,id',
            'encomenda_id'    => 'nullable|exists:encomendas,id',
            'valor_total'     => 'required|numeric|min:0',
            'documento'       => 'nullable|file|max:10240',
            'estado'          => 'required|in:pendente,paga',
        ]);

        if ($request->hasFile('documento')) {
            $validated['documento'] = $request->file('documento')
                ->store('faturas/documentos', 'local');
        }

        $fatura = FaturaFornecedor::create($validated);

        activity()
            ->performedOn($fatura)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $fatura->numero, 'valor_total' => $fatura->valor_total, 'estado' => $fatura->estado])
            ->log('criou fatura fornecedor');

        return redirect()->route('faturas-fornecedor.index')
            ->with('success', 'Fatura criada com sucesso.');
    }

    public function edit(FaturaFornecedor $faturasFornecedor)
    {
        return Inertia::render('financeiro/faturas/Form', [
            'fatura'       => $faturasFornecedor->load(['fornecedor', 'encomenda']),
            'fornecedores' => Entidade::where('is_fornecedor', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'encomendas'   => Encomenda::where('tipo', 'fornecedor')->where('estado', 'fechado')->orderByDesc('created_at')->get(['id', 'numero', 'entidade_id']),
        ]);
    }

    public function update(Request $request, FaturaFornecedor $faturasFornecedor)
    {
        $validated = $request->validate([
            'data_fatura'     => 'required|date',
            'data_vencimento' => 'nullable|date',
            'fornecedor_id'   => 'required|exists:entidades,id',
            'encomenda_id'    => 'nullable|exists:encomendas,id',
            'valor_total'     => 'required|numeric|min:0',
            'documento'       => 'nullable|file|max:10240',
            'estado'          => 'required|in:pendente,paga',
        ]);

        if ($request->hasFile('documento')) {
            if ($faturasFornecedor->documento) {
                Storage::disk('local')->delete($faturasFornecedor->documento);
            }
            $validated['documento'] = $request->file('documento')
                ->store('faturas/documentos', 'local');
        }

        $faturasFornecedor->update($validated);

        activity()
            ->performedOn($faturasFornecedor)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $faturasFornecedor->numero, 'valor_total' => $faturasFornecedor->valor_total, 'estado' => $faturasFornecedor->estado])
            ->log('atualizou fatura fornecedor');

        return redirect()->route('faturas-fornecedor.index')
            ->with('success', 'Fatura atualizada com sucesso.');
    }

    public function destroy(FaturaFornecedor $faturasFornecedor)
    {
        activity()
            ->performedOn($faturasFornecedor)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $faturasFornecedor->numero])
            ->log('removeu fatura fornecedor');

        if ($faturasFornecedor->documento) {
            Storage::disk('local')->delete($faturasFornecedor->documento);
        }

        $faturasFornecedor->delete();

        return back()->with('success', 'Fatura removida com sucesso.');
    }

    public function download(FaturaFornecedor $faturasFornecedor)
    {
        if (!$faturasFornecedor->documento) {
            abort(404);
        }

        return Storage::download($faturasFornecedor->documento);
    }

    public function enviarComprovativo(Request $request, FaturaFornecedor $faturasFornecedor)
    {
        $request->validate([
            'comprovativo' => 'required|file|max:10240',
        ]);

        $path = $request->file('comprovativo')
            ->store('faturas/comprovativos', 'local');

        $faturasFornecedor->update([
            'comprovativo' => $path,
            'estado'       => 'paga',
        ]);

        activity()
            ->performedOn($faturasFornecedor)
            ->causedBy(auth()->user())
            ->withProperties(['numero' => $faturasFornecedor->numero])
            ->log('enviou comprovativo de pagamento');

        $fornecedor = $faturasFornecedor->fornecedor;

        if ($fornecedor->email) {
            Mail::send([], [], function ($message) use ($fornecedor, $faturasFornecedor, $path) {
                $message->to($fornecedor->email)
                    ->subject("Comprovativo de Pagamento - Fatura {$faturasFornecedor->numero}")
                    ->text(
                        "Estimado(a) {$fornecedor->nome},\n\n" .
                        "Enviamos em anexo o comprovativo de pagamento da fatura {$faturasFornecedor->numero}.\n\n" .
                        "Qualquer questão, entre em contacto connosco.\n\n" .
                        "Cumprimentos"
                    )
                    ->attach(Storage::disk('local')->path($path));
            });
        }

        return back()->with('success', 'Comprovativo enviado com sucesso.');
    }
}