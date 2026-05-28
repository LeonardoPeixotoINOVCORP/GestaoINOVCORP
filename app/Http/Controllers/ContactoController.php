<?php

namespace App\Http\Controllers;

use App\Models\Contacto;
use App\Models\ContactoFuncao;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactoController extends Controller
{
    public function index()
    {
        $contactos = Contacto::with(['entidade', 'funcao'])
            ->orderBy('nome')
            ->paginate(20);

        return Inertia::render('contactos/Index', [
            'contactos' => $contactos,
        ]);
    }

    public function create()
    {
        return Inertia::render('contactos/Form', [
            'entidades' => Entidade::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'nif']),
            'funcoes'   => ContactoFuncao::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entidade_id' => 'required|exists:entidades,id',
            'nome'        => 'required|string|max:255',
            'apelido'     => 'nullable|string|max:255',
            'funcao_id'   => 'nullable|exists:contactos_funcoes,id',
            'telefone'    => 'nullable|digits:9',
            'telemovel'   => 'nullable|digits:9',
            'email'       => 'nullable|email|max:255',
            'rgpd'        => 'boolean',
            'observacoes' => 'nullable|string',
            'ativo'       => 'boolean',
        ]);

        Contacto::create($validated);

        return redirect()->route('contactos.index')
            ->with('success', 'Contacto criado com sucesso.');
    }

    public function edit(Contacto $contacto)
    {
        return Inertia::render('contactos/Form', [
            'contacto'  => $contacto->load(['entidade', 'funcao']),
            'entidades' => Entidade::where('ativo', true)->orderBy('nome')->get(['id', 'nome', 'nif']),
            'funcoes'   => ContactoFuncao::where('ativo', true)->orderBy('nome')->get(),
        ]);
    }

    public function update(Request $request, Contacto $contacto)
    {
        $validated = $request->validate([
            'entidade_id' => 'required|exists:entidades,id',
            'nome'        => 'required|string|max:255',
            'apelido'     => 'nullable|string|max:255',
            'funcao_id'   => 'nullable|exists:contactos_funcoes,id',
            'telefone'    => 'nullable|digits:9',
            'telemovel'   => 'nullable|digits:9',
            'email'       => 'nullable|email|max:255',
            'rgpd'        => 'boolean',
            'observacoes' => 'nullable|string',
            'ativo'       => 'boolean',
        ]);

        $contacto->update($validated);

        return redirect()->route('contactos.index')
            ->with('success', 'Contacto atualizado com sucesso.');
    }

    public function destroy(Contacto $contacto)
    {
        $contacto->delete();

        return back()->with('success', 'Contacto removido com sucesso.');
    }
}