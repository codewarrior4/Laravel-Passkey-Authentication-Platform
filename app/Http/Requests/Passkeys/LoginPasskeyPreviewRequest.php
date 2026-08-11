<?php

namespace App\Http\Requests\Passkeys;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginPasskeyPreviewRequest extends FormRequest
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
            'device_choice' => ['required', 'string', 'max:255'],
            'work_email' => ['required', 'email'],
        ];
    }
}
