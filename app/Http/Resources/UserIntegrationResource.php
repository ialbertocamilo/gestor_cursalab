<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserIntegrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    
    public function toArray($request)
    {
        $criterions = [];
        foreach ($this->criterion_values as $criterion_value) {
            if(isset($criterion_value->criterion->code)){
                $criterions[] = [
                    $criterion_value->criterion->code => $criterion_value->value_text
                ];
            }
        }
        return [
            'active' => $this->active,
            'document' => $this->document,
            'person_number' => $this->person_number,
            'fullname' => $this->fullname,
            'name' => $this->name,
            'lastname' => $this->lastname,
            'surname' => $this->surname,
            'username' => $this->username,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'criterions'=> $criterions
        ];
    }
}
