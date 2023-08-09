<?php

namespace App\Models;

use App\Traits\CustomAudit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProjectResources extends Model
{
    use HasFactory;
    use SoftDeletes , CustomAudit;
    protected $fillable = ['project_id','type_id','size','path_file','from_resource','filename','ext','type_media'];

    public function media()
    {
        return $this->belongsTo('App\Media', 'type_id');
    }

    protected function storeUpdateRequest($request,$project,$from_resource,$insert_media=true){
        $project_resource = [];
        if($request->has('multimedias')){
            $multimedias = $request->multimedias;
            foreach ($multimedias as $multimedia) {
                if($from_resource=='media_project_user'){
                    $project_resource[] = $this->formatProjectResource($request->user->id,$project->id,$from_resource,$multimedia['relative_path'],$multimedia['title'],null);
                }else{
                    $project_resource[] = $this->formatProjectResource($multimedia['id'],$project->id,$from_resource,$multimedia['file'],$multimedia['title'],$multimedia['ext']);
                }
            }
        }
        if($request->hasFile('files')){
            $files = $request->file('files');
            foreach ($files as $file) {
                $path = Media::uploadFile($file,$insert_media);
                if($insert_media){
                    $media = Media::where('file',$path)->select('id','title','ext')->first();
                    $project_resource[] = $this->formatProjectResource($media->id,$project->id,$from_resource,$path,$media->title,$media->ext);
                }else{
                    $project_resource[] = $this->formatProjectResource($request->user->id,$project->id,$from_resource,$path,$file->getClientOriginalName(),$file->getClientOriginalExtension());
                }
            }
        }
        if(count($project_resource)>0){
            foreach ($project_resource as $resource) {
                if(!$this->ProjectResourceExist($resource)){
                    ProjectResources::insert($resource);
                }
            }
            $delete_resources=ProjectResources::where('project_id',$project->id)
                            ->where('from_resource',$from_resource);
            if($from_resource=='media_project_user'){
                $delete_resources = $delete_resources->where('type_id',$request->user->id)->whereNotIn('path_file',array_column($project_resource,'path_file'))->get();
                foreach ($delete_resources as $delete_resource) {
                    Storage::disk('s3')->delete($delete_resource->path_file);
                    $delete_resource->delete();
                }
            }else{
                $delete_resources->whereNotIn('type_id',array_column($project_resource,'type_id'));
                $delete_resources->delete();
            }
        }else{
            $delete_resources = ProjectResources::where('project_id',$project->id)->where('from_resource',$from_resource);
            if($from_resource=='media_project_user'){
                $delete_resources = $delete_resources->where('type_id',$request->user->id)->get();
                foreach ($delete_resources as $delete_resource) {
                    Storage::disk('s3')->delete($delete_resource->path_file);
                    $delete_resource->delete();
                }
            }else{
                $delete_resources->delete();
            }
        }
        return $project_resource;
    }
    private function ProjectResourceExist($resource){
        if($resource['from_resource']=='media_project_user'){
            return !is_null(self::where('project_id',$resource['project_id'])->where('from_resource',$resource['from_resource'])->where('path_file',$resource['path_file'])->first());
        }else{
            return !is_null(self::where('project_id',$resource['project_id'])->where('from_resource',$resource['from_resource'])->where('type_id',$resource['type_id'])->first());
        }
    }
    private function formatProjectResource($type_id,$project_id,$from_resource,$path_file,$title,$ext){
        $ext = strtolower($ext);
        $extensions = config('constantes.extensiones');
        $tipo = 'office';
        $size = Storage::disk('s3')->size($path_file);
        $size =  $size ? number_format($size / 1048576, 2) : 0;
        foreach ($extensions as $key => $extension) {
            in_array($ext,$extension) && $tipo = $key;
        }
        return [
            'type_id'=>$type_id,
            'project_id'=>$project_id,
            'from_resource'=>$from_resource,
            'path_file'=>$path_file,
            'filename'=>$title,
            'ext' => $ext,
            'size' =>$size,
            'type_media'=>$tipo
        ];
    }
}
