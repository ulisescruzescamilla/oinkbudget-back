<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Translation\PotentiallyTranslatedString;

/**
 * Case-insensitive uniqueness that does not depend on the column collation.
 * Queries through the model, so soft deleted records are excluded by its global scope.
 */
class UniqueCaseInsensitive implements ValidationRule
{
    /**
     * @param  class-string<Model>  $model
     */
    public function __construct(
        private string $model,
        private string $column,
        private string $message,
        private ?Model $ignore = null,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            return;
        }

        $isTaken = $this->model::query()
            ->whereRaw("LOWER({$this->column}) = LOWER(?)", [$value])
            ->when($this->ignore, fn ($query) => $query->whereKeyNot($this->ignore->getKey()))
            ->exists();

        if ($isTaken) {
            $fail($this->message);
        }
    }
}
