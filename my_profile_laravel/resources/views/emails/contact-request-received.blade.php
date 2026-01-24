@component('mail::message')
# 新的聯繫請求

您好，有一位客戶想要聯繫您！

## 客戶資訊

- **姓名**: {{ $contactRequest->customer_name }}
- **Email**: {{ $contactRequest->customer_email }}
@if($contactRequest->customer_phone)
- **電話**: {{ $contactRequest->customer_phone }}
@endif

## 訊息內容

{{ $contactRequest->message }}

@component('mail::button', ['url' => $replyUrl])
回覆客戶
@endcomponent

---

**提醒**: 請盡快回覆客戶，提供專業的諮詢服務。

感謝您使用 YAMU 業務員推廣平台！

@endcomponent
