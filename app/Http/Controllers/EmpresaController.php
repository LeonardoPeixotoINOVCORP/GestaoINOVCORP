<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EmpresaController extends Controller
{
    public function edit()
    {
        $tenant = Tenant::findOrFail(session('tenant_id'));

        return Inertia::render('configuracoes/empresa/Form', [
            'empresa' => $tenant,
        ]);
    }

    public function update(Request $request)
    {
        $tenant = Tenant::findOrFail(session('tenant_id'));

        $validated = $request->validate([
            'nome'          => 'required|string|max:255',
            'morada'        => 'nullable|string|max:255',
            'codigo_postal' => 'nullable|string|regex:/^\d{4}-\d{3}$/',
            'localidade'    => 'nullable|string|max:255',
            'nif'           => 'nullable|string|max:20',
            'telefone'      => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:255',
            'website'       => 'nullable|url|max:255',
            'logotipo'      => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logotipo')) {

            if ($tenant->logotipo) {
                Storage::disk('public')->delete($tenant->logotipo);
            }

            $validated['logotipo'] = $request
                ->file('logotipo')
                ->store('empresa', 'public');
        }

        $tenant->update($validated);

        return redirect()
            ->route('empresa.edit')
            ->with('success', 'Dados da empresa atualizados com sucesso.');
    }
}