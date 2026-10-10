<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * CustomerRequest
 *
 * Handles validation for creating and updating customers.
 */
class CustomerRequest extends FormRequest
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
        $customerId = $this->route('customer') ?? $this->route('id');

        return [
            'name'    => ['required', 'string', 'max:255'],
            'email'   => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customerId),
            ],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'city'    => ['nullable', 'string', 'max:100'],
            'notes'   => ['nullable', 'string'],
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
            'name'  => 'customer name',
            'email' => 'email address',
        ];
    }
}
