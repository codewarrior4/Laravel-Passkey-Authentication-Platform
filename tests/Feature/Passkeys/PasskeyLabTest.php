<?php

namespace Tests\Feature\Passkeys;

use Tests\TestCase;

class PasskeyLabTest extends TestCase
{
    public function test_the_passkey_lab_route_is_available(): void
    {
        $response = $this->get(route('passkeys.lab'));

        $response
            ->assertOk()
            ->assertSeeText('Passkey lab for real browser registration, login, and account checks.')
            ->assertSeeText('Ready to test')
            ->assertSeeText('Registration checks');
    }
}
