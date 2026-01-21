<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalespersonProfileResource extends JsonResource
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
            'company_id' => $this->company_id,
            'full_name' => $this->full_name,
            'phone' => $this->phone,
            'bio' => $this->bio,
            'specialties' => $this->specialties,
            'service_regions' => $this->service_regions,
            'years_of_experience' => $this->years_of_experience,
            'avatar_data' => $this->avatar_data,
            'avatar_mime' => $this->avatar_mime,
            'avatar_size' => $this->avatar_size,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // Include relationships if loaded
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'company' => $this->whenLoaded('company', fn () => new CompanyResource($this->company)),
            'experiences' => ExperienceResource::collection($this->whenLoaded('experiences')),
            'certifications' => CertificationResource::collection($this->whenLoaded('certifications')),
        ];
    }
}
