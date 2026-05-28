<?php

namespace App\Http\Controllers;

use App\Models\ArquivoDigital;
use App\Models\Entidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ArquivoDigitalController extends Controller
{
    public function index()
    {
        $ficheiros = ArquivoDigital::with(['entidade', 'user'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('arquivo/Index', [
            'ficheiros' => $ficheiros,
            'entidades' => Entidade::orderBy('nome')->get(['id', 'nome']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome'        => 'required|string|max:255',
            'ficheiro'    => 'required|file|max:20480',
            'entidade_id' => 'nullable|exists:entidades,id',
            'observacoes' => 'nullable|string',
        ]);

        $file = $request->file('ficheiro');
        $path = $file->store('arquivo', 'local');

        ArquivoDigital::create([
            'nome'        => $request->nome,
            'ficheiro'    => $path,
            'tipo_mime'   => $file->getMimeType(),
            'tamanho'     => $file->getSize(),
            'entidade_id' => $request->entidade_id,
            'observacoes' => $request->observacoes,
            'user_id'     => auth()->id(),
        ]);

        return back()->with('success', 'Ficheiro carregado com sucesso.');
    }

    public function download(ArquivoDigital $arquivoDigital)
    {
        if (!Storage::disk('local')->exists($arquivoDigital->ficheiro)) {
            abort(404);
        }

        return Storage::download(
            $arquivoDigital->ficheiro,
            $arquivoDigital->nome
        );
    }

    public function destroy(ArquivoDigital $arquivoDigital)
    {
        Storage::disk('local')->delete($arquivoDigital->ficheiro);
        $arquivoDigital->delete();

        return back()->with('success', 'Ficheiro removido com sucesso.');
    }
}