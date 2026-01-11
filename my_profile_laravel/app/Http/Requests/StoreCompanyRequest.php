<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCompanyRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:200'],
            'tax_id' => ['nullable', 'string', 'max:50', 'unique:companies,tax_id'],
            'is_personal' => ['boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            // If is_personal is false (registered company), tax_id is required
            if ($this->input('is_personal') === false && empty($this->input('tax_id'))) {
                $validator->errors()->add('tax_id', '註冊公司必須提供統一編號');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '請輸入公司名稱',
            'name.max' => '公司名稱不得超過 200 個字元',
            'tax_id.max' => '統一編號不得超過 50 個字元',
            'tax_id.unique' => '此統一編號已被註冊',
            'is_personal.boolean' => '個人工作室標記格式錯誤',
        ];
    }
}
