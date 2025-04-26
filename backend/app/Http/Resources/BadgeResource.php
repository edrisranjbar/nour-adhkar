<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BadgeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'points_required' => $this->points_required,
            'earned_at' => $this->whenPivotLoaded('user_badges', function () {
                return $this->pivot->earned_at;
            }),
        ];
    }
} 