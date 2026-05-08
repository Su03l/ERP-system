<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

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

    public function direction(?string $locale = null): string
    {
        return $this->sanitize($locale ?? App::currentLocale()) === 'ar' ? 'rtl' : 'ltr';
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

        if ($request->hasSession()) {
            $sessionLocale = $request->session()->get('locale');

            if (is_string($sessionLocale) && $sessionLocale !== '') {
                return $this->sanitize($sessionLocale);
            }
        }

        $requestLocale = $request->query('locale', $request->header('X-Locale'));

        if (is_string($requestLocale) && $requestLocale !== '') {
            return $this->sanitize($requestLocale);
        }

        return $this->fallbackLocale();
    }
}
