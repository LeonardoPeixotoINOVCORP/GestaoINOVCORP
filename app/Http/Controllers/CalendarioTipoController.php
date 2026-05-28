<?php

namespace App\Http\Controllers;

use App\Models\CalendarioTipo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarioTipoController extends Controller
{
    public function index()
    {
        return Inertia::render('configuracoes/calendario-tipos/Index', [
            'tipos' => CalendarioTipo::orderBy('nome')->paginate(20),
        ]);
    }

    public function create()
    {
        return Inertia::render('configuracoes/calendario-tipos/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'cor'  => 'required|string|max:7',
            'ativo' => 'boolean',
        ]);

        CalendarioTipo::create($request->all());

        return redirect()->route('configuracoes.calendario-tipos.index')
            ->with('success', 'Tipo criado com sucesso.');
    }

    public function edit(CalendarioTipo $calendarioTipo)
    {
        return Inertia::render('configuracoes/calendario-tipos/Form', [
            'tipo' => $calendarioTipo,
        ]);
    }

    public function update(Request $request, CalendarioTipo $calendarioTipo)
    {
        $request->validate([
            'nome'  => 'required|string|max:255',
            'cor'   => 'required|string|max:7',
            'ativo' => 'boolean',
        ]);

        $calendarioTipo->update($request->all());

        return redirect()->route('configuracoes.calendario-tipos.index')
            ->with('success', 'Tipo atualizado com sucesso.');
    }

    public function destroy(CalendarioTipo $calendarioTipo)
    {
        $calendarioTipo->delete();

        return back()->with('success', 'Tipo removido com sucesso.');
    }
}