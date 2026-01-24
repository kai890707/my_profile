<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContactRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'salesperson_id' => $this->salesperson_id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'message' => $this->message,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'created_at' => $this->created_at?->toIso8601String(),
            'salesperson' => [
                'id' => $this->salesperson->id,
                'name' => $this->salesperson->name,
                'email' => $this->salesperson->email,
            ],
        ];
    }
}
