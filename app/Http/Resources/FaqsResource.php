<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FaqsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,

            'title' => clean_html($this->title, 100),
            'content' => clean_html($this->content, 50),

            'position' => $this->position,

            'active' => $this->active ? true : false,
            'is_super_user' => auth()->user()->isAn('super-user'),

            'created_at' => $this->created_at ? $this->created_at->format('d/m/Y g:i a') : 'No definido',
            'updated_at' => $this->created_at ? $this->updated_at->format('d/m/Y g:i a') : 'No definido',
        ];
    }
}
