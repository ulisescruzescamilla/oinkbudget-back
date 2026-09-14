<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBudgetRequest extends FormRequest
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
            'name' => 'sometimes|nullable|string|min:2|max:255',
            'max_limit' => 'nullable|required_without:percentage_value|numeric|min:0',
            'percentage_value' => 'nullable|required_without:max_limit|integer|between:0,100',
            'expense_amount' => 'sometimes|nullable|numeric|min:0',
            'period' => 'sometimes|nullable|string|in:yearly,monthly,biweekly,weekly',
            'is_recurrent' => 'nullable|boolean',
            'start_date' => 'sometimes|nullable|date|date_format:Y-m-d',
            'end_date' => 'sometimes|nullable|date|date_format:Y-m-d|after:start_date',
            'category_id' => 'nullable|integer|exists:categories,id',
        ];
    }
}
