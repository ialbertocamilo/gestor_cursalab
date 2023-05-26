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
        // $usuario_id = $request->usuario_id;
        // $attendants = $this->attendants->where('type.code', 'cohost')->where('usuario_id', $usuario_id)->first();
        switch($this->status->color){
            case 'primary':
                $color = "#00E396";
                break;
            case 'warning':
                $color = "#9696F2";
                break;
            case 'error':
                $color = "#FF4560";
                break;
            default:
                $color = "#2A3649";
        }
        return [
            'id' => $this->id,
            // 'attendants' => $attendants,
            // 'attendants' => $this->attendants,
//            'name' => "[ACC#{$this->account->id}] " . $this->name , // DEV
            'name' => $this->name , // DEV
            'custom_meeting_name' => $this->name,
//            'type' => "[{$this->account->service->name}] {$this->type->name} ". // DEV
            'type' => $this->type->name,
            'host' => $this->host->name ?? 'No definido',
            // 'duration' => $this->duration . ' min.',
            'status_meeting' => [
                'text' => $this->status->name,
                'color' => $color,
            ],
            'starts_at' => $this->starts_at->format('d/m/Y'),
            'starts_at_horario' => $this->starts_at->format('g:i a'),
            'starts_at_duracion' => $this->duration . ' min',

            'attendants_count' => $this->attendants_count,
            'prefix' => $this->buildPrefix(),

            'editable' => $this->canBeEdited(),
            'cancelable' => $this->canBeCancelled(),
            'deletable' => $this->canBeDeleted(),
            'is_live' => $this->isLive(),
            'is_super_user'=>auth()->user()->isAn('super-user'),
            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->updated_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
