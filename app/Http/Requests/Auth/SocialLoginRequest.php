<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SocialLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Allow all users to use this request
    }

    public function rules(): array
    {
        return [
            'provider' => 'required|string|in:google,apple',
            'access_token' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'provider.required' => __('validation.required', ['attribute' => 'provider']),
            'provider.in' => __('validation.in', ['attribute' => 'provider']),
            'access_token.required' => __('validation.required', ['attribute' => 'access token']),
        ];
    }
}
