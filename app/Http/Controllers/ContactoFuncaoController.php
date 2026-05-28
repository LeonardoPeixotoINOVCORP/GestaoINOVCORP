<?php

namespace App\Http\Controllers;

use App\Models\ContactoFuncao;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactoFuncaoController extends Controller
{
    public function index()
    {
        return Inertia::render('configuracoes/contactos-funcoes/Index', [
            'funcoes' => ContactoFuncao::orderBy('nome')->paginate(20),
        ]);
    }

    public function create()
    {
        return Inertia::render('configuracoes/contactos-funcoes/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'  => 'required|string|max:255',
            'ativo' => 'boolean',
        ]);

        ContactoFuncao::create($request->all());

        return redirect()->route('configuracoes.contactos-funcoes.index')
            ->with('success', 'Função criada com sucesso.');
    }

    public function edit(ContactoFuncao $contactoFuncao)
    {
        return Inertia::render('configuracoes/contactos-funcoes/Form', [
            'funcao' => $contactoFuncao,
        ]);
    }

    public function update(Request $request, ContactoFuncao $contactoFuncao)
    {
        $request->validate([
            'nome'  => 'required|string|max:255',
            'ativo' => 'boolean',
        ]);

        $contactoFuncao->update($request->all());

        return redirect()->route('configuracoes.contactos-funcoes.index')
            ->with('success', 'Função atualizada com sucesso.');
    }

    public function destroy(ContactoFuncao $contactoFuncao)
    {
        $contactoFuncao->delete();

        return back()->with('success', 'Função removida com sucesso.');
    }
}