<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $avatar_user = $this->getFirstMediaUrl('avatar', 'thumb');

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'lastname' => $this->lastnames,
            'email' => $this->email,
            'phone' => $this->phone,
            'birthdate' => ($this->age ?? 'X') . ' años',
            'avatar' => empty($avatar_user) ? 'default_user.png' : $avatar_user,
            // 'avatar' => '/avatars/user-' . random_int(1, 8) . '.png',
            'job_position' => $this->job_position->name ?? 'No definido',

            'country' => [
                'name' => $this->country->name ?? 'No definido',
                'code' => $this->country->code ?? '',
            ],
            // 'is_super_user'=>auth()->user()->isAn('super-user'),
            'is_super_user'=> true,

            'active' => $this->active,

            'created_at' => $this->created_at->format('d/m/Y g:i a'),
            'updated_at' => $this->updated_at->format('d/m/Y g:i a'),
        ];

        return $data;
    }
}
