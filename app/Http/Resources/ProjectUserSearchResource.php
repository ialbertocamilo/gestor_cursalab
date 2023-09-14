<?php

namespace App\Http\Resources;

use App\Services\FileService;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectUserSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if(count($this->project_resources)>0){
            foreach ($this->project_resources as $project_resource) {
                $file = $project_resource->path_file;
                // $project_resource->full_path_file =  str_contains($file,'http') ? $file : env('DO_URL') . "/" .$file ;
                $project_resource->full_path_file = FileService::generateUrl($file,'s3');
            }
        }
        return [
            'subworkspace'=>$this->subworkspace->name,
            'name'=>$this->name,
            'lastname'=>$this->lastname.' '.$this->sourname,
            'document'=>$this->document,
            'status_project' => isset($this->projects[0]->status) ? $this->projects[0]->status->name : 'Pendiente',
            'status_project_id' => isset($this->projects[0]->status) ? $this->projects[0]->status->id : null,
            'project_user_id' => isset($this->projects[0]->id) ? $this->projects[0]->id : null,
            'msg_to_user' => isset($this->projects[0]->msg_to_user) ? $this->projects[0]->msg_to_user : null,
            'resources_user' => $this->project_resources,
            'has_resource' => count($this->project_resources)>0
        ]; 
    }
}
