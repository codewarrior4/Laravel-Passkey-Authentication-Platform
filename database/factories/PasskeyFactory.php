<?php

namespace Database\Factories;

use App\Models\Passkey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Passkey>
 */
class PasskeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'challenge_expires_at' => now()->addMinutes(5),
            'counter' => 0,
            'credential_id_hash' => hash('sha256', Str::random(32)),
            'current_challenge' => Str::random(64),
            'label' => fake()->randomElement(['Executive MacBook', 'Product Demo iPhone']),
            'status' => fake()->randomElement(['pending', 'active']),
            'user_handle' => (string) fake()->randomNumber(5, true),
            'user_id' => User::factory(),
        ];
    }
}
