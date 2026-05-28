<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class OnboardingController extends Controller
{

    public function index()
    {
        $tenant = auth()->user()->currentTenant();
        $step   = session('onboarding_step', 1);

        return Inertia::render('onboarding/Wizard', [
            'tenant'    => $tenant,
            'step'      => $step,
            'checklist' => $tenant->checklistCompleta(),
            'percentagem' => $tenant->percentagemChecklist(),
            'grupos'    => \Spatie\Permission\Models\Role::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, int $step)
    {
        $tenant = auth()->user()->currentTenant();

        if ($step === 1) {
            $validated = $request->validate([
                'nome'     => 'required|string|max:100',
                'email'    => 'nullable|email',
                'telefone' => 'nullable|string|max:20',
                'website'  => 'nullable|string|max:255',
            ]);
            $tenant->update($validated);
        }

        if ($step === 2) {
            $validated = $request->validate([
                'nif'           => 'nullable|string|max:20',
                'morada'        => 'nullable|string|max:255',
                'codigo_postal' => 'nullable|string|max:20',
                'localidade'    => 'nullable|string|max:100',
            ]);
            $tenant->update($validated);
        }

        if ($step === 3) {
            $request->validate([
                'logotipo' => 'nullable|image|max:2048',
            ]);

            if ($request->hasFile('logotipo')) {
                $path = $request->file('logotipo')
                    ->store('tenants/' . $tenant->id . '/branding', 'public');
                $tenant->update(['logotipo' => $path]);
            }
        }

        session(['onboarding_step' => $step + 1]);

        return redirect()->route('onboarding.wizard');
    }

    public function complete()
    {
        // \Log::info('tenant_id sessão: ' . session('tenant_id'));
        // \Log::info('user id: ' . auth()->id());

        $tenant = auth()->user()->tenants()
            ->where('tenants.id', session('tenant_id'))
            ->first();

        // \Log::info('tenant encontrado: ' . ($tenant?->id ?? 'null'));

        $tenant->update(['onboarding_completo' => true]);

        // \Log::info('onboarding marcado como completo para tenant_id: ' . $tenant->id);
        // \Log::info('tenant onboarding_completo: ' . ($tenant->onboarding_completo ? 'true' : 'false'));

        session()->forget('onboarding_step');
        return redirect()->route('dashboard');
    }

    public function back()
    {
        $step = session('onboarding_step', 1);

        if ($step > 1) {
            session(['onboarding_step' => $step - 1]);
        }

        return redirect()->route('onboarding.wizard');
    }
}