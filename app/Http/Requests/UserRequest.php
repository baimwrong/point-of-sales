<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * UserRequest
 *
 * Handles validation for creating and updating users.
 */
class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->route('id');
        $isCreating = $this->isMethod('post');

        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'role_id'  => ['required', 'exists:roles,id'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => [
                $isCreating ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attribute names for validator error messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'role_id' => 'role',
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'password.required'  => 'A password is required when creating a new user.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min'       => 'The password must be at least 8 characters.',
        ];
    }
}
