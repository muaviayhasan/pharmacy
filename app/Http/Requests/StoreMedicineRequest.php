<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'generic_name' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:medicine_categories,id'],
            'manufacturer_id' => ['nullable', 'integer', 'exists:manufacturers,id'],
            'default_supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'dosage_form' => ['nullable', 'string', 'max:50'],
            'strength' => ['nullable', 'string', 'max:50'],
            'strength_unit' => ['nullable', 'string', 'max:20'],
            'pack_size' => ['nullable', 'string', 'max:50'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:medicines,barcode'],
            'rack_shelf' => ['nullable', 'string', 'max:50'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'max_discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_stock_level' => ['nullable', 'integer', 'min:0'],
            'reorder_level' => ['nullable', 'integer', 'min:0'],
            'max_stock_level' => ['nullable', 'integer', 'min:0'],
            'prescription_required' => ['nullable', 'boolean'],
            'controlled_medicine' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
