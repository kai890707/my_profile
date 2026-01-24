<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'phone' => $this->phone,
            'email_public' => $this->email_public,
            'line_id' => $this->line_id,
            'wechat_id' => $this->wechat_id,
            'contact_preferences' => $this->contact_preferences,
            'has_contact_methods' => $this->hasContactMethods(),
        ];
    }
}
