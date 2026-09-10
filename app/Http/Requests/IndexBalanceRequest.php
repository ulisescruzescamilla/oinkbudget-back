<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexBalanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date|date_format:Y-m-d',
            'end_date' => 'nullable|date|date_format:Y-m-d',
            'order' => 'nullable|in:asc,desc',
            'group_by' => 'nullable|string|in:created_at,account_id',
            'range' => 'nullable|string|in:today,week,month,all',
            'type' => 'nullable|string|in:income,expense,all',
        ];
    }
}
