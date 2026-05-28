<?php

namespace App\Http\Controllers;

use App\Models\ContaBancaria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContaBancariaController extends Controller
{
    public function index()
    {
        return Inertia::render('financeiro/contas-bancarias/Index', [
            'contas' => ContaBancaria::orderBy('banco')->paginate(20),
        ]);
    }

    public function create()
    {
        return Inertia::render('financeiro/contas-bancarias/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'banco'   => 'required|string|max:255',
            'iban'    => 'required|string|max:34|unique:contas_bancarias,iban',
            'swift'   => 'nullable|string|max:11',
            'titular' => 'required|string|max:255',
            'ativo'   => 'boolean',
        ]);

        ContaBancaria::create($request->all());

        return redirect()->route('contas-bancarias.index')
            ->with('success', 'Conta bancária criada com sucesso.');
    }

    public function edit(ContaBancaria $contasBancaria)
    {
        return Inertia::render('financeiro/contas-bancarias/Form', [
            'conta' => $contasBancaria,
        ]);
    }

    public function update(Request $request, ContaBancaria $contasBancaria)
    {
        $request->validate([
            'banco'   => 'required|string|max:255',
            'iban'    => 'required|string|max:34|unique:contas_bancarias,iban,' . $contasBancaria->id,
            'swift'   => 'nullable|string|max:11',
            'titular' => 'required|string|max:255',
            'ativo'   => 'boolean',
        ]);

        $contasBancaria->update($request->all());

        return redirect()->route('contas-bancarias.index')
            ->with('success', 'Conta bancária atualizada com sucesso.');
    }

    public function destroy(ContaBancaria $contasBancaria)
    {
        $contasBancaria->delete();

        return back()->with('success', 'Conta bancária removida com sucesso.');
    }
}