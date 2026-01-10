<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class RejectSalespersonRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'max:500'],
            'reapply_days' => ['nullable', 'integer', 'min:0', 'max:90'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rejection_reason.required' => '請輸入拒絕原因',
            'rejection_reason.max' => '拒絕原因不得超過 500 個字元',
            'reapply_days.integer' => '重新申請天數必須為整數',
            'reapply_days.min' => '重新申請天數不得小於 0',
            'reapply_days.max' => '重新申請天數不得超過 90 天',
        ];
    }

    /**
     * Get the default value for reapply days.
     */
    public function getReapplyDays(): int
    {
        return $this->input('reapply_days', User::DEFAULT_REAPPLY_DAYS);
    }
}
