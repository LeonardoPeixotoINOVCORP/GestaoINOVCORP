<?php

namespace App\Http\Controllers;

use App\Models\CalendarioAcao;
use App\Models\CalendarioEvento;
use App\Models\CalendarioTipo;
use App\Models\Entidade;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CalendarioController extends Controller
{
    public function index()
    {
        return Inertia::render('calendario/Index', [
            'tipos'      => CalendarioTipo::where('ativo', true)->get(),
            'acoes'      => CalendarioAcao::where('ativo', true)->get(),
            'entidades'  => Entidade::where('ativo', true)->orderBy('nome')->get(['id', 'nome']),
            'utilizadores' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function eventos(Request $request)
    {
        $eventos = CalendarioEvento::with(['entidade', 'tipo', 'acao', 'user'])
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->entidade_id, fn($q) => $q->where('entidade_id', $request->entidade_id))
            ->get()
            ->map(fn($e) => [
                'id'          => $e->id,
                'title'       => $e->titulo,
                'start'       => $e->inicio,
                'end'         => $e->fim,
                'color'       => $e->tipo?->cor ?? '#3b82f6',
                'extendedProps' => [
                    'descricao'  => $e->descricao,
                    'estado'     => $e->estado,
                    'entidade'   => $e->entidade?->nome,
                    'tipo'       => $e->tipo?->nome,
                    'acao'       => $e->acao?->nome,
                    'utilizador' => $e->user?->name,
                    'partilhado' => $e->partilhado,
                ],
            ]);

        return response()->json($eventos);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'titulo'      => 'required|string|max:255',
            'inicio'      => 'required|date',
            'fim'         => 'nullable|date',
            'duracao'     => 'nullable|integer',
            'entidade_id' => 'nullable|exists:entidades,id',
            'tipo_id'     => 'nullable|exists:calendario_tipos,id',
            'acao_id'     => 'nullable|exists:calendario_acoes,id',
            'partilhado'  => 'boolean',
            'descricao'   => 'nullable|string',
            'estado'      => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();

        CalendarioEvento::create($validated);

        return back()->with('success', 'Evento criado com sucesso.');
    }

    public function update(Request $request, CalendarioEvento $evento)
    {
        $validated = $request->validate([
            'titulo'      => 'required|string|max:255',
            'inicio'      => 'required|date',
            'fim'         => 'nullable|date',
            'duracao'     => 'nullable|integer',
            'entidade_id' => 'nullable|exists:entidades,id',
            'tipo_id'     => 'nullable|exists:calendario_tipos,id',
            'acao_id'     => 'nullable|exists:calendario_acoes,id',
            'partilhado'  => 'boolean',
            'descricao'   => 'nullable|string',
            'estado'      => 'nullable|string',
        ]);

        $evento->update($validated);

        return back()->with('success', 'Evento atualizado com sucesso.');
    }

    public function destroy(CalendarioEvento $evento)
    {
        $evento->delete();

        return back()->with('success', 'Evento removido com sucesso.');
    }
}