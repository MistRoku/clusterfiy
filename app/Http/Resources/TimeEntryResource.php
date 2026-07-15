<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeEntryResource extends JsonResource
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
            'started_at' => $this->started_at->toISOString(),
            'ended_at' => $this->ended_at ? $this->ended_at->toISOString() : null,
            'duration_hours' => $this->duration_hours,
            'description' => $this->description,
        ];
    }
}
