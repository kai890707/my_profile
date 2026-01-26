<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Rating
 */
class RatingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'salesperson_id' => $this->salesperson_id,
            'rating' => round($this->rating, 1),
            'comment' => $this->comment,
            'reply' => $this->reply,
            'replied_at' => $this->replied_at?->toIso8601String(),
            'status' => $this->status,
            'is_hidden' => $this->is_hidden,
            'edited_at' => $this->edited_at?->toIso8601String(),
            'edit_count' => $this->edit_count,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),

            // Relations
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'username' => $this->user->username,
                    'full_name' => $this->user->full_name,
                    'avatar_url' => $this->user->avatar_url,
                ];
            }),

            'salesperson' => $this->whenLoaded('salesperson', function () {
                return [
                    'id' => $this->salesperson->id,
                    'username' => $this->salesperson->username,
                    'full_name' => $this->salesperson->full_name,
                    'avatar_url' => $this->salesperson->avatar_url,
                ];
            }),
        ];
    }
}
