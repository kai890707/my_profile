<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ApprovedSalespersonExists implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $salesperson = User::with('salespersonProfile')
            ->where('id', $value)
            ->where('role', 'salesperson')
            ->first();

        if (! $salesperson || ! $salesperson->salespersonProfile || $salesperson->salespersonProfile->approval_status !== 'approved') {
            $fail('無法聯繫此業務員，業務員不存在或尚未通過審核');
        }
    }
}
