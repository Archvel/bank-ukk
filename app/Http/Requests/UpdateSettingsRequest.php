<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Prepare formatted numeric inputs for validation.
     */
    protected function prepareForValidation(): void
    {
        $normalized = [];

        foreach (['min_initial_deposit', 'daily_withdraw_limit'] as $field) {
            if ($this->has($field)) {
                $raw = (string) $this->input($field);
                $normalized[$field] = str_replace(['.', ',', ' '], '', trim($raw));
            }
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'min_initial_deposit' => ['required', 'integer', 'min:1000', 'max:10000000'],
            'daily_withdraw_limit' => ['required', 'integer', 'min:10000', 'max:100000000'],
            'scanner_auto_submit' => ['required', 'boolean'],
            'scanner_beep_sound' => ['required', 'boolean'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'min_initial_deposit.required' => 'Nominal setoran awal minimal wajib diisi.',
            'min_initial_deposit.integer' => 'Nominal setoran awal minimal harus berupa angka bulat.',
            'min_initial_deposit.min' => 'Setoran awal minimal paling sedikit Rp 1.000.',
            'daily_withdraw_limit.required' => 'Batas penarikan harian wajib diisi.',
            'daily_withdraw_limit.integer' => 'Batas penarikan harian harus berupa angka bulat.',
            'daily_withdraw_limit.min' => 'Batas penarikan harian minimal Rp 10.000.',
        ];
    }
}
