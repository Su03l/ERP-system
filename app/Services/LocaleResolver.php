<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class LocaleResolver
{
    /**
     * @return array<int, string>
     */
    public function supportedLocales(): array
    {
        return config('app.supported_locales', ['ar', 'en']);
    }

    public function fallbackLocale(): string
    {
        return 'ar';
    }

    public function sanitize(?string $locale): string
    {
        if ($locale !== null && in_array($locale, $this->supportedLocales(), true)) {
            return $locale;
        }

        return $this->fallbackLocale();
    }

    public function resolveForRequest(Request $request): string
    {
        $user = $request->user();

        if ($user instanceof User && $user->preferred_locale !== null) {
            return $this->sanitize($user->preferred_locale);
        }

        if ($user instanceof User && $user->company?->locale !== null) {
            return $this->sanitize($user->company->locale);
        }

        return $this->sanitize($request->getPreferredLanguage($this->supportedLocales()));
    }
}
