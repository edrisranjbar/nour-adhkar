<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'heart_score' => $this->heart_score,
            'streak' => $this->streak,
            'has_new_badge' => $this->has_new_badge,
            'role' => $this->role,
            'active' => $this->active,
            'total_adhkar_completed' => $this->total_dhikrs,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 