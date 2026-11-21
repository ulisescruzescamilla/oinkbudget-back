<?php

namespace App\Http\Requests;

use App\Models\Period;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePeriodRequest extends FormRequest
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
     * The end date is compared against the incoming start date when present,
     * otherwise against the start date already stored on the period.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Period $period */
        $period = $this->route('period');

        $effectiveStartDate = $this->has('start_date') ? 'start_date' : $period->start_date->format('Y-m-d');
        $effectiveEndDate = $this->has('end_date') ? 'end_date' : $period->end_date->format('Y-m-d');

        return [
            'start_date' => "sometimes|date_format:Y-m-d|before:{$effectiveEndDate}",
            'end_date' => "sometimes|date_format:Y-m-d|after:{$effectiveStartDate}",
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Spanish messages for the date rules, shown by the app under their fields.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'start_date.date_format' => 'La fecha de inicio debe tener el formato AAAA-MM-DD.',
            'start_date.before' => 'La fecha de inicio debe ser anterior a la fecha de fin.',
            'end_date.date_format' => 'La fecha de fin debe tener el formato AAAA-MM-DD.',
            'end_date.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}
