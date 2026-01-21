<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterUserRequest extends FormRequest
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
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['nullable', 'in:user,salesperson'],
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
            'username.required' => '請輸入使用者名稱',
            'username.unique' => '此使用者名稱已被使用',
            'name.required' => '請輸入姓名',
            'email.required' => '請輸入電子郵件',
            'email.email' => '請輸入有效的電子郵件格式',
            'email.unique' => '此電子郵件已被註冊',
            'password.required' => '請輸入密碼',
            'password.min' => '密碼至少需要 6 個字元',
            'password.confirmed' => '密碼確認不一致',
            'role.in' => '角色必須為 user 或 salesperson',
        ];
    }
}
