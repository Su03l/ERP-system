<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<UserSession> */
class UserSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'session_id' => hash('sha256', Str::random(40)),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'last_activity_at' => now(),
            'revoked_at' => null,
            'metadata' => null,
        ];
    }
}
