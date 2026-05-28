<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Notifications\TrialEndingNotification;
use Illuminate\Console\Command;

class EnviarNotificacoesTrialCommand extends Command
{
    protected $signature   = 'trial:notificar';
    protected $description = 'Envia notificações de fim de trial aos utilizadores';

    public function handle(): void
    {
        // Notifica 7 dias antes
        $this->notificarEm(7);

        // Notifica 3 dias antes
        $this->notificarEm(3);

        // Notifica 1 dia antes
        $this->notificarEm(1);

        $this->info('Notificações de trial enviadas.');
    }

    private function notificarEm(int $dias): void
    {
        $tenants = Tenant::where('subscription_status', 'trial')
            ->whereDate('trial_ends_at', now()->addDays($dias)->toDateString())
            ->get();

        foreach ($tenants as $tenant) {
            $owner = \App\Models\User::find($tenant->owner_id);

            if (!$owner) {
                continue;
            }

            $jaNotificado = $owner->notifications()
                ->where('type', TrialEndingNotification::class)
                ->whereDate('created_at', today())
                ->whereJsonContains('data->dias_restantes', $dias)
                ->exists();

            if (!$jaNotificado) {
                $owner->notify(new TrialEndingNotification($tenant, $dias));
                $this->line("Notificado: {$owner->email} — {$tenant->nome} ({$dias} dias)");
            }
        }
    }
}