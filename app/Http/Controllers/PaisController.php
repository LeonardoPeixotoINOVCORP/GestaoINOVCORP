<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaisController extends Controller
{
    public function index()
    {
        return Inertia::render('configuracoes/paises/Index', [
            'paises' => Pais::orderBy('nome')->paginate(20),
        ]);
    }

    public function create()
    {
        return Inertia::render('configuracoes/paises/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'   => 'required|string|max:255',
            'codigo' => 'required|string|max:3|unique:paises,codigo',
            'ativo'  => 'boolean',
        ]);

        Pais::create($request->all());

        return redirect()->route('configuracoes.paises.index')
            ->with('success', 'País criado com sucesso.');
    }

    public function edit(Pais $paise)
    {
        return Inertia::render('configuracoes/paises/Form', [
            'pais' => $paise,
        ]);
    }

    public function update(Request $request, Pais $paise)
    {
        $request->validate([
            'nome'   => 'required|string|max:255',
            'codigo' => 'required|string|max:3|unique:paises,codigo,' . $paise->id,
            'ativo'  => 'boolean',
        ]);

        $paise->update($request->all());

        return redirect()->route('configuracoes.paises.index')
            ->with('success', 'País atualizado com sucesso.');
    }

    public function destroy(Pais $paise)
    {
        $paise->delete();

        return back()->with('success', 'País removido com sucesso.');
    }
}