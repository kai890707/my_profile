<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveCompanyRequest extends FormRequest
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
            'company_id' => [
                'nullable',
                'integer',
                Rule::exists('companies', 'id'),
                // Ensure company_id and is_self_employed are mutually exclusive
                Rule::requiredIf(fn () => ! $this->boolean('is_self_employed')),
            ],
            'is_self_employed' => [
                'nullable',
                'boolean',
                // Ensure at least one is provided
                Rule::requiredIf(fn () => ! $this->has('company_id')),
            ],
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
            'company_id.exists' => '選擇的公司不存在',
            'company_id.integer' => '公司ID格式錯誤',
            'company_id.required' => '請選擇公司或勾選自營業者',
            'is_self_employed.boolean' => '自營業者格式錯誤',
            'is_self_employed.required' => '請選擇公司或勾選自營業者',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            // Ensure company_id and is_self_employed are mutually exclusive
            if ($this->filled('company_id') && $this->boolean('is_self_employed')) {
                $validator->errors()->add(
                    'company_id',
                    '不可同時選擇公司和自營業者'
                );
            }

            // Ensure at least one is provided
            if (! $this->filled('company_id') && ! $this->boolean('is_self_employed')) {
                $validator->errors()->add(
                    'company_id',
                    '請選擇公司或勾選自營業者'
                );
            }
        });
    }
}
