<?php

namespace App\Models;

use ZipArchive;
use Aws\S3\S3Client;
use Illuminate\Support\Str;
use App\Services\FileService;
use App\Services\DashboardService;
use Illuminate\Support\Facades\File;

use Symfony\Component\Mime\MimeTypes;
use Illuminate\Support\Facades\Storage;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Foundation\Application;
use App\Models\{Course,
    Topic,
    Announcement,
    School,
    Vademecum,
    Videoteca,
    Workspace};

class Media extends BaseModel
{

    use SoftDeletes;


    const DEFAULT_AUDIO_IMG = "images/default-audio-img_119.png";
    const DEFAULT_SCORM_IMG = "images/default-scorm-img_116_360.png";
    const DEFAULT_VIDEO_IMG = "images/default-video-img_285_360.png";
    const DEFAULT_PDF_IMG = "images/default-pdf-img_210.png";

    protected $table = 'media';

    protected $fillable = [
        'title', 'description', 'file', 'ext', 'status', 'external_id', 'size', 'workspace_id','ia_convert'
    ];

    public function modulesByFile() {

        $workspaces = Workspace::select('id', 'name')
                                ->where('logo', $this->file)
                                ->orWhere('plantilla_diploma', $this->file)
                                ->get();

        $workspaces = $workspaces->map(function ($workspace) {
            $workspace->url = url("/modulos?media_data=modulo");
            $workspace->url_filters = [
                'q' => $workspace->name,
                'id' => $workspace->id
            ];

            return $workspace;
        });

        return $workspaces;
    }


    public function announcementsByFile()
    {
        $announcements = Announcement::select('id', 'nombre as name','active')
                        ->where('imagen', $this->file)
                        ->orWhere('archivo', $this->file)
                        ->get();

        $announcements = $announcements->map(function ($announcement) {
            $announcement_module_id = $announcement->criterionValues()->pluck('criterion_value_id');
            $announcement_filters = [
                'q' => $announcement->name,
                'module' => $announcement_module_id,
                'active' => (int) $announcement->active,
                'id' => $announcement->id
            ];

            $announcement->url = url("/anuncios?media_data=anuncio");
            $announcement->url_filters = $announcement_filters;
            return $announcement;
        });

        return $announcements;
    }

    public function vademecumsByFile()
    {
        $vademecums = Vademecum::select('id', 'name', 'category_id')
                               ->where('media_id', $this->id)
                               ->get();

        $vademecums = $vademecums->map(function ($vademecum) {
            $vademecum_module_id = $vademecum->modules()->pluck('id');
            $vademecum_filters = [
                'q' => $vademecum->name,
                'module_id' => $vademecum_module_id,
                'category_id' => $vademecum->category_id,
                'id' => $vademecum->id
            ];

            $vademecum->url = url("/vademecum?media_data=vademecum");
            $vademecum->url_filters = $vademecum_filters;

            return $vademecum;
        });

        return $vademecums;
    }

    public function videotecasByFile()
    {
        $videotecas = Videoteca::select('id', 'title as name')
                              ->where('media_id', $this->id)
                              ->orWhere('preview_id', $this->id)
                              ->get();

        $videotecas = $videotecas->map(function ($videoteca) {
            $videoteca->url = url("/videoteca/list?media_data=videoteca");
            $videoteca->url_filters = [
                'q' => $videoteca->name,
                'id' => $videoteca->id
            ];
            return $videoteca;
        });

        return $videotecas;
    }

    public function schoolsByFile()
    {
        $schools = School::select('id', 'name')
                         ->where('imagen', $this->file)
                         ->orWhere('plantilla_diploma', $this->file)
                         ->get();

        $schools = $schools->map(function ($school) {
            $school->url = [
                route('escuelas.edit', [
                    'school' => $school->id
                ])
            ];

            return $school;
        });

        return $schools;
    }

    public function coursesByFile()
    {
        $courses = Course::select('id', 'name')->where('imagen', $this->file)
                         ->with('schools:id,name')->get();

        $courses = $courses->map(function ($course) {
            if ($course->schools) {
                $course->url = $course->schools->map(function ($school) use ($course) {
                    return route('cursos.editCurso', [
                        'school' => $school->id,
                        'course' => $course->id,
                    ]);
                })->toArray();

            } else $course->url = [];

            return $course;
        });

        return $courses;
    }

