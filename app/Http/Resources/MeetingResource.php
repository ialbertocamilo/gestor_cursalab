<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
//            'name' => "[ACC#{$this->account->id}] " . $this->name , // DEV
           'name' => $this->name , // DEV
            'custom_meeting_name' => $this->name,
//            'type' => "[{$this->account->service->name}] {$this->type->name} ". // DEV
            'type' => $this->type->name,
            'host' => $this->host->nombre ?? 'No definido',
            // 'duration' => $this->duration . ' min.',
            'status' => [
                'text' => $this->status->name,
                'color' => $this->status->color,
            ],
            'starts_at' => $this->starts_at->format('d/m/Y g:i a') . ' (' . $this->duration . ' min)',

            'attendants_count' => $this->attendants_count,

            'editable' => $this->canBeEdited(),
            'cancelable' => $this->canBeCancelled(),
            'deletable' => $this->canBeDeleted(),
            'is_live' => $this->isLive(),

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->updated_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
