<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * ProductRequest
 *
 * Handles validation for creating and updating products.
 */
class ProductRequest extends FormRequest
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
        $productId = $this->route('product') ?? $this->route('id');

        return [
            'name'            => ['required', 'string', 'max:255'],
            'sku'             => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'barcode'         => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('products', 'barcode')->ignore($productId),
            ],
            'selling_price'   => ['required', 'numeric', 'min:0'],
            'cost_price'      => ['required', 'numeric', 'min:0'],
            'stock_quantity'  => ['nullable', 'integer', 'min:0'],
            'category_id'     => ['nullable', 'exists:categories,id'],
            'unit_id'         => ['nullable', 'exists:units,id'],
            'image'           => ['nullable', 'image', 'max:2048'],
            'min_stock_alert' => ['nullable', 'integer', 'min:0'],
            'tax_rate'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'description'     => ['nullable', 'string'],
            'is_active'       => ['nullable', 'boolean'],
            'track_stock'     => ['nullable', 'boolean'],
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
            'sku'             => 'SKU',
            'selling_price'   => 'selling price',
            'cost_price'      => 'cost price',
            'stock_quantity'  => 'stock quantity',
            'category_id'     => 'category',
            'unit_id'         => 'unit',
            'min_stock_alert' => 'minimum stock alert',
            'tax_rate'        => 'tax rate',
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
            'sku.unique'     => 'This SKU is already in use by another product.',
            'barcode.unique' => 'This barcode is already assigned to another product.',
            'image.max'      => 'The product image must not exceed 2 MB.',
        ];
    }
}
