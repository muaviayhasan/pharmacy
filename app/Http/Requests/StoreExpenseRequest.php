<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'category_id' => ['nullable', 'integer', 'exists:expense_categories,id'],
            'expense_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,card,bank,cheque'],
            'payment_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
        ];
    }
}
