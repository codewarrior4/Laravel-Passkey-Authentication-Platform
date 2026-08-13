<?php

namespace Tests\Feature\Passkeys;

use App\Authentication\Passkeys\Support\Base64Url;
use App\Models\AuthenticationEvent;
use App\Models\Device;
use App\Models\Passkey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
            ->assertSeeText('Sign in with passkey');

        $this->get(route('passkeys.dashboard'))
            ->assertRedirect(route('login'));

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('passkeys.dashboard'))
            ->assertOk()
            ->assertSeeText('Remove broken or outdated credentials.')
            ->assertSeeText('Active sessions');
    }

    public function test_public_authentication_pages_do_not_show_account_or_session_panels(): void
    {
        $this->get(route('passkeys.register'))
            ->assertOk()
            ->assertDontSeeText('Registered devices')
            ->assertDontSeeText('Accounts');

        $this->get(route('passkeys.dashboard'))
            ->assertRedirect(route('login'));

        $this->get(route('passkeys.login'))
            ->assertOk()
            ->assertDontSeeText('Accounts')
            ->assertDontSeeText('Relying Party')
            ->assertDontSeeText('Release controls')
            ->assertDontSeeText('Work email')
            ->assertDontSeeText('Active passkeys');
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
                    'authenticatorSelection',
                    'challenge',
                    'hints',
                    'pubKeyCredParams',
                    'rp',
                    'timeout',
                    'user',
                ],
            ])
            ->assertJsonPath('public_key.hints.0', 'client-device')
            ->assertJsonPath('public_key.authenticatorSelection.authenticatorAttachment', 'platform')
            ->assertJsonPath('public_key.authenticatorSelection.requireResidentKey', true)
            ->assertJsonPath('public_key.authenticatorSelection.residentKey', 'required')
            ->assertJsonPath('public_key.authenticatorSelection.userVerification', 'required');

        $user = User::query()->where('email', 'arielle@onely.app')->firstOrFail();
        $passkey = Passkey::query()->where('user_id', $user->id)->latest('id')->firstOrFail();

        $this->assertSame('pending', $passkey->status);
        $this->assertNotNull($passkey->current_challenge);
        $this->assertNotNull($passkey->challenge_expires_at);
    }

    public function test_the_registration_start_endpoint_keeps_a_domain_rp_id_when_opened_from_an_ip_host(): void
    {
        $response = $this
            ->withServerVariables([
                'HTTP_HOST' => '127.0.0.1:8000',
            ])
            ->postJson(route('passkeys.register.start'), [
                'full_name' => 'Arielle Stone',
                'work_email' => 'arielle@onely.app',
                'device_name' => 'Executive MacBook',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('public_key.rp.id', config('passkeys.relying_party.id'));
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

        $this->assertAuthenticatedAs($user);

        $this->assertGreaterThanOrEqual(2, AuthenticationEvent::query()->count());
        $this->assertDatabaseHas('authentication_events', [
            'event' => 'passkey.preview.login.succeeded',
            'user_id' => $user->id,
        ]);
    }

    public function test_the_authentication_start_endpoint_returns_request_options(): void
    {
        $user = User::factory()->create([
            'email' => 'arielle@onely.app',
            'name' => 'Arielle Stone',
            'password' => Hash::make('password'),
        ]);

        $device = Device::factory()->create([
            'label' => 'Executive MacBook',
            'user_id' => $user->id,
        ]);

        Passkey::factory()->create([
            'challenge_expires_at' => null,
            'credential_id' => Base64Url::encode('credential-123'),
            'credential_id_hash' => hash('sha256', Base64Url::encode('credential-123')),
            'current_challenge' => null,
            'device_id' => $device->id,
            'public_key' => Base64Url::encode('fake-public-key'),
            'status' => 'active',
            'transports' => ['internal'],
            'user_id' => $user->id,
        ]);

        $response = $this->postJson(route('passkeys.login.start'));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'passkey_id',
                'public_key' => [
                    'allowCredentials',
                    'challenge',
                    'hints',
                    'rpId',
                    'timeout',
                    'userVerification',
                ],
            ])
            ->assertJsonPath('public_key.hints.0', 'client-device')
            ->assertJsonPath('public_key.userVerification', 'required');

        $this->assertIsString(session('passkey_authentication_challenge'));
    }

    public function test_the_authentication_start_endpoint_does_not_require_an_email_or_registered_account(): void
    {
        $response = $this->postJson(route('passkeys.login.start'));

        $response
            ->assertOk()
            ->assertJsonPath('public_key.allowCredentials', []);
    }

    public function test_the_authentication_finish_endpoint_logs_the_user_in_and_rotates_the_counter(): void
    {
        $keyResource = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $privateKey = '';
        openssl_pkey_export($keyResource, $privateKey);
        $publicKeyDetails = openssl_pkey_get_details($keyResource);
        $publicKeyDer = $this->publicKeyDerFromPem($publicKeyDetails['key']);

        $user = User::factory()->create([
            'email' => 'arielle@onely.app',
            'name' => 'Arielle Stone',
        ]);

        $device = Device::factory()->create([
            'label' => 'Executive MacBook',
            'user_id' => $user->id,
        ]);

        $passkey = Passkey::factory()->create([
            'counter' => 3,
            'credential_id' => Base64Url::encode('credential-123'),
            'credential_id_hash' => hash('sha256', Base64Url::encode('credential-123')),
            'device_id' => $device->id,
            'public_key' => Base64Url::encode($publicKeyDer),
            'status' => 'active',
            'transports' => ['internal'],
            'user_id' => $user->id,
        ]);

        $startResponse = $this->postJson(route('passkeys.login.start'));
        $startPayload = $startResponse->json();
        $startChallenge = $startPayload['public_key']['challenge'];

        $authenticatorData = hash('sha256', config('passkeys.relying_party.id'), true).chr(0x01).pack('N', 4);
        $clientDataJson = json_encode([
            'challenge' => $startChallenge,
            'origin' => config('app.url'),
            'type' => 'webauthn.get',
        ], JSON_THROW_ON_ERROR);

        $signatureBase = $authenticatorData.hash('sha256', $clientDataJson, true);
        openssl_sign($signatureBase, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $response = $this->postJson(route('passkeys.login.finish'), [
            'authenticator_data' => Base64Url::encode($authenticatorData),
            'client_data_json' => Base64Url::encode($clientDataJson),
            'credential_id' => Base64Url::encode('credential-123'),
            'origin' => config('app.url'),
            'signature' => Base64Url::encode($signature),
        ]);

        $response
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Passkey login completed for arielle@onely.app.',
            ]);

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('passkeys', [
            'id' => $passkey->id,
            'counter' => 4,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('authentication_events', [
            'event' => 'passkey.authentication.completed',
            'user_id' => $user->id,
        ]);
    }

    public function test_an_authenticated_user_can_revoke_their_passkey_from_the_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'arielle@onely.app',
            'name' => 'Arielle Stone',
        ]);

        $device = Device::factory()->create([
            'label' => 'Executive MacBook',
            'user_id' => $user->id,
        ]);

        $passkey = Passkey::factory()->create([
            'device_id' => $device->id,
            'label' => 'Executive MacBook',
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['passkey_last_authenticated_at' => now()->toIso8601String()])
            ->post(route('passkeys.revoke', $passkey));

        $response
            ->assertRedirect(route('passkeys.dashboard'))
            ->assertSessionHas('status', 'Passkey removed from this account.');

        $this->assertDatabaseHas('passkeys', [
            'id' => $passkey->id,
            'status' => 'revoked',
        ]);

        $this->assertNotNull($passkey->fresh()?->revoked_at);
        $this->assertDatabaseHas('authentication_events', [
            'event' => 'passkey.revoked',
            'passkey_id' => $passkey->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_an_authenticated_user_can_rename_and_revoke_a_device_from_the_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'arielle@onely.app',
            'name' => 'Arielle Stone',
        ]);

        $device = Device::factory()->create([
            'label' => 'Executive MacBook',
            'user_id' => $user->id,
        ]);

        Passkey::factory()->create([
            'device_id' => $device->id,
            'label' => 'Executive MacBook',
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $renameResponse = $this->actingAs($user)
            ->withSession(['passkey_last_authenticated_at' => now()->toIso8601String()])
            ->post(route('passkeys.devices.rename', $device), [
                'label' => 'Office MacBook',
            ]);

        $renameResponse
            ->assertRedirect(route('passkeys.dashboard'))
            ->assertSessionHas('status', 'Device renamed successfully.');

        $this->assertDatabaseHas('devices', [
            'id' => $device->id,
            'label' => 'Office MacBook',
        ]);

        $revokeResponse = $this->actingAs($user)
            ->withSession(['passkey_last_authenticated_at' => now()->toIso8601String()])
            ->post(route('passkeys.devices.revoke', $device));

        $revokeResponse
            ->assertRedirect(route('passkeys.dashboard'))
            ->assertSessionHas('status', 'Device revoked and linked passkeys disabled.');

        $this->assertNotNull($device->fresh()?->revoked_at);
        $this->assertDatabaseHas('authentication_events', [
            'event' => 'device.revoked',
            'device_id' => $device->id,
            'user_id' => $user->id,
        ]);
        $this->assertDatabaseHas('passkeys', [
            'device_id' => $device->id,
            'status' => 'revoked',
        ]);
    }

    public function test_the_authentication_finish_endpoint_allows_zero_counters_for_supported_passkeys(): void
    {
        $keyResource = openssl_pkey_new([
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $privateKey = '';
        openssl_pkey_export($keyResource, $privateKey);
        $publicKeyDetails = openssl_pkey_get_details($keyResource);
        $publicKeyDer = $this->publicKeyDerFromPem($publicKeyDetails['key']);

        $user = User::factory()->create([
            'email' => 'arielle@onely.app',
            'name' => 'Arielle Stone',
        ]);

        $device = Device::factory()->create([
            'label' => 'Executive MacBook',
            'user_id' => $user->id,
        ]);

        $passkey = Passkey::factory()->create([
            'counter' => 0,
            'credential_id' => Base64Url::encode('credential-123'),
            'credential_id_hash' => hash('sha256', Base64Url::encode('credential-123')),
            'device_id' => $device->id,
            'public_key' => Base64Url::encode($publicKeyDer),
            'status' => 'active',
            'transports' => ['internal'],
            'user_id' => $user->id,
        ]);

        $startResponse = $this->postJson(route('passkeys.login.start'));
        $startPayload = $startResponse->json();

        $authenticatorData = hash('sha256', config('passkeys.relying_party.id'), true).chr(0x01).pack('N', 0);
        $clientDataJson = json_encode([
            'challenge' => $startPayload['public_key']['challenge'],
            'origin' => config('app.url'),
            'type' => 'webauthn.get',
        ], JSON_THROW_ON_ERROR);

        $signatureBase = $authenticatorData.hash('sha256', $clientDataJson, true);
        openssl_sign($signatureBase, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        $response = $this->postJson(route('passkeys.login.finish'), [
            'authenticator_data' => Base64Url::encode($authenticatorData),
            'client_data_json' => Base64Url::encode($clientDataJson),
            'credential_id' => Base64Url::encode('credential-123'),
            'origin' => config('app.url'),
            'signature' => Base64Url::encode($signature),
        ]);

        $response
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Passkey login completed for arielle@onely.app.',
            ]);

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('passkeys', [
            'id' => $passkey->id,
            'counter' => 0,
            'status' => 'active',
        ]);
    }

    private function publicKeyDerFromPem(string $pem): string
    {
        $normalized = str_replace([
            "-----BEGIN PUBLIC KEY-----\n",
            "\n-----END PUBLIC KEY-----\n",
            "\r",
            "\n",
        ], '', $pem);

        return base64_decode($normalized, true) ?: '';
    }
}
