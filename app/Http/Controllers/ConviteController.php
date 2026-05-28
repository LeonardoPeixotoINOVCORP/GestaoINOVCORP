<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\PlanLimitService;

class ConviteController extends Controller
{
    public function store(Request $request)
    {

        $tenant  = auth()->user()->currentTenant();
        $service = new PlanLimitService($tenant);

        if (!$service->podeAdicionarUtilizador()) {
            return back()->with('erro', $service->erroLimite('utilizador'));
        }

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role'  => 'nullable|exists:roles,name',
        ]);

        $tenant = auth()->user()->currentTenant();
        $plan   = $tenant->plan;

        // Verifica limite do plano
        if ($plan && $tenant->users()->count() >= $plan->max_utilizadores) {
            return back()->with('erro', "O teu plano permite no máximo {$plan->max_utilizadores} utilizadores.");
        }

        // Cria utilizador com password temporária
        $passwordTemp = Str::random(12);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($passwordTemp),
        ]);

        // Associa ao tenant
        $tenant->users()->attach($user->id, [
            'role'  => $request->role ?? 'member',
            'ativo' => true,
        ]);

        // Atribui role de permissões
        if ($request->role) {
            $user->assignRole($request->role);
        }

        // Envia email com credenciais
        \Mail::send([], [], function ($message) use ($user, $passwordTemp, $tenant) {
            $message->to($user->email)
                ->subject("Convite para {$tenant->nome}")
                ->text(
                    "Olá {$user->name},\n\n" .
                    "Foste convidado para aceder à plataforma {$tenant->nome}.\n\n" .
                    "Email: {$user->email}\n" .
                    "Password temporária: {$passwordTemp}\n\n" .
                    "Acede em: " . config('app.url') . "\n\n" .
                    "Altera a tua password após o primeiro login.\n\n" .
                    "Cumprimentos"
                );
        });

        return back()->with('sucesso', "Convite enviado para {$user->email}.");
    }

    public function destroy(User $user)
    {
        $tenant = auth()->user()->currentTenant();

        if ($user->id === auth()->id()) {
            return back()->with('erro', 'Não podes remover-te a ti próprio.');
        }

        $tenant->users()->detach($user->id);

        return back()->with('sucesso', 'Utilizador removido do tenant.');
    }
}