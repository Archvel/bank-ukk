<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isTeller() ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_number' => ['required', 'string', 'exists:savers,account_number'],
            'amount' => ['required', 'integer', 'min:1000'],
            'pin' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
