<?php

namespace App\Http\Controllers;

use App\Models\ContaCorrenteCliente;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContaCorrenteClienteController extends Controller
{
    public function index()
    {
        $movimentos = ContaCorrenteCliente::with('entidade')
            ->orderByDesc('data_movimento')
            ->paginate(20);

        return Inertia::render('financeiro/conta-corrente/Index', [
            'movimentos' => $movimentos,
        ]);
    }

    public function create()
    {
        return Inertia::render('financeiro/conta-corrente/Form', [
            'clientes' => Entidade::where('is_cliente', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'entidade_id' => 'required|exists:entidades,id',
            'data_movimento'        => 'required|date',
            'descricao'   => 'required|string|max:255',
            'valor'       => 'required|numeric',
            'tipo'        => 'required|in:debito,credito',
            'referencia'  => 'nullable|string|max:255',
        ]);

        ContaCorrenteCliente::create($request->all());

        return redirect()->route('conta-corrente.index')
            ->with('success', 'Movimento criado com sucesso.');
    }

    public function edit(ContaCorrenteCliente $contaCorrente)
    {
        return Inertia::render('financeiro/conta-corrente/Form', [
            'movimento' => $contaCorrente->load('entidade'),
            'clientes'  => Entidade::where('is_cliente', true)->where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function update(Request $request, ContaCorrenteCliente $contaCorrente)
    {
        $request->validate([
            'entidade_id' => 'required|exists:entidades,id',
            'data_movimento'        => 'required|date',
            'descricao'   => 'required|string|max:255',
            'valor'       => 'required|numeric',
            'tipo'        => 'required|in:debito,credito',
            'referencia'  => 'nullable|string|max:255',
        ]);

        $contaCorrente->update($request->all());

        return redirect()->route('conta-corrente.index')
            ->with('success', 'Movimento atualizado com sucesso.');
    }

    public function destroy(ContaCorrenteCliente $contaCorrente)
    {
        $contaCorrente->delete();

        return back()->with('success', 'Movimento removido com sucesso.');
    }
}