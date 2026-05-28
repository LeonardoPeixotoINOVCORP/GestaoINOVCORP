<?php

namespace App\Http\Controllers;

use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

class StripeWebhookController extends CashierWebhookController
{
    public function handleInvoicePaymentSucceeded(array $payload): void
    {
        $customerId = $payload['data']['object']['customer'];
        $user = \App\Models\User::where('stripe_id', $customerId)->first();

        if ($user) {
            $tenant = $user->tenants()->where('stripe_customer_id', $customerId)->first();
            if ($tenant) {
                $tenant->update([
                    'subscription_status' => 'active',
                    'subscription_ends_at' => now()->addMonth(),
                ]);
            }
        }
    }

    public function handleCustomerSubscriptionDeleted(array $payload): void
    {
        $customerId = $payload['data']['object']['customer'];
        $user = \App\Models\User::where('stripe_id', $customerId)->first();

        if ($user) {
            $tenant = $user->tenants()->where('stripe_customer_id', $customerId)->first();
            if ($tenant) {
                $tenant->update(['subscription_status' => 'canceled']);
            }
        }
    }
}