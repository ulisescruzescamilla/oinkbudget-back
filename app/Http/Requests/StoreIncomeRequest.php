<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIncomeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['required', 'string'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'client_id' => ['sometimes', 'nullable', 'uuid'],
            'created_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
