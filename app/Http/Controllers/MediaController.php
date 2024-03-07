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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Services\FileService;

class MediaController extends Controller
{

    /**
     * Return view to list media
     *
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
     *
     * @param Media $multimedia
     * @return JsonResponse
     */
    public function show(Media $multimedia)
    {


        $type = $multimedia->getMediaType($multimedia->ext);
        $type = $type . ' (' . strtoupper($multimedia->ext) . ')';

        $multimedia['file_url'] = $multimedia->ext === 'scorm'
            ? $multimedia->file
            : FileService::generateUrl($multimedia->file);

        $multimedia->preview = $multimedia->getPreview();
        $multimedia->type = $type;
        $multimedia->created = $multimedia->created_at->format('d/m/Y');
        $multimedia->formattedSize = FileService::formatSize($multimedia->size);

        $multimedia['sections'] = [
            'modules' => $multimedia->modulesByFile(),
            'schools' => $multimedia->schoolsByFile(),
            'courses' => $multimedia->coursesByFile(),
            'topics' => $multimedia->topicsByFile(),
            'announcements' => $multimedia->announcementsByFile(),
            'videotecas' => $multimedia->videotecasByFile(),
            'vademecums' => $multimedia->vademecumsByFile()
        ];

        return $this->success([
            'multimedia' => $multimedia
        ]);
    }

    /**
     * Retrieve media list according type
     *
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
     *
     * @return Application|Factory|View
     */
    public function create()
    {
        return view('media.create');
    }

    /**
     * File upload
     *
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
     *
     * @param MultimediaUploadFileRequest $request
     * @return JsonResponse
     */
    public function upload(MultimediaUploadFileRequest $request)
    {
        $data = $request->validated();

        // Upload files one by one

        $hasStorageAvailable = Media::validateStorageByWorkspace($data['file']);
        if ($hasStorageAvailable) {

            foreach ($data['file'] as $file) {
                Media::uploadFile($file);
            }

            return $this->success([
                'msg' => 'Archivo(s) subido(s) correctamente.'
            ]);

        } else {

            return response()->json([
                'msg' => config('errors.limit-errors.limit-storage-allowed')
            ], 403);
        }
    }

    /**
     * Deletes a file
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Media $media, Request $request): JsonResponse
    {
//        $id = $request->id;
//        $media = Media::find($id);
//
//        $ext = $media->ext;
//        $isScorm = in_array(strtolower($ext), ['zip', 'scorm']);
//
//        // Deletes scrom directory
//
//        if ($isScorm) {
//
//            File::deleteDirectory(
//                public_path('uploads/scorm/' . $media->title)
//            );
//
//        } else {
//
//            // Deletes file from remote storage
//
//            Storage::disk('do_spaces')
//                   ->delete($media->file);
//        }

        // Deletes file from database

        $media->delete();


        return $this->success([
            'msg' => 'Archivo eliminado correctamente.'
        ]);
    }

    /**
     * Process request to load records filtered according search term
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $workspace = get_current_workspace();
        $request->merge(['workspace_id' => $workspace?->id]);

        $medias = Media::queryGrid($request, 12);

        MultimediaSearchResource::collection($medias);

        return response()->json(compact('medias'), 200);
    }

    /**
     * Process request to download external file
     *
     * @param Media $media
     * @return StreamedResponse
     */
    public function downloadExternalFile(Media $media): StreamedResponse
    {
        return $media->streamDownloadFile();
    }

    /**
     * Process request to download media topic file
     *
     * @param Media $media
     * @return StreamedResponse
     */
    public function downloadMediaTopicExternalFile($media_topic_id): StreamedResponse
    {
        $media_topic = DB::table('media_topics')->where('id', $media_topic_id)->first();

        if (!$media_topic) abort(404);

        $filename = Str::after($media_topic->value, '/');
        $pathInfo = pathinfo($filename);
        // $stream = Storage::readStream($this->file);

        // Set content type

        $headers = [];
        if (isset($pathInfo['extension'])) {
            if (strtolower($pathInfo['extension']) === 'pdf') {
                $headers = ['Content-Type' => 'application/pdf'];
            }
        }

        $response = response()->streamDownload(function () use($media_topic){

//            $path = Storage::url($media_topic->value);
            $path = get_media_url($media_topic->value);

            if ($stream = fopen($path, 'r')) {

                while (!feof($stream)) {
                    echo fread($stream, 1024);
                    flush();
                }

                fclose($stream);
            }
        }, $filename, $headers);

        if (ob_get_level()) ob_end_clean();

        return $response;
    }

    // todo: remove this method since it is not used anywhere
//    public function fill_media_from_space()
//    {
//
//        $files = Storage::allFiles('');
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
