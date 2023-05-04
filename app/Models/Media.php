<?php

namespace App\Models;

use App\Services\FileService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Aws\S3\S3Client;
use ZipArchive;


class Media extends BaseModel
{

    use SoftDeletes;


    const DEFAULT_AUDIO_IMG = "images/default-audio-img_119.png";
    const DEFAULT_SCORM_IMG = "images/default-scorm-img_116_360.png";
    const DEFAULT_VIDEO_IMG = "images/default-video-img_285_360.png";
    const DEFAULT_PDF_IMG = "images/default-pdf-img_210.png";

    protected $table = 'media';

    protected $fillable = [
        'title', 'description', 'file', 'ext', 'status', 'external_id', 'size', 'workspace_id'
    ];

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
    protected function uploadFile($file, $name = null, bool $return_media = false)
    {

        // Get file values
        // info('Inicia');
        $ext = $file->getClientOriginalExtension();

        if (!$name) {
            $namewithextension = $file->getClientOriginalName();
            $name = Str::slug(explode('.', $namewithextension)[0]);
        } else {
            $name = Str::slug($name);
        }

        $name = Str::of($name)->limit(200);

        // Generate filename

        // rand(1000, 9999) old random
        $str_random = Str::random(15);
        $workspace_id = session('workspace')['id'] ?? NULL;

        // workspace creation reference
        $workspace_code = 'wrkspc-' . ($workspace_id ?? 'x');
        $name = $workspace_code . '-' . $name . '-' . date('YmdHis') . '-' . $str_random;
        $fileName = $name . '.' . $ext;

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
        $valid_ext5 = ['zip', 'scorm']; // todo verificar esto: Los zip se suben en el storage local (del proyecto)
        $valid_ext6 = ['xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx'];

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

            $name_scorm = $this->verify_scorm($file,$name);

            $temp_path = 'uploads-temp/scorm/' . $name;
            $extracted = Media::extractZipToTempFolder($file, $temp_path);

            if ($extracted) {

                $new_folder = 'scorm/' . $name;

                Media::uploadUnzippedFolderToBucket($temp_path, $new_folder);

                $name = $new_folder . '/' . $name_scorm['nombre'];
                $path = get_media_url($name, 'cdn_scorm');
                $ext = 'scorm';

                $uploaded = true;
            }
        }

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
            $media->save();
        }

        // Return media or path;
        if ($return_media)
            return $media;

        return $path;
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

    protected function uploadUnzippedFolderToBucket($temp_path, $new_folder)
    {
        $config = config('filesystems.disks.s3');

        $client = new S3Client([
            'key' => $config['key'],
            'secret' => $config['secret'],
            'version' => 'latest',
            'region' => $config['region'],
            'options' => [
                'CacheControl' => 'max-age=25920000, no-transform, public', 
            ]
        ]);

        $bucket = $config['scorm']['bucket'];
        $keyPrefix = $config['scorm']['root'] . '/' . $new_folder . '/';
        $options = array(
            'concurrency' => 20,
        );

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

    protected function requestUploadFile($data, $field)
    {
        if (!empty($data[$field])) {

            $data[$field] = $data[$field];

        } else {

            $file_field = 'file_' . $field;

            if (!empty($data[$file_field])) {
                $path = Media::uploadFile($data[$file_field]);
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
            $title = Str::slug(trim($request->q));
            $query->where('title', 'like', "%{$title}%");
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
            $preview = $this->file;
        } else if (in_array(strtolower($ext), $valid_ext2)) {
            $preview = FileService::generateUrl(self::DEFAULT_VIDEO_IMG);
        } else if (in_array(strtolower($ext), $valid_ext3)) {
            $preview = FileService::generateUrl(self::DEFAULT_AUDIO_IMG);
        } else if (in_array(strtolower($ext), $valid_ext4)) {
            $preview = FileService::generateUrl(self::DEFAULT_PDF_IMG);
        } else if (in_array(strtolower($ext), $valid_ext6)) {
            $preview = FileService::generateUrl(self::DEFAULT_PDF_IMG);
        } else if (in_array(strtolower($ext), $valid_ext5)) {
            $preview = FileService::generateUrl(self::DEFAULT_SCORM_IMG);
        } else {
            $preview = FileService::generateUrl(self::DEFAULT_SCORM_IMG);
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

}
