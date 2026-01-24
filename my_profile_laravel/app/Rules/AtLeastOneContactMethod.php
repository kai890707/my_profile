<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AtLeastOneContactMethod implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get all request data to check all contact fields
        $data = request()->all();

        $hasContact = ! empty($data['phone']) ||
                     ! empty($data['email_public']) ||
                     ! empty($data['line_id']) ||
                     ! empty($data['wechat_id']);

        if (! $hasContact) {
            $fail('必須至少提供一種聯繫方式（電話、Email、LINE 或 WeChat）');
        }
    }
}
