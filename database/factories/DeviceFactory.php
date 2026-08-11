<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'browser' => fake()->randomElement(['Chrome', 'Safari', 'Firefox']),
            'ip_address' => fake()->ipv4(),
            'label' => fake()->randomElement(['Executive MacBook', 'Product Demo iPhone', 'YubiKey 5 NFC']),
            'last_used_at' => now(),
            'platform' => fake()->randomElement(['macOS', 'iOS', 'Android']),
            'registered_at' => now()->subDay(),
            'type' => fake()->randomElement(['platform', 'phone', 'hardware-key']),
            'user_agent' => fake()->userAgent(),
            'user_id' => User::factory(),
        ];
    }
}
