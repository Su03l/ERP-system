<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CompanyApiToken>
 */
class CompanyApiTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'token' => hash('sha256', Str::random(40)),
            'abilities' => ['analytics.view'],
            'last_used_at' => null,
            'expires_at' => null,
            'revoked_at' => null,
            'metadata' => null,
        ];
    }
}
