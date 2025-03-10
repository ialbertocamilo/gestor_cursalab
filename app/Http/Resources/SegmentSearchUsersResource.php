<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SegmentSearchUsersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $user = $this;

        return [
            'document' => $user->document,
            'fullname' => $user->fullname,

            'criterion_value_id' => $user->criterion_values?->first()?->id,
        ];
    }
}
