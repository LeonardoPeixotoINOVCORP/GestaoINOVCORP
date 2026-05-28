<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TrialEndingNotification extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        private Tenant $tenant,
        private int $diasRestantes
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("O teu trial termina em {$this->diasRestantes} dias — {$this->tenant->nome}")
            ->greeting("Olá {$notifiable->name},")
            ->line("O período de trial da empresa **{$this->tenant->nome}** termina em **{$this->diasRestantes} dias**.")
            ->line('Para continuar a utilizar a plataforma sem interrupções, subscreve um plano.')
            ->action('Escolher Plano', url('/billing/planos'))
            ->line('Se tiveres alguma dúvida, responde a este email.')
            ->salutation('Cumprimentos, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'tenant_id'       => $this->tenant->id,
            'tenant_nome'     => $this->tenant->nome,
            'dias_restantes'  => $this->diasRestantes,
            'trial_ends_at'   => $this->tenant->trial_ends_at,
            'tipo'            => 'trial_ending',
        ];
    }
}