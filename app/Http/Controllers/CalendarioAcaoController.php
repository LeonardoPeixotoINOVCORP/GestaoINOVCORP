<?php

namespace App\Http\Controllers;

use App\Models\CalendarioAcao;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarioAcaoController extends Controller
{
    public function index()
    {
        return Inertia::render('configuracoes/calendario-acoes/Index', [
            'acoes' => CalendarioAcao::orderBy('nome')->paginate(20),
        ]);
    }

    public function create()
    {
        return Inertia::render('configuracoes/calendario-acoes/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'  => 'required|string|max:255',
            'ativo' => 'boolean',
        ]);

        CalendarioAcao::create($request->all());

        return redirect()->route('configuracoes.calendario-acoes.index')
            ->with('success', 'Ação criada com sucesso.');
    }

    public function edit(CalendarioAcao $calendarioAcao)
    {
        return Inertia::render('configuracoes/calendario-acoes/Form', [
            'acao' => $calendarioAcao,
        ]);
    }

    public function update(Request $request, CalendarioAcao $calendarioAcao)
    {
        $request->validate([
            'nome'  => 'required|string|max:255',
            'ativo' => 'boolean',
        ]);

        $calendarioAcao->update($request->all());

        return redirect()->route('configuracoes.calendario-acoes.index')
            ->with('success', 'Ação atualizada com sucesso.');
    }

    public function destroy(CalendarioAcao $calendarioAcao)
    {
        $calendarioAcao->delete();

        return back()->with('success', 'Ação removida com sucesso.');
    }
}