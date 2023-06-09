<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;

class Certificate extends Model
{
    use softdeletes;

    protected $fillable = ['media_id', 'title', 'path_file', 'info_bg', 'd_objects', 's_objects', 'active'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public function media()
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    protected function search($request, $paginate = 10) {
        $workspace = get_current_workspace();

        $q = self::query()->withWhereHas('media', function($query) use($workspace) {
            $query->where('workspace_id', $workspace->id);
        });

        if ($request->q)
            $q->where('title', 'like', "%$request->q%");

        if ($request->active)
            $q->where('active', $request->active);

        $sort = ($request->sortDesc == 'true') ? 'DESC' : 'ASC';
        
        if($request->sortBy) 
            $q->orderBy($request->sortBy, $sort);
        else
            $q->orderBy('created_at', 'DESC');

        return $q->paginate($request->paginate);
    }
    
    protected function getBasesFromImages($edit_plantilla) {
        $images_bases = [];
        $images_bases = $edit_plantilla['s_objects_images'];
        $images_bases[] = $edit_plantilla['s_object_bg'];

        return collect($images_bases)->map(function ($item) {
            return [
                'media_id' => $item['media_id'],
                'src' => $item['src'],
            ];
        });
    }
    

    protected function pushType_iText($e_static) {
        $data = get_data_bykeys($e_static, ['type', 'text', 'left','top',  'fill', 'textAlign', 
            // adicional
            'fontStyle', 'fontFamily', 'fontWeight', 'fontSize',
            // 'width', 'height',
            // 'lineHeight', 'zoomX', 'originX', 'originY'
        ]);
        return $data;
    }

    protected function compareBases64($static, $bases_data) {
        $base_main = explode(',', $static, 2);
        $base_main = $base_main[1];

        $media_id = NULL;

        foreach ($bases_data as $base) {
            $base_current = explode(',', $base['src'], 2);
            $base_current = $base_current[1];

            if($base_main === $base_current) {
                $media_id = $base['media_id'];
                break;
            }
        }

        return $media_id;
    }
    

    protected function pushType_image($e_static, $nombre_plantilla, 
                                      $compare = false, $bases64_compare = []) {
        $data = get_data_bykeys($e_static, ['type', 'left','top', 'scaleX', 'scaleY'
            // adicional
            // 'width', 'height',
            // 'flipX', 'flipY', 'skewX', 'skewY'
        ]);

        $nombre_plantilla_final = Media::generateNameFile($nombre_plantilla, 'jpg'); 
        $path = 'images/diplomas/'.$nombre_plantilla_final;

        // comparar los bases
        if($compare){
            $media_id = self::compareBases64($e_static['src'], $bases64_compare);

            // si es igual al base  
            if($media_id) {
                $data['media_id'] = $media_id;
                return $data;
            }
        }

        $media = self::uploadMediaBase64($nombre_plantilla_final, $path, $e_static['src']);   
        $data['media_id'] = $media->id;

        // info(['msg' => 'create a media', 'media_id' => $media->id]);
        return $data;
    }

    protected function uploadMediaBase64($name, $path, $base64)
    {
        $exploded = explode(',', $base64, 2);
        $s3 = Storage::disk('s3')->put($path, base64_decode($exploded[1]), 'public');
        $size = Storage::disk('s3')->size($path);

        try {
            $save_size = round($size  / 1024);
        } catch (\Exception $exception) {
            $save_size = 0;
        }

        $media = new Media;
        $media->title = $name;
        $media->file = $path;
        $media->ext = 'jpg';
        $media->size = $size;
        $media->workspace_id = session('workspace')['id'] ?? NULL;
        $media->save();

        return $media;
    }

    protected function getTotalByUser($user = null, $assigned_courses = null)
    {
        $user = $user ?? auth()->user();

        $user_courses = $assigned_courses ?? $user->getCurrentCourses(withRelations: 'soft');
        $user_courses_id = $user_courses->pluck('id');
        $user_compatibles_courses_id = $user_courses->whereNotNull('compatible')->pluck('compatible.course_id');
        $all_courses_id = $user_courses_id->merge($user_compatibles_courses_id);

        $query = SummaryCourse::query()
            ->where('user_id', $user->id)
            ->whereIn('course_id', $all_courses_id->toArray())
            ->whereNotNull('certification_issued_at');

        // if ($request->type == 'accepted')
        //     $query->whereNotNull('certification_accepted_at');

        // if ($request->type == 'pending')
        //     $query->whereNull('certification_accepted_at');

        $certificates = $query->get();

        $total = 0;

        // $qs = $request->q ?? NULL;

        foreach ($user_courses as $user_course) {

            // if ($qs AND !stringContains($user_course->name, $qs))
            //     continue;

            $certificate = $certificates->where('course_id', $user_course->id)->first();

            if ($certificate) {

                $total++;

                continue;
            }

            if ($user_course->compatible) {

                $compatible_certificate = $certificates->where('course_id', $user_course->compatible->course_id)->first();

                if ($compatible_certificate) {
                    $total++;
                }
            }
        }

        return $total;
    }
}


