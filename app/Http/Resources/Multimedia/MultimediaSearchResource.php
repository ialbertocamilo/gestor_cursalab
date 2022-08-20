<?php

namespace App\Http\Resources\Multimedia;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\FileService;

class MultimediaSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'file' => $this->file,
            'ext' => $this->ext,
            'size' => $this->size === 0 ? '-' : $this->size,
            'tipo' => $this->getMediaType($this->ext),
            'created_at' => $this->created_at->format('d/m/Y'),
//            'image' => env('DO_URL')."/".$this->file,
            'image' => FileService::generateUrl($this->getPreview()) ,
        ];
    }
}
