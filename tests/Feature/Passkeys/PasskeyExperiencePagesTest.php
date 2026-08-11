<?php

namespace Tests\Feature\Passkeys;

use App\Models\AuthenticationEvent;
use App\Models\Device;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasskeyExperiencePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_showcase_pages_are_available(): void
    {
        $this->get(route('passkeys.overview'))
            ->assertOk()
            ->assertSeeText('Passkey Experience Suite')
            ->assertSeeText('Walk the audience through the entire trust journey.');

        $this->get(route('passkeys.register'))
            ->assertOk()
            ->assertSeeText('Create a passkey')
            ->assertSeeText('Create registration draft');

        $this->get(route('passkeys.login'))
            ->assertOk()
            ->assertSeeText('Use your passkey to continue')
            ->assertSeeText('Preview sign-in');

        $this->get(route('passkeys.dashboard'))
            ->assertOk()
            ->assertSeeText('Control every credential anchor.')
            ->assertSeeText('Active sessions');
    }

    public function test_the_registration_preview_redirects_back_with_feedback(): void
    {
        $response = $this->post(route('passkeys.register.preview'), [
            'full_name' => 'Arielle Stone',
            'work_email' => 'arielle@onely.app',
            'device_name' => 'Executive MacBook',
        ]);

        $response
            ->assertRedirect(route('passkeys.register'))
            ->assertSessionHas('status');

        $user = User::query()->where('email', 'arielle@onely.app')->first();

        $this->assertNotNull($user);
        $this->assertDatabaseHas('devices', [
            'label' => 'Executive MacBook',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('passkeys', [
            'label' => 'Executive MacBook',
            'status' => 'pending',
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('authentication_events', [
            'event' => 'passkey.registration.requested',
            'user_id' => $user->id,
        ]);
    }

    public function test_the_login_preview_redirects_to_the_dashboard_with_feedback(): void
    {
        $user = User::factory()->create([
            'email' => 'arielle@onely.app',
            'name' => 'Arielle Stone',
        ]);

        Device::factory()->create([
            'label' => 'Executive MacBook',
            'user_id' => $user->id,
        ]);

        Passkey::factory()->create([
            'device_id' => $user->devices()->first()?->id,
            'label' => 'Executive MacBook',
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $response = $this->post(route('passkeys.login.preview'), [
            'work_email' => 'arielle@onely.app',
            'device_choice' => 'Executive MacBook',
        ]);

        $response
            ->assertRedirect(route('passkeys.dashboard'))
            ->assertSessionHas('status');

        $this->assertGreaterThanOrEqual(2, AuthenticationEvent::query()->count());
        $this->assertDatabaseHas('authentication_events', [
            'event' => 'passkey.preview.login.succeeded',
            'user_id' => $user->id,
        ]);
    }
}
