<?php

use App\Services\BudgetLimitCalculator;

beforeEach(function () {
    $this->calculator = new BudgetLimitCalculator;
});

it('derives percentage_value from max_limit', function () {
    $result = $this->calculator->calculate(maxLimit: 250.0, percentageValue: null, amountAvailable: 1000.0);

    expect($result)->toBe(['max_limit' => 250.0, 'percentage_value' => 25]);
});

it('derives max_limit from percentage_value', function () {
    $result = $this->calculator->calculate(maxLimit: null, percentageValue: 25, amountAvailable: 1000.0);

    expect($result)->toBe(['max_limit' => 250.0, 'percentage_value' => 25]);
});

it('prefers max_limit when both are provided', function () {
    $result = $this->calculator->calculate(maxLimit: 250.0, percentageValue: 90, amountAvailable: 1000.0);

    expect($result)->toBe(['max_limit' => 250.0, 'percentage_value' => 25]);
});

it('returns zero percentage when no amount is available', function () {
    $result = $this->calculator->calculate(maxLimit: 250.0, percentageValue: null, amountAvailable: 0.0);

    expect($result)->toBe(['max_limit' => 250.0, 'percentage_value' => 0]);
});

it('returns zero max_limit when no amount is available', function () {
    $result = $this->calculator->calculate(maxLimit: null, percentageValue: 25, amountAvailable: 0.0);

    expect($result)->toBe(['max_limit' => 0.0, 'percentage_value' => 25]);
});

it('returns zeroes when neither value is provided', function () {
    $result = $this->calculator->calculate(maxLimit: null, percentageValue: null, amountAvailable: 1000.0);

    expect($result)->toBe(['max_limit' => 0.0, 'percentage_value' => 0]);
});
