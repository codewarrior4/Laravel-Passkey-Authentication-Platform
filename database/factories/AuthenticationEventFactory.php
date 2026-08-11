<?php

namespace Database\Factories;

use App\Models\AuthenticationEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuthenticationEvent>
 */
class AuthenticationEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event' => fake()->randomElement([
                'passkey.registration.requested',
                'passkey.authentication.requested',
                'passkey.preview.login.succeeded',
            ]),
            'ip_address' => fake()->ipv4(),
            'metadata' => ['source' => 'factory'],
            'occurred_at' => now(),
            'risk_level' => fake()->randomElement(['info', 'low', 'medium']),
            'user_agent' => fake()->userAgent(),
            'user_id' => User::factory(),
        ];
    }
}
