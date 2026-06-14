<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'status' => ['required', 'in:active,inactive,blocked'],
            'two_factor_enabled' => ['nullable', 'boolean'],
            'branches' => ['nullable', 'array'],
            'branches.*' => ['integer', 'exists:branches,id'],
            'default_branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'password' => ['nullable', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ];
    }
}
