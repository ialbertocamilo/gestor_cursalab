<?php

namespace App\Http\Controllers;

use App\Http\Requests\Multimedia\MultimediaUploadFileRequest;
use App\Http\Resources\Multimedia\MultimediaSearchResource;
use App\Models\Media;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{

    /**
     * Return view to list media
     * @param Request $request
     * @return Application|Factory|View
     */
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

    /**
     * Generate media details
     * @param Media $multimedia
     * @return JsonResponse
     */
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

    /**
     * Retrieve media list according type
     * @param Request $request
     * @return mixed
     */
    public function modal_list_media_asigna(Request $request)
    {

        $ext_image = ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'];
        $ext_video = ['mp4', 'webm', 'mov'];
        $ext_audio = ['mp3'];
        $ext_pdf = ['pdf'];
        $ext_scorm = ['scorm', 'zip'];
        $exts = array_merge(
            $ext_image, $ext_video, $ext_audio, $ext_pdf, $ext_scorm
        );

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
            $medias = Media::whereIn('ext', $exts)
                            ->where('title', 'like', '%' . $question . '%')
                            ->orderBy('created_at', 'DESC')
                            ->paginate(18);
        } else {

            $medias = Media::whereIn('ext', $exts)
                            ->orderBy('created_at', 'DESC')
                            ->paginate(18);
        }

        return $medias;
    }

    /**
     * Return view to upload media
     * @return Application|Factory|View
     */
    public function create()
    {
        return view('media.create');
    }

    /**
     * File upload
     * @param Request $request
     * @return void
     */
    public function fileupload(Request $request): void
    {

        // If file is not defined, stop method execution

        if (!$request->hasFile('file')) return;

        // Upload file

        Media::uploadFile($request->file('file'));
    }

    /**
     * Upload multiple files
     * @param MultimediaUploadFileRequest $request
     * @return JsonResponse
     */
    public function upload(MultimediaUploadFileRequest $request)
    {
        $data = $request->validated();

        // Upload files one by one

        foreach ($data['file'] as $file) {
            Media::uploadFile($file);
        }

        return $this->success([
            'msg' => 'Archivo(s) subido(s) correctamente.'
        ]);
    }

    /**
     * Deletes a file
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $request->id;
        $media = Media::find($id);

        $ext = $media->ext;
        $isScorm = in_array(strtolower($ext), ['zip', 'scorm']);

        // Deletes scrom directory

        if ($isScorm) {

            File::deleteDirectory(
                public_path('uploads/scorm/' . $media->title)
            );

        } else {

            // Deletes file from remote storage

            Storage::disk('do_spaces')
                   ->delete($media->file);
        }

        // Deletes file from database

        $media->delete();


        return $this->success([
            'msg' => 'Archivo eliminado correctamente.'
        ]);
    }

    /**
     * Search media according search term
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $medias = Media::queryGrid($request, 12);

        MultimediaSearchResource::collection($medias);

        return response()->json(compact('medias'), 200);
    }

    /**
     * Download external file
     * @param Media $media
     * @return StreamedResponse
     */
    public function downloadExternalFile(Media $media): StreamedResponse
    {
        return $media->streamDownloadFile();
    }

    // todo: remove this method since it is not used anywhere
//    public function fill_media_from_space()
//    {
//
//        $files = Storage::disk('do_spaces')->allFiles('');
//
//        foreach ($files as $file) {
//            $fileName = basename($file);
//            $arr_nombre_ext = explode('.', $fileName);
//
//            $ext = (isset($arr_nombre_ext[1])) ? $arr_nombre_ext[1] : "";
//
//            $media = new Media;
//            $media->title = $fileName;
//            $media->file = $file;
//            $media->ext = $ext;
//            $media->save();
//        }
//
//        return "Archivos insertados en tabla media desde space.";
//    }
}
