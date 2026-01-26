<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\RatingReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReportRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', Rule::in([
                'false_info',
                'malicious',
                'sensitive',
                'spam',
                'other',
            ])],
            'description' => ['nullable', 'string', 'min:10', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => '請選擇檢舉原因',
            'reason.in' => '檢舉原因不正確',
            'description.min' => '詳細說明至少需要 10 個字',
            'description.max' => '詳細說明最多 200 個字',
        ];
    }
}
