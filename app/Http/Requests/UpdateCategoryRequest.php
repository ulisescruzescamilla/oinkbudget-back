<?php

namespace App\Http\Requests;

use App\Enums\BalanceTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Trim the name before validating so the uniqueness check compares trimmed values.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('categories', 'name')->withoutTrashed()->ignore($this->route('category'))],
            'type' => ['sometimes', 'string', Rule::enum(BalanceTypeEnum::class)],
            'color' => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'icon_code' => 'sometimes|string|max:10',
        ];
    }

    /**
     * Spanish messages for the name and type rules, shown by the app under their fields.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede tener más de :max caracteres.',
            'name.unique' => 'Ya existe una categoría con ese nombre.',
            'type.required' => 'El tipo es obligatorio.',
            'type.string' => 'El tipo debe ser ingreso o gasto.',
            'type.enum' => 'El tipo debe ser ingreso o gasto.',
        ];
    }
}
