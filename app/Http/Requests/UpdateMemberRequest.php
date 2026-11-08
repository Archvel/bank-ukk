<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $member = $this->route('member');
        $userId = $member?->user_id ?? $member?->user?->id;

        return [
            'student_name' => ['sometimes', 'required', 'string', 'max:255'],
            'class_name' => ['sometimes', 'nullable', 'string', 'max:50'],
            'major' => ['sometimes', 'nullable', 'string', 'max:50'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'username' => ['nullable', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($userId)],
            'graduation_year' => ['sometimes', 'required', 'string', 'max:10'],
            'phone_number' => ['nullable', 'string', 'max:20'],
        ];
    }
}
