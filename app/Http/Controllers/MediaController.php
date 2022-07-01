<?php

namespace App\Http\Controllers;

use App\Http\Requests\Multimedia\MultimediaUploadFileRequest;
use App\Http\Resources\Multimedia\MultimediaSearchResource;
use Illuminate\Http\Request;
use App\Models\Media;
use DB;
use Storage;
use ZipArchive;

class MediaController extends Controller
{

    public function index(Request $request)
    {
        if ($request->has('q')) {
            $question = $request->input('q');
            $medias = Media::where('title', 'like', '%' . $question . '%')->orderBy('created_at', 'DESC')->paginate(54);
        } else {
            $medias = Media::orderBy('created_at', 'DESC')->paginate(54);
        }

        return view('media.index', compact('medias'));
    }

    public function show(Media $multimedia)
    {
        $size = $multimedia->size === 0.0 ? '-' : $multimedia->size;
        $multimedia->file = $multimedia->ext === 'scorm' ? $multimedia->file : env('DO_URL') . '/' . $multimedia->file;
        $multimedia->preview = env('DO_URL') . '/' . $multimedia->getPreview();
        $multimedia->type = $multimedia->getMediaType($multimedia->ext);
        $multimedia->created = $multimedia->created_at->format('d/m/Y');
        $multimedia->size = $size;
        return $this->success([
            'multimedia' => $multimedia
        ]);
    }

    public function modal_list_media_asigna(Request $request)
    {
        $ext_image = array("jpeg", "jpg", "png", "gif", "svg", "webp");
        $ext_video = array("mp4", "webm", "mov");
        $ext_audio = array("mp3");
        $ext_pdf = array("pdf");
        $ext_scorm = array("scorm", "zip");
        $exts = array_merge($ext_image, $ext_video, $ext_audio, $ext_pdf, $ext_scorm);

        if ($request->has('tipo')) {
            $tipo = $request->input('tipo');

            if ($tipo == 'image') {
                $exts = $ext_image;
            } else if ($tipo == 'video') {
                $exts = $ext_video;
            } else if ($tipo == 'audio') {
                $exts = $ext_audio;
            } else if ($tipo == 'pdf') {
                $exts = $ext_pdf;
            } else if ($tipo == 'scorm') {
                $exts = $ext_scorm;
            }
        }

        if ($request->has('q')) {
            $question = $request->input('q');
            $medias = Media::whereIn('ext', $exts)->where('title', 'like', '%' . $question . '%')->orderBy('created_at', 'DESC')->paginate(18);
        } else {
            $medias = Media::whereIn('ext', $exts)->orderBy('created_at', 'DESC')->paginate(18);
        }

        return $medias;
    }

    public function create()
    {
        return view('media.create');
    }

    /*
    File upload
    */
    public function fileupload(Request $request)
    {

        if ($request->hasFile('file')) {

            $file = $request->file('file');
            $ext = $file->getClientOriginalExtension();
            $namewithextension = $file->getClientOriginalName();
            $name = str_slug(explode('.', $namewithextension)[0]);
            $name = $name . "_" . rand(100, 300);
            $fileName = $name . '.' . $ext;

            $path = '';
            $uploaded = false;

            $valid_ext1 = array("jpeg", "jpg", "png", "gif", "svg", "webp");
            $valid_ext2 = array("mp4", "webm", "mov");
            $valid_ext3 = array("mp3");
            $valid_ext4 = array("pdf");
            $valid_ext5 = array("zip", "scorm"); // Los zip se suben en el storage local (del proyecto)

            if (in_array(strtolower($ext), $valid_ext1)) {
                $path = 'images/' . $fileName;
            } else if (in_array(strtolower($ext), $valid_ext2)) {
                $path = 'videos/' . $fileName;
            } else if (in_array(strtolower($ext), $valid_ext3)) {
                $path = 'audio/' . $fileName;
            } else if (in_array(strtolower($ext), $valid_ext4)) {
                $path = 'files/' . $fileName;
            } else if (in_array(strtolower($ext), $valid_ext5)) {
                // $path = 'zip/'.$fileName;
                $localpath = 'uploads/scorm/' . $name;
                $zip = new ZipArchive();
                $res = $zip->open($file, ZipArchive::CREATE);
                if ($res === TRUE) {
                    $zip->extractTo(public_path($localpath));
                    $zip->close();

                    $uploaded = true;
                    $path = url($localpath);
                    $ext = 'scorm';
                }
            }

            // Upload to remote storage
            if (!$uploaded) {
                // $file->move($path, $fileName);
                if (Storage::disk('do_spaces')->put($path, file_get_contents($file), 'public')) {
                    $uploaded = true;
                }
            }
            // Insert Media
            if ($uploaded) {
                $media = new Media;
                $media->title = $name;
                // $media->file = $fileName;
                $media->file = $path;
                $media->ext = $ext;
                $media->save();
            }

        }
    }

    public function eliminar(Request $request)
    {
        $id = $request->id;
        $media = Media::find($id);

        $ext = $media->ext;
        $valid_ext1 = array("jpeg", "jpg", "png", "gif", "svg", "webp");
        $valid_ext2 = array("mp4", "webm", "mov");
        $valid_ext3 = array("mp3");
        $valid_ext4 = array("pdf");
        $valid_ext5 = array("zip", "scorm");

        $path = "";

        if (in_array(strtolower($ext), $valid_ext1)) {
            $path = 'images/' . $media->file;
        } else if (in_array(strtolower($ext), $valid_ext2)) {
            $path = 'videos/' . $media->file;
        } else if (in_array(strtolower($ext), $valid_ext3)) {
            $path = 'audio/' . $media->file;
        } else if (in_array(strtolower($ext), $valid_ext4)) {
            $path = 'files/' . $media->file;
        } else if (in_array(strtolower($ext), $valid_ext5)) {
            // \File::deleteDirectory(public_path('uploads/scorm/'.$media->file));
            \File::deleteDirectory(public_path('uploads/scorm/' . $media->title));
        }

        if ($path != '') {
            Storage::disk('do_spaces')->delete($path);
        }
        $media->delete();

//        return $id;
        return $this->success([
            'msg' => 'Archivo eliminado correctamente.'
        ]);
    }

    //
    public function fill_media_from_space()
    {

        $files = Storage::disk('do_spaces')->allFiles('');

        foreach ($files as $file) {
            $fileName = basename($file);
            $arr_nombre_ext = explode('.', $fileName);

            $ext = (isset($arr_nombre_ext[1])) ? $arr_nombre_ext[1] : "";

            $media = new Media;
            $media->title = $fileName;
            $media->file = $file;
            $media->ext = $ext;
            $media->save();
        }

        return "Archivos insertados en tabla media desde space.";
    }

    public function search(Request $request)
    {
        $medias = Media::queryGrid($request, 12);

        MultimediaSearchResource::collection($medias);

        return response()->json(compact('medias'), 200);
    }

    public function downloadExternalFile(Media $media)
    {
        return $media->streamDownloadFile();
    }

    public function upload(MultimediaUploadFileRequest $request)
    {
        $data = $request->validated();

        foreach ($data['file'] as $file) {
            Media::uploadFile($file);
        }

        return $this->success([
            'msg' => 'Archivo(s) subido(s) correctamente.'
        ]);
    }
}
