<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmPinResetRequest extends FormRequest
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
            'reset_token' => ['required', 'string', 'exists:pin_reset_requests,reset_token'],
            'new_pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'new_pin_confirmation' => ['required', 'same:new_pin'],
        ];
    }
}