    public function topicsByFile()
    {
        $file = $this->file;
        $topics = Topic::select('id', 'course_id', 'name')
                        ->where('imagen', $file)
                        ->orWhereHas('medias', function ($query) use ($file) {
                            $query->where('value', $file);
                        })
                        ->with([
                        'course' => function($q_course){
                            $q_course->select('id', 'name');
                        },
                        'course.schools' => function($q_course_school){
                            $q_course_school->select('id', 'name');
                        }
                        ])->get();

        $topics = $topics->map(function ($topic) {
            if ($topic->course && $topic->course->schools) {

                $topic->url = $topic->course->schools->map(function ($school) use ($topic) {
                    return url("/escuelas/{$school->id}/cursos/{$topic->course->id}/temas/edit/{$topic->id}");
                })->toArray();

            } else $topic->url = [];

            return $topic;
        });

        return $topics;
    }

    /*

        Attributes

    --------------------------------------------------------------------------*/

    public function getFullTitleAttribute()
    {
        return ucwords(str_replace('-', ' ', $this->title));
    }

    /*

        Methods

    --------------------------------------------------------------------------*/

    /**
     * Upload file to local or remote location
     * @param $file
     * @param $name
     * @param bool $return_media
     * @return Media|Application|UrlGenerator|string
     */
    protected function uploadFile($file, $name = null, bool $return_media = false, $extension = null, $type = null)
    {

        // Get file values
        // info('Inicia');
        if($extension)
            $ext = $extension;
        else
            $ext = $file->getClientOriginalExtension();

        if (!$name) {
            $namewithextension = $file->getClientOriginalName();
            $name = Str::slug(explode('.', $namewithextension)[0]);
        } else {
            $name = Str::slug($name);
        }

        $name = Str::of($name)->limit(200);
        $is_h5p = $ext == 'h5p';
        // Generate filename
        // if($ext == 'h5p'){
        //     $ext = 'zip';
        // }
        // rand(1000, 9999) old random
        $str_random = Str::random(10);
        $workspace_id = session('workspace')['id'] ?? NULL;

        // workspace creation reference
        $workspace_code = 'wrkspc-' . ($workspace_id ?? 'x');
        $name = $workspace_code . '-' . $name . '-' . date('YmdHis') . '-' . $str_random;
        $fileName = $is_h5p ? $name . '.zip' : $name . '.' . $ext;

        // Get file size, Laravel returns the value in bytes,
        // so converts the value to Kilobytes (as an integer)

        try {
            $size = round($file->getSize() / 1024);
        } catch (\Exception $exception) {
            $size = 0;
        }

        // Generate path according file extension

        $path = '';
        $uploaded = false;
        $extracted = false;

        $valid_ext1 = ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'];
        $valid_ext2 = ['mp4', 'webm', 'mov'];
        $valid_ext3 = ['mp3'];
        $valid_ext4 = ['pdf'];
        $valid_ext5 = ['zip', 'scorm','h5p']; // todo verificar esto: Los zip se suben en el storage local (del proyecto)
        $valid_ext6 = ['xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx','txt'];
        $valid_ext7 = ['ttf'];
        // $valid_ext7 = ['sdasd'];
        if (in_array(strtolower($ext), $valid_ext1)) {
            $path = 'images/' . $fileName;
        } else if (in_array(strtolower($ext), $valid_ext2)) {
            $path = 'videos/' . $fileName;
        } else if (in_array(strtolower($ext), $valid_ext3)) {
            $path = 'audio/' . $fileName;
        } else if (in_array(strtolower($ext), $valid_ext4)) {
            $path = 'files/' . $fileName;
        } else if (in_array(strtolower($ext), $valid_ext6)) {
            $path = 'office-files/' . $fileName;
        } else if (in_array(strtolower($ext), $valid_ext5)) {
            $name_scorm = $is_h5p ? ['nombre'=>$name] : $this->verify_scorm($file,$name);

            $temp_path = 'uploads-temp/scorm/' . $name;

            $extracted = Media::extractZipToTempFolder($file, $temp_path);
            if ($extracted) {

                // $new_folder = 'scorm/' . $name;
                $new_folder = $name;
                Media::uploadUnzippedFolderToBucket($temp_path, $new_folder,$is_h5p,$is_h5p);

                $name = $new_folder . '/' . $name_scorm['nombre'];
                $path = $is_h5p ? $path = 'h5p/' . $new_folder : get_media_url($name, 'cdn_scorm');
                $ext =  $is_h5p ? 'h5p' : 'scorm';
                // Delete temporal folder
                File::deleteDirectory(public_path($temp_path));


                $uploaded = true;
            }
        }else if (in_array(strtolower($ext), $valid_ext7)) {
            $path = 'fonts/' . $fileName;
        }
        // else if (in_array(strtolower($ext), $valid_ext7)) {
        //     $path = 'h5p/' . $fileName;
        // }
        // Upload to remote storage

        if (!$uploaded) {

            // $result = Storage::disk('s3')->put($path, file_get_contents($file));
            $result = Storage::disk('s3')->put($path, file_get_contents($file), 'public'); // temporal

            if ($result) {
                $uploaded = true;
            }
        }

        // Insert Media

        if ($uploaded) {
            $media = new \App\Models\Media();
            $media->title = $name;
            $media->file = $path;
            $media->ext = $ext;
            $media->size = $size;
            $media->workspace_id = $workspace_id;
            if($type)
                $media->type = $type;
            $media->save();
        }

        // Return media or path;
        if ($return_media)
            return $media;

        return $path;
    }
    protected function uploadMediaBase64($name, $path, $base64,$save_in_media=true,$status='public')
    {
        $exploded = explode(',', $base64, 2);
        $s3 = Storage::disk('s3')->put($path, base64_decode($exploded[1]), $status);
        $size = Storage::disk('s3')->size($path);

        try {
            $save_size = round(($size / 1024) / 1024);
        } catch (\Exception $exception) {
            $save_size = 0;
        }
        if(!$save_in_media){
            return $path;
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
    protected function validateStorageByWorkspace($files){

        // Reload workspace limits from database instead of using the data
        // from session, since is likely out-of-date
        $workspace = get_current_workspace();
        $workspace = Workspace::find($workspace->id);

        $workspace_current_storage = DashboardService::loadSizeWorkspaces([$workspace->id])->first();
        $workspace_current_storage = (int) $workspace_current_storage->medias_sum_size;

        // === workspace storage actual ===
        $total_current_storage = $workspace_current_storage;
        foreach ($files as $file) {
            if (is_string($file)) {
                info($file);
            } else {
                $total_current_storage += round($file->getSize() / 1024);
            }
        }

        $total_current_storage = formatSize($total_current_storage, parsed:false);
        // === workspace storage actual ===

        $total_storage_limit = $workspace->limit_allowed_storage ?? 0;
        $still_has_storage  = (
            ($total_current_storage['size_unit'] == 'Gb' &&
             $total_current_storage['size'] <= $total_storage_limit)
            || $total_current_storage['size_unit'] == 'Mb'
            || $total_current_storage['size_unit'] == 'Kb'
        );

        return  $still_has_storage;
    }
    protected function extractZipToTempFolder($file, $temp_path)
    {
        $zip = new ZipArchive();
        $res = $zip->open($file, ZipArchive::CREATE);

        if ($res === TRUE) {

            $zip->extractTo(public_path($temp_path));
            $zip->close();

            return true;
        }

        return false;
    }

    protected function uploadUnzippedFolderToBucket($temp_path, $new_folder,$is_h5p=false,$public=false)
    {
        $config = config('filesystems.disks.s3');

        $client = new S3Client([
            'key' => $config['key'],
            'secret' => $config['secret'],
            'version' => 'latest',
            'region' => $config['region'],
            'endpoint'    => 'https://sfo2.digitaloceanspaces.com',
            'options' => [
                'CacheControl' => 'max-age=25920000, no-transform, public',
            ]
        ]);

        $bucket = $is_h5p ? $config['bucket'] : $config['scorm']['bucket'] ;
        $keyPrefix = $is_h5p ? $config['root'].'/h5p/'.$new_folder . '/' : $config['scorm']['root'] . '/' . $new_folder . '/';
        $options =  array(
            'concurrency' => 20,
            'before' => function (\Aws\Command $command){
                $command['ACL'] = strpos($command['Key'], 'CONFIDENTIAL') === false
                    ? 'public-read'
                    : 'private';
            });
        // $options =  array(
        //     'concurrency' => 20,
        //     'before' => function (\Aws\Command $command) use($public) {
        //         if($public){
        //             $command['ACL'] = strpos($command['Key'], 'CONFIDENTIAL') === false
        //                 ? 'public-read'
        //                 : 'private';
        //         }else{
        //             $command['ACL'] =  'public-read';
        //         }
        // });
        // dd($options['before']);
        $client->uploadDirectory($temp_path, $bucket, $keyPrefix, $options);


    }

    public static function verify_scorm($file,$name){
        $zip = new ZipArchive();
        $zip->open($file);
        $find_main_file = false;
        $tipos_scorm = config('constantes.tipos_scorm');
        $nombre = '';
        //Search in main folder
        foreach ($tipos_scorm as $main_file_name) {
            if($zip->statName($main_file_name)){
                $nombre = $main_file_name;
                $find_main_file = true;
                if($main_file_name=='index.html'){
                    break;
                }
            }
        }
        //if not found in main folder, verify only in subfolders
        if (!$find_main_file) {
            for( $i = 0; $i < $zip->numFiles; $i++ ){
                $stat = $zip->statIndex($i);
                foreach ($tipos_scorm as $main_file_name) {
                    if (count(explode('/',$stat['name']))==2 && str_contains($stat['name'], '/'.$main_file_name)) {
                        $nombre = $stat['name'];
                        $find_main_file = true;
                        if($main_file_name=='index.html'){
                            break;
                        }
                    }
                }
            }
        }
        $zip->close();
        return compact('nombre','find_main_file');
    }

    protected function requestUploadFile($data, $field, $return_media = false, $name_file = null, $ext = null, $type = null)
    {
        if (!empty($data[$field])) {

            $data[$field] = $data[$field];

        } else {

            $file_field = 'file_' . $field;

            if (!empty($data[$file_field])) {
                $path = Media::uploadFile($data[$file_field], $name_file, $return_media, $ext, $type);
                $data[$field] = $path;
            } else {
                $data[$field] = null;
            }
        }

        return $data;

        // if (!empty($data[$field])) {
        //     $path = Media::uploadFile($data[$field]);

        //     $data[$field] = $path;
        // } else {
        //     $multimedia_field = 'multimedia_' . $field;

        //     if (!empty($data[$multimedia_field])) {
        //         $data[$field] = $data[$multimedia_field];
        //     }
        // }

        // return $data;
    }


    protected function requestUploadFileOnly($data, $field)
    {
        $file_field = 'file_' . $field;

        if (!empty($data[$file_field]) && is_string($data[$file_field])) {
            $data[$file_field] = $data[$file_field];
        } else {
            if (!empty($data[$file_field])) {
                $path = Media::uploadFile($data[$file_field]);
                $data[$file_field] = $path;
            } else {
                $data[$file_field] = null;
            }
        }

        return $data;
    }

    protected function requestUploadFileForId($data, $field, $nombre = null)
    {
        $multimedia_field = 'file_' . $field;
        // $multimedia_field = 'multimedia_' . $field;

        if (!empty($data[$multimedia_field])) {
            //            dd(1);
            $media = Media::uploadFile($data[$multimedia_field], $nombre, true);
            $data[$field . '_id'] = $media->id;
        } else {
            $multimedia_field = $field . '_id';

            if (!empty($data[$multimedia_field])) {
                // $media = Media::uploadFile($data[$field], $nombre, true);
                //                dd(2);

                $data[$field . '_id'] = $data[$multimedia_field] ?? NULL;
            } else {
                if (!empty($data[$field])) {
                    //                    dd(3);

                    $media = Media::where('file', $data[$field])->first();
                    $data[$field . '_id'] = $media->id ?? NULL;
                } else {
                    //                    dd(4);

                    $data[$field . '_id'] = NULL;
                }
            }
        }

        return $data;
    }

    protected function queryGrid($request)
    {
        $session = session()->all();
        $workspace = $session['workspace'];

        $query = Media::query()
                      ->where('workspace_id', $workspace->id);

        if ($request->q) {
            $q = trim($request->q);
            $slug = Str::slug($q);
            $query->where('title', 'like', "%{$slug}%");
            $query->orWhere('title', 'like', "%{$q}%");
        }

        if ($request->tipo) {
            if ($request->tipo[0] != null) {

                $exts = [];
                $extensions = config('constantes.extensiones');

                foreach ($request->tipo as $tipo) {
                    if ($tipo and isset($extensions[$tipo])) {
                        foreach ($extensions[$tipo] as $ext) {
                            $exts[] = $ext;
                        }
                    }
                }

                $query->whereIn('ext', $exts);
            }
        }

        if ($request->fecha) {

            // Only start date

            if (isset($request->fecha[0]) && !isset($request->fecha[1])) {
                $query->whereDate('created_at', '=', $request->fecha[0]);
            }

            // Date range

            if (isset($request->fecha[0]) && isset($request->fecha[1])) {
                $query->whereBetween('created_at', [$request->fecha[0], $request->fecha[1]]);
            }
        }

        if ($request->order_by) {
            $query->orderBy($request->order_by, 'DESC');
        } else {
            $query->orderBy('created_at', 'DESC');
        }

        if ($request->workspace_id)
            $query->where('workspace_id', $request->workspace_id);

        return $query->paginate($request->paginate);
    }

    public function getMediaType($ext)
    {
        $type = '';
        $extensions = config('constantes.extensiones');
        $extensions_es = config('constantes.extensiones_es');

        foreach ($extensions as $type_ext => $exts) {
            if (in_array(strtolower($ext), $exts, false)) {
                $type = $type_ext;
                break;
            }
        }

        return $extensions_es[$type] ?? '-';
    }

    public function getPreview()
    {
        $ext = $this->ext;
        $valid_ext1 = array("jpeg", "jpg", "png", "gif", "svg", "webp");
        $valid_ext2 = array("mp4", "webm", "mov");
        $valid_ext3 = array("mp3");
        $valid_ext4 = array("pdf");
        $valid_ext5 = array("zip", "scorm"); // Los zip se suben en el storage local (del proyecto)
        $valid_ext6 = array('xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx');

        $preview = '';

        if (in_array(strtolower($ext), $valid_ext1)) {
            $preview = FileService::generateUrl($this->file);
        } else if (in_array(strtolower($ext), $valid_ext2)) {
            $preview = asset(self::DEFAULT_VIDEO_IMG);
        } else if (in_array(strtolower($ext), $valid_ext3)) {
            $preview = asset(self::DEFAULT_AUDIO_IMG);
        } else if (in_array(strtolower($ext), $valid_ext4)) {
            $preview = asset(self::DEFAULT_PDF_IMG);
        } else if (in_array(strtolower($ext), $valid_ext6)) {
            $preview = asset(self::DEFAULT_PDF_IMG);
        } else if (in_array(strtolower($ext), $valid_ext5)) {
            $preview = asset(self::DEFAULT_SCORM_IMG);
        } else {
            $preview = asset(self::DEFAULT_SCORM_IMG);
        }

        return $preview;
    }

    public function getModalHtml()
    {
        $media = $this;
        $view = (string)view('media.modal-data', compact('media'))->render();

        return $view;
    }

    public function streamDownloadFile()
    {
        $filename = Str::after($this->file, '/');
        // $stream = Storage::readStream($this->file);

        $response = response()->streamDownload(function () {

            $path = get_media_url($this->file);

            if ($stream = fopen($path, 'r')) {

                while (!feof($stream)) {
                    echo fread($stream, 1024);
                    flush();
                }

                fclose($stream);
            }
        }, $filename);

        if (ob_get_level()) ob_end_clean();

        return $response;
    }

    protected function generateNameFile($name, $ext) {

        $name = Str::of($name)->limit(200);
        $str_random = Str::random(15);
        $workspace_id = session('workspace')['id'] ?? NULL;

        // workspace creation reference
        $workspace_code = 'wrkspc-' . ($workspace_id ?? 'x');
        $name = $workspace_code . '-' . $name . '-' . date('YmdHis') . '-' . $str_random;
        $fileName = $name . '.' . $ext;

        return $fileName;
    }

    // public function getViewDetail($preview)
    // {
    //     $media = $this;
    //     $files = [];

    //     $configs = Abconfig::where('logo', $media->file)->orWhere('isotipo', $media->file)->get();

    //     $this->getLinkedData($files, $configs, 'abconfigs', 'Módulo', 'etapa');

    //     $anuncios = Anuncio::where('imagen', $media->file)->get();

    //     $this->getLinkedData($files, $anuncios, 'anuncios', 'Anuncio', 'nombre');

    //     $cursos = Curso::where('imagen', $media->file)->get();
    //     $this->getLinkedData($files, $cursos, 'cursos', 'Curso', 'nombre');

    //     // $carreras = Carrera::where('imagen', $media->file)->orWhere('malla_archivo', $media->file)->get();
    //     // $this->getLinkedData($files, $carreras, 'carreras', 'Carrera', 'nombre');

    //     $categorias = Categoria::where('imagen', $media->file)->get();
    //     $this->getLinkedData($files, $categorias, 'categorias', 'Categoría', 'nombre');

    //     $posteos = Posteo::where('imagen', $media->file)->orWhere('archivo', $media->file)->orWhere('video', $media->file)->orWhere('media', 'like', "%{$media->file}%")->get();
    //     $this->getLinkedData($files, $posteos, 'posteos', 'Tema', 'nombre');

    //     $view = (string) view('media.show', compact('media', 'preview', 'files'))->render();

    //     return $view;
    // }

    // public function getLinkedData(&$files, $data, $key, $name, $field)
    // {
    //     if($data->count())
    //     {
    //         $files[$key]['name'] = $name;

    //         foreach ($data as $row){

    //             switch ($key) {
    //                 case 'posteos':
    //                     $files[$key]['items'][] = ['route' => route($key . '.edit', [ $row->curso_id, $row->id]), 'name' => $row->$field];
    //                     break;
    //                 case 'cursos':
    //                     $files[$key]['items'][] = ['route' => route($key . '.edit', [$row->categoria_id, $row->id]), 'name' => $row->$field];
    //                     break;
    //                 // case 'carreras':
    //                 //     $files[$key]['items'][] = ['route' => route($key . '.edit', [$row->config_id, $row->id]), 'name' => $row->$field];
    //                 //     break;
    //                 case 'categorias':
    //                     # code...
    //                     $files[$key]['items'][] = ['route' => route($key . '.edit', [$row->config_id, $row->id]), 'name' => $row->$field];
    //                     break;

    //                 default:
    //                     # code...
    //                     $files[$key]['items'][] = ['route' => route($key . '.edit', $row->id), 'name' => $row->$field];
    //                     break;
    //             }

    //         }
    //     }
    // }
    protected function createZipFromStorage($files){
        $rutas =  $files['rutas'];
        $file_zip_name = $files['file_zip_name'];
        $zip = new ZipArchive;
        $mimeTypes = new MimeTypes();
        if (true === ($zip->open($file_zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE))) {
            foreach ($rutas as $ruta) {
                if(Storage::disk('s3')->exists($ruta)){
                    $file_content = Storage::disk('s3')->get($ruta);
                    $file_extension = $mimeTypes->getExtensions(Storage::disk('s3')->mimeType($ruta));
                    $file_extension = isset($file_extension[0]) ? $file_extension[0] : 'txt';
                    $basename = pathinfo($ruta, PATHINFO_FILENAME) . '.' . $file_extension;
                    // dd($file_content,$basename,$ruta);
                    $zip->addFromString($basename, $file_content);
                }
            }
        }
        $zip->close();
        return $file_zip_name;
    }

    protected function downloadFilesInZip($data,$zipFileName = 'download-files'){
        // Crear un archivo ZIP temporal
        $zip = new ZipArchive();
        if ($zip->open($zipFileName, ZipArchive::CREATE) !== true) {
            return false;
        }
        $mimeTypes = new MimeTypes();
        foreach ($data as $item) {
            // Añadir la carpeta al archivo ZIP
            $folderName = $item['folder_name'];
            $zip->addEmptyDir($folderName);

            // Añadir archivos a la carpeta del ZIP
            foreach ($item['routes'] as $route) {
                if(Storage::disk('s3')->exists($route)){
                    $file_content = Storage::disk('s3')->get($route);
                    $file_extension = $mimeTypes->getExtensions(Storage::disk('s3')->mimeType($route));
                    $file_extension = isset($file_extension[0]) ? $file_extension[0] : 'txt';
                    $basename = pathinfo($route, PATHINFO_FILENAME) . '.' . $file_extension;
                    $zip->addFromString($folderName . '/' . $basename, $file_content);
                }
            }
        }
        $zip->close();
        return $zipFileName;
    }
}
