<?php

namespace App\Http\Controllers;

use App\Models\Encomenda;
use App\Models\Entidade;
use App\Models\FaturaFornecedor;
use App\Models\Proposta;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index()
    {
        $hoje = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();

        return Inertia::render('Dashboard', [
            // Contagens gerais
            'stats' => [
                'clientes'              => Entidade::where('is_cliente', true)->where('ativo', true)->count(),
                'fornecedores'          => Entidade::where('is_fornecedor', true)->where('ativo', true)->count(),
                'propostas_abertas'     => Proposta::where('estado', 'rascunho')->count(),
                'encomendas_abertas'    => Encomenda::where('estado', 'rascunho')->count(),
                'faturas_pendentes'     => FaturaFornecedor::where('estado', 'pendente')->count(),
                'faturas_valor_pendente' => FaturaFornecedor::where('estado', 'pendente')->sum('valor_total'),
            ],

            // Propostas recentes
            'propostas_recentes' => Proposta::with('entidade')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'numero', 'estado', 'entidade_id', 'created_at']),

            // Encomendas recentes
            'encomendas_recentes' => Encomenda::with('entidade')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'numero', 'estado', 'tipo', 'entidade_id', 'created_at']),

            // Faturas a vencer nos próximos 30 dias
            'faturas_a_vencer' => FaturaFornecedor::with('fornecedor')
                ->where('estado', 'pendente')
                ->whereBetween('data_vencimento', [$hoje, $hoje->copy()->addDays(30)])
                ->orderBy('data_vencimento')
                ->limit(5)
                ->get(['id', 'numero', 'valor_total', 'data_vencimento', 'fornecedor_id']),

            // Atividade recente
            'atividade_recente' => Activity::query()
                ->where('tenant_id', session('tenant_id'))
                ->with('causer')
                ->orderByDesc('created_at')
                ->limit(8)
                ->get([
                    'id',
                    'description',
                    'created_at',
                    'causer_id',
                    'causer_type',
                    'subject_type',
                ]),
        ]);
    }
}