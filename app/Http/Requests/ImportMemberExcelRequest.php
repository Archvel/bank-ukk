<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ImportMemberExcelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (! in_array($ext, ['xlsx', 'xls', 'csv', 'txt'])) {
                        $fail('Berkas harus berformat Excel (.xlsx, .xls) atau .csv.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Silakan pilih berkas Excel atau CSV untuk diimpor.',
            'file.file' => 'Berkas yang diunggah tidak valid.',
            'file.max' => 'Ukuran berkas maksimal adalah 10 MB.',
        ];
    }
}
