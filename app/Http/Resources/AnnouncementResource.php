<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        $expired = '';
        if ($this->end_date) {
            $endDate = new Carbon($this->end_date);
            $endDate->addDays(1);
            $now = Carbon::now();
            if ($now->gt($endDate)) $expired = 'Ya venció';
        }


        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'image' =>  FileService::generateUrl($this->imagen),
            'active' => $this->active ? true : false,
            'orden' => $this->orden,
            'publish_date' => $this->getPublicationDate(),
            'expired' => $expired,
            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
            'is_super_user'=>auth()->user()->isAn('super-user'),
            'segments_count' => $this->segments_count,
        ];
    }
}
