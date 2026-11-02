<?php

namespace App\Http\Requests;

use App\Rules\Iso8601Offset;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string',
            'budget_id' => 'required|integer|exists:budgets,id',
            'account_id' => 'required|integer|exists:accounts,id',
            'category_id' => ['sometimes', 'nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'expense')->withoutTrashed()],
            'client_id' => 'sometimes|nullable|uuid',
            'created_at' => ['sometimes', 'nullable', 'date', new Iso8601Offset],
        ];
    }
}
