<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\ContactRequest;
use App\Rules\ApprovedSalespersonExists;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'salesperson_id' => ['required', 'integer', new ApprovedSalespersonExists],
            'customer_phone' => ['nullable', 'string', 'regex:/^09\d{8}$/'],
            'message' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $salespersonId = $this->input('salesperson_id');
            $userId = auth()->id();

            // BR-RL-002: Check 24h rate limit (same salesperson)
            $existingRequest = ContactRequest::where('user_id', $userId)
                ->where('salesperson_id', $salespersonId)
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($existingRequest) {
                $validator->errors()->add(
                    'salesperson_id',
                    '您已在 24 小時內聯繫過此業務員，請稍後再試'
                );

                return;
            }

            // BR-RL-003: Check daily rate limit (5 requests per day across all salespersons)
            $todayCount = ContactRequest::where('user_id', $userId)
                ->whereDate('created_at', Carbon::today())
                ->count();

            if ($todayCount >= 5) {
                $validator->errors()->add(
                    'rate_limit',
                    '您今日的聯繫次數已達上限（每天最多 5 次），請明天再試'
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
            'salesperson_id.required' => '請選擇要聯繫的業務員',
            'salesperson_id.integer' => '業務員 ID 格式不正確',
            'customer_phone.regex' => '電話格式不正確（請使用手機格式：0912345678）',
            'message.required' => '請填寫訊息內容',
            'message.min' => '訊息內容最少 10 個字元',
            'message.max' => '訊息內容最多 500 個字元',
        ];
    }
}
