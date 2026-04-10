<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIncomeRequest extends FormRequest
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
        ];
    }
}
