<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBudgetRequest extends FormRequest
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
            'max_limit' => 'required|numeric|min:0',
            'expense_amount' => 'required|numeric|min:0',
            'percentage_value' => 'required|integer|min:0|max:100',
            'graph_color' => 'required|string|regex:/^[A-Fa-f0-9]{6}$/',
        ];
    }
}
