<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,
            'due_time' => $this->due_time ? $this->due_time->format('H:i') : null,
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'completed_at' => $this->completed_at ? $this->completed_at->toISOString() : null,

            // Relationships
            'assignee' => $this->whenLoaded('assignee', function () {
                return [
                    'id' => $this->assignee->id,
                    'name' => $this->assignee->name,
                    'email' => $this->assignee->email,
                    'avatar' => $this->assignee->avatar,
                ];
            }),
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ];
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'time_entries' => TimeEntryResource::collection($this->whenLoaded('timeEntries')),
            'status_changes' => StatusChangeResource::collection($this->whenLoaded('statusChanges')),
        ];
    }
}
