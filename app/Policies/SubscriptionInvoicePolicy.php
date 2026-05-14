<?php

namespace App\Policies;

use App\Models\SubscriptionInvoice;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class SubscriptionInvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return Gate::forUser($user)->allows('subscription_invoices.view');
    }

    public function view(User $user, SubscriptionInvoice $subscriptionInvoice): bool
    {
        return Gate::forUser($user)->allows('subscription_invoices.view');
    }

    public function generate(User $user): bool
    {
        return Gate::forUser($user)->allows('subscription_invoices.generate');
    }

    public function update(User $user, SubscriptionInvoice $subscriptionInvoice): bool
    {
        return Gate::forUser($user)->allows('subscription_invoices.generate')
            || Gate::forUser($user)->allows('subscription_invoices.mark_paid');
    }

    public function markPaid(User $user, SubscriptionInvoice $subscriptionInvoice): bool
    {
        return Gate::forUser($user)->allows('subscription_invoices.mark_paid');
    }
}
