<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterSalespersonRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'specialties' => ['nullable', 'string', 'max:500'],
            'service_regions' => ['nullable', 'array'],
            'service_regions.*' => ['string', 'max:100'],
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
            'name.required' => '請輸入姓名',
            'email.required' => '請輸入電子郵件',
            'email.email' => '請輸入有效的電子郵件格式',
            'email.unique' => '此電子郵件已被註冊',
            'password.required' => '請輸入密碼',
            'password.min' => '密碼至少需要 8 個字元',
            'password.confirmed' => '密碼確認不一致',
            'full_name.required' => '請輸入業務員全名',
            'phone.required' => '請輸入聯絡電話',
            'phone.max' => '聯絡電話不得超過 20 個字元',
            'bio.max' => '個人簡介不得超過 1000 個字元',
            'specialties.max' => '專長領域不得超過 500 個字元',
            'service_regions.array' => '服務區域格式錯誤',
        ];
    }
}
