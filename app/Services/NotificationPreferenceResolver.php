<?php

namespace App\Services;

use App\Models\User;

class NotificationPreferenceResolver
{
    /**
     * @return array<int, string>
     */
    public function channelsFor(User $user, string $notificationType = 'default'): array
    {
        $preferences = $user->company?->settings['notification_preferences'] ?? [];

        $channels = [];

        if (($preferences['database_enabled'] ?? true) === true) {
            $channels[] = 'database';
        }

        if (($preferences['email_enabled'] ?? true) === true) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * @return array<int, string>
     */
    public function futureChannelsFor(User $user): array
    {
        $preferences = $user->company?->settings['notification_preferences'] ?? [];

        $channels = [];

        if (($preferences['sms_enabled'] ?? false) === true) {
            $channels[] = 'sms';
        }

        if (($preferences['whatsapp_enabled'] ?? false) === true) {
            $channels[] = 'whatsapp';
        }

        return $channels;
    }
}
