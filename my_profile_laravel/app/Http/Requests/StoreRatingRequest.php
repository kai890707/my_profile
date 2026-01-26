<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
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
            'salesperson_id' => ['required', 'integer', 'exists:users,id'],
            'contact_request_id' => ['required', 'integer', 'exists:contact_requests,id'],
            'rating' => ['required', 'numeric', 'min:1', 'max:5', 'regex:/^\d(\.5)?$/'],
            'comment' => ['nullable', 'string', 'min:10', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'salesperson_id.required' => '請選擇業務員',
            'salesperson_id.exists' => '業務員不存在',
            'contact_request_id.required' => '聯繫記錄不存在',
            'contact_request_id.exists' => '聯繫記錄不存在',
            'rating.required' => '請選擇評分',
            'rating.min' => '評分最少 1 星',
            'rating.max' => '評分最多 5 星',
            'rating.regex' => '評分必須是 0.5 的倍數',
            'comment.min' => '評論至少需要 10 個字',
            'comment.max' => '評論最多 500 個字',
        ];
    }
}
