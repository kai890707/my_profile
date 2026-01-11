<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExperienceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:200'],
            'position' => ['required', 'string', 'max:200'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
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
            'company.required' => '公司名稱為必填項目',
            'company.max' => '公司名稱不得超過 200 個字元',
            'position.required' => '職位名稱為必填項目',
            'position.max' => '職位名稱不得超過 200 個字元',
            'start_date.required' => '開始日期為必填項目',
            'start_date.date' => '開始日期格式不正確',
            'end_date.date' => '結束日期格式不正確',
            'end_date.after_or_equal' => '結束日期必須等於或晚於開始日期',
            'sort_order.integer' => '排序順序必須為整數',
            'sort_order.min' => '排序順序不得小於 0',
        ];
    }
}
