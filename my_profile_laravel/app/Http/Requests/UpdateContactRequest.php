<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role === 'salesperson';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', 'regex:/^09\d{8}$|^0\d-\d{7,8}$/'],
            'email_public' => ['nullable', 'email', 'max:255'],
            'line_id' => ['nullable', 'string', 'min:3', 'max:20', 'regex:/^[a-zA-Z0-9_]+$/'],
            'wechat_id' => ['nullable', 'string', 'min:6', 'max:20', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'contact_preferences' => ['nullable', 'array'],
            'contact_preferences.*' => ['string', 'in:phone,email,line,wechat'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            $hasContact = ! empty($data['phone']) ||
                         ! empty($data['email_public']) ||
                         ! empty($data['line_id']) ||
                         ! empty($data['wechat_id']);

            if (! $hasContact) {
                $validator->errors()->add(
                    'phone',
                    '必須至少提供一種聯繫方式（電話、Email、LINE 或 WeChat）'
                );
            }
        });
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => '電話格式不正確（手機: 0912345678 或市話: 02-12345678）',
            'email_public.email' => 'Email 格式不正確',
            'email_public.max' => 'Email 最多 255 個字元',
            'line_id.min' => 'LINE ID 最少 3 個字元',
            'line_id.max' => 'LINE ID 最多 20 個字元',
            'line_id.regex' => 'LINE ID 格式不正確（僅允許英數字和底線）',
            'wechat_id.min' => 'WeChat ID 最少 6 個字元',
            'wechat_id.max' => 'WeChat ID 最多 20 個字元',
            'wechat_id.regex' => 'WeChat ID 格式不正確（僅允許英數字、底線和減號）',
            'contact_preferences.array' => '聯繫偏好必須是陣列',
            'contact_preferences.*.in' => '聯繫偏好選項無效',
        ];
    }
}
