<?php

namespace App\Http\Requests\Passkeys;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class FinishPasskeyRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'authenticator_data' => ['required', 'string'],
            'client_data_json' => ['required', 'string'],
            'credential_id' => ['required', 'string'],
            'origin' => ['required', 'url'],
            'passkey_id' => ['required', 'integer'],
            'public_key' => ['required', 'string'],
            'public_key_algorithm' => ['required', 'integer'],
            'transports' => ['nullable', 'array'],
            'transports.*' => ['string'],
        ];
    }
}
