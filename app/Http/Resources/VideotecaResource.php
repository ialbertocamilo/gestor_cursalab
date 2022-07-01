<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Videoteca;

class VideotecaResource extends JsonResource
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
            'description' => $this->description,
            'categoria' => $this->categoria,
            'media_type' => $this->media_type,
            'tags' => $this->tags,
            // 'views' => $this->actions_sum,
            'views' => Videoteca::getViews($this),
            'image' => space_url($this->getPreview()),
            'active' => $this->active ? true : false,

            // 'orden' => $this->orden,
            // 'publication_date' => $this->getPublicationDate(),
            // 'body' => clean_html($this->content, 30),

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];
    }
}
