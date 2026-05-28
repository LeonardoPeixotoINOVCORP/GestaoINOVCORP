<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Cashier\Exceptions\IncompletePayment;
use App\Models\Entidade;
use App\Models\Artigo;
use App\Models\PlanLog;

class BillingController extends Controller
{
    public function index(): Response
    {
        $tenant = auth()->user()->currentTenant();
        $planoAtual = $tenant->plan;

        $subscription = null;

        if ($planoAtual) {
            $subscription = [
                'name'          => $planoAtual->nome,
                'stripe_status' => $tenant->subscription_status,
                'ends_at'       => $tenant->subscription_ends_at,
                'trial_ends_at' => $tenant->trial_ends_at,
                'is_trial'      => $tenant->isOnTrial(),
                'trial_days_left' => $tenant->trialDaysLeft(),
            ];
        }

        $utilizacao = [
            'utilizadores' => [
                'atual' => $tenant->users()->count(),
                'max'   => $planoAtual?->max_utilizadores ?? 0,
            ],
            'clientes' => [
                'atual' => Entidade::where('is_cliente', true)->count(),
                'max'   => $planoAtual?->max_clientes ?? 0,
            ],
            'artigos' => [
                'atual' => Artigo::count(),
                'max'   => $planoAtual?->max_artigos ?? 0,
            ],
            'arquivo_digital' => $planoAtual?->arquivo_digital ?? false,
            'calendario'      => $planoAtual?->calendario ?? false,
            'financeiro'      => $planoAtual?->financeiro ?? false,
        ];

        return Inertia::render('billing/Index', [
            'subscription' => $subscription,
            'utilizacao'   => $utilizacao,
            'plano'        => $planoAtual,
        ]);
    }

    public function planos(): Response
    {
        $tenant = auth()->user()->currentTenant();

        $planos = Plan::where('ativo', true)
            ->orderBy('preco')
            ->get();

        return Inertia::render('billing/Planos', [
            'tenant' => $tenant,
            'planos' => $planos,
        ]);
    }

    public function subscrever(Request $request, Plan $plano)
    {
        $tenant     = auth()->user()->currentTenant();
        $planAntigo = $tenant->plan;

        if ($plano->slug === 'free') {
            $tenant->update([
                'plan_id'             => $plano->id,
                'subscription_status' => 'active',
            ]);

            PlanLog::create([
                'tenant_id'       => $tenant->id,
                'user_id'         => auth()->id(),
                'plan_anterior_id' => $planAntigo?->id,
                'plan_novo_id'    => $plano->id,
                'acao'            => $planAntigo ? 'downgrade' : 'subscribe',
            ]);

            return redirect()->route('billing.index')->with('sucesso', 'Plano atualizado.');
        }

        if (!$plano->stripe_price_id) {
            return back()->with('erro', 'Plano sem configuração Stripe.');
        }

        $request->validate(['payment_method' => 'required|string']);

        try {
            DB::beginTransaction();

            $user = auth()->user();

            if (!$user->stripe_id) {
                $user->createAsStripeCustomer();
            }

            $user->updateDefaultPaymentMethod($request->payment_method);

            if (!$user->subscribed('default')) {
                $subscription = $user->newSubscription('default', $plano->stripe_price_id)
                    ->create($request->payment_method);
            } else {
                $subscription = $user->subscription('default');
                $subscription->swapAndInvoice($plano->stripe_price_id);
            }

            // Determina ação
            $acao = 'subscribe';
            if ($planAntigo) {
                $acao = $plano->preco > $planAntigo->preco ? 'upgrade' : 'downgrade';
            }

            $tenant->update([
                'plan_id'                => $plano->id,
                'stripe_customer_id'     => $user->stripe_id,
                'stripe_subscription_id' => $subscription->stripe_id,
                'subscription_status'    => 'active',
                'subscription_ends_at'   => null,
            ]);

            PlanLog::create([
                'tenant_id'        => $tenant->id,
                'user_id'          => auth()->id(),
                'plan_anterior_id' => $planAntigo?->id,
                'plan_novo_id'     => $plano->id,
                'acao'             => $acao,
                'valor_pago'       => $plano->preco,
            ]);

            DB::commit();

            return redirect()->route('billing.index')->with('sucesso', 'Plano atualizado com sucesso.');

        } catch (IncompletePayment $exception) {
            DB::rollBack();
            return redirect()->route('cashier.payment', [
                $exception->payment->id,
                'redirect' => route('billing.index'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return back()->with('erro', 'Erro ao processar subscrição.');
        }
    }

    public function cancelar()
    {
        $tenant     = auth()->user()->currentTenant();
        $planAntigo = $tenant->plan;
        $user       = auth()->user();

        if ($user->subscribed('default')) {
            $user->subscription('default')->cancel();
        }

        $tenant->update([
            'subscription_status'  => 'canceled',
            'subscription_ends_at' => now()->addMonth(),
        ]);

        PlanLog::create([
            'tenant_id'        => $tenant->id,
            'user_id'          => auth()->id(),
            'plan_anterior_id' => $planAntigo?->id,
            'plan_novo_id'     => null,
            'acao'             => 'cancel',
            'notas'            => 'Cancelado pelo utilizador. Acesso até ' . now()->addMonth()->format('d/m/Y'),
        ]);

        return back()->with('sucesso', 'Subscrição cancelada no final do período atual.');
    }

    public function portal()
    {
        $user = auth()->user();

        if (!$user->stripe_id) {
            return redirect()->route('billing.expirado');
        }

        return $user->redirectToBillingPortal(
            route('billing.index')
        );
    }

    public function expirado(): Response
    {
        return Inertia::render('billing/Expirado');
    }

    public function logs(): Response
    {
        $tenant = auth()->user()->currentTenant();

        $logs = PlanLog::where('tenant_id', $tenant->id)
            ->with(['user', 'planAnterior', 'planNovo'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('billing/Logs', [
            'logs' => $logs,
        ]);
    }
}