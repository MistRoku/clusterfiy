<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusChangeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'changed_by' => [
                'id' => $this->changedBy->id,
                'name' => $this->changedBy->name,
            ],
            'created_at' => $this->created_at->toISOString(),
        ];
    }
}
