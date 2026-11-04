<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetOpeningCashRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isTeller() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'opening_balance' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
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
            'opening_balance.required' => 'Nominal uang kas awal wajib diisi.',
            'opening_balance.integer' => 'Nominal uang kas awal harus berupa bilangan bulat.',
            'opening_balance.min' => 'Nominal uang kas awal tidak boleh bernilai negatif.',
            'notes.max' => 'Catatan kas awal maksimal 500 karakter.',
        ];
    }
}
