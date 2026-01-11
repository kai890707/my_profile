<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificationResource extends JsonResource
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
            'user_id' => $this->user_id,
            'name' => $this->name,
            'issuer' => $this->issuer,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'description' => $this->description,
            'file_data' => null, // Never return file_data in API (performance)
            'file_mime' => $this->file_mime,
            'file_size' => $this->file_size,
            'file_size_mb' => $this->file_size ? round($this->file_size / 1024 / 1024, 2) : null,
            'has_file' => $this->file_size > 0,
            'approval_status' => $this->approval_status,
            'rejected_reason' => $this->rejected_reason,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
