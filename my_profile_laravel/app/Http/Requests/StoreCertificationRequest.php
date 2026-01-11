<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCertificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
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
            'issuer' => ['required', 'string', 'max:200'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'string'], // Base64 encoded file
            'file_mime' => ['required', 'string', 'in:image/jpeg,image/jpg,image/png,application/pdf'],
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
            'name.required' => '證照名稱為必填項目',
            'name.max' => '證照名稱不得超過 200 個字元',
            'issuer.required' => '發證機構為必填項目',
            'issuer.max' => '發證機構名稱不得超過 200 個字元',
            'issue_date.date' => '發證日期格式不正確',
            'expiry_date.date' => '到期日期格式不正確',
            'expiry_date.after_or_equal' => '到期日期必須等於或晚於發證日期',
            'file.required' => '證照檔案為必填項目',
            'file.string' => '證照檔案格式不正確',
            'file_mime.required' => '檔案類型為必填項目',
            'file_mime.in' => '只接受 JPEG、PNG 或 PDF 格式的檔案',
        ];
    }
}
