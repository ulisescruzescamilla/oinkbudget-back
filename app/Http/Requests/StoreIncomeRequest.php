<?php

namespace App\Http\Requests;

use App\Rules\Iso8601Offset;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'income')->withoutTrashed()],
            'client_id' => ['sometimes', 'nullable', 'uuid'],
            'created_at' => ['sometimes', 'nullable', 'date', new Iso8601Offset],
        ];
    }
}
