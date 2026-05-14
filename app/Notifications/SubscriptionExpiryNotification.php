<?php

namespace App\Notifications;

use App\Models\CompanySubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubscriptionExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly CompanySubscription $subscription,
        public readonly string $event,
    ) {
        $this->afterCommit();
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_expiry',
            'event' => $this->event,
            'company_id' => $this->subscription->company_id,
            'subscription_id' => $this->subscription->id,
            'plan_id' => $this->subscription->plan_id,
            'status' => $this->subscription->status?->value ?? $this->subscription->status,
            'trial_ends_at' => $this->subscription->trial_ends_at?->toDateString(),
            'ends_at' => $this->subscription->ends_at?->toDateString(),
            'grace_ends_at' => $this->subscription->grace_ends_at?->toDateString(),
            'message' => __("saas.subscription_expiry_notifications.{$this->event}"),
            'reminder_date' => now()->toDateString(),
        ];
    }
}
