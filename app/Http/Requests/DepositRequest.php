<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class DepositRequest extends FormRequest
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
            'member_id' => ['nullable', 'integer', 'exists:members,id'],
            'saver_id' => ['nullable', 'integer', 'exists:savers,id'],
            'account_number' => ['nullable', 'string', 'exists:savers,account_number'],
            'amount' => ['required', 'integer', 'min:1000'],
            'pin' => ['nullable', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }
}
