<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class Iso8601Offset implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/(Z|[+-]\d{2}:?\d{2})$/', $value)) {
            $fail('The :attribute must be an ISO-8601 datetime with an explicit UTC offset.');
        }
    }
}
