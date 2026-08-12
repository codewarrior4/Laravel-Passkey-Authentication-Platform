<?php

namespace Tests\Feature\Passkeys;

use App\Authentication\Passkeys\Support\Base64Url;
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
            ->assertSeeText('Register passkey in browser');

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

    public function test_the_registration_start_endpoint_returns_creation_options_and_persists_a_draft(): void
    {
        $response = $this->postJson(route('passkeys.register.start'), [
            'full_name' => 'Arielle Stone',
            'work_email' => 'arielle@onely.app',
            'device_name' => 'Executive MacBook',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'passkey_id',
                'public_key' => [
                    'attestation',
                    'challenge',
                    'pubKeyCredParams',
                    'rp',
                    'timeout',
                    'user',
                ],
            ]);

        $user = User::query()->where('email', 'arielle@onely.app')->firstOrFail();
        $passkey = Passkey::query()->where('user_id', $user->id)->latest('id')->firstOrFail();

        $this->assertSame('pending', $passkey->status);
        $this->assertNotNull($passkey->current_challenge);
        $this->assertNotNull($passkey->challenge_expires_at);
    }

    public function test_the_registration_finish_endpoint_activates_the_passkey(): void
    {
        $startResponse = $this->postJson(route('passkeys.register.start'), [
            'full_name' => 'Arielle Stone',
            'work_email' => 'arielle@onely.app',
            'device_name' => 'Executive MacBook',
        ]);

        $startPayload = $startResponse->json();
        $challenge = $startPayload['public_key']['challenge'];
        $passkeyId = $startPayload['passkey_id'];

        $rpIdHash = hash('sha256', config('passkeys.relying_party.id'), true);
        $authenticatorData = $rpIdHash.chr(0x01).pack('N', 0);

        $response = $this->postJson(route('passkeys.register.finish'), [
            'authenticator_data' => Base64Url::encode($authenticatorData),
            'client_data_json' => Base64Url::encode(json_encode([
                'challenge' => $challenge,
                'origin' => config('app.url'),
                'type' => 'webauthn.create',
            ], JSON_THROW_ON_ERROR)),
            'credential_id' => Base64Url::encode('credential-123'),
            'origin' => config('app.url'),
            'passkey_id' => $passkeyId,
            'public_key' => Base64Url::encode('public-key-material'),
            'public_key_algorithm' => -7,
            'transports' => ['internal'],
        ]);

        $response
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Passkey registration completed for Executive MacBook.',
            ]);

        $this->assertDatabaseHas('passkeys', [
            'credential_id_hash' => hash('sha256', Base64Url::encode('credential-123')),
            'id' => $passkeyId,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('authentication_events', [
            'event' => 'passkey.registration.completed',
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
