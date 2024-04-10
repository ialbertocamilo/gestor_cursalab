<?php

namespace App\Http\Controllers;

use App\Http\Resources\DiplomaSearchResource;
use App\Models\Ambiente;
use App\Models\Media;
use App\Models\User;
use App\Models\Course;
use App\Models\SummaryCourse;
use App\Models\{ Certificate as Diploma, Process, Stage};
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DiplomaController extends Controller
{
    public function __construct()
    {
        if (!\Session::has('config')) {
            $data = DB::table('config_general')->first();
            session(['config' => $data]);
        }
    }

    public function show(){
        return view('diplomas.index');
    }

    public function search(Request $request)
    {
        $diplomas = Diploma::search($request);
        DiplomaSearchResource::collection($diplomas);

        return $this->success($diplomas);
    }

    public function getMediaStreamBase64($media_id)
    {
        $media = Media::find($media_id);
        $file = str_replace(" ", "%20", $media->file);
        $image = Storage::disk('s3')->get($file);
        return "data:image/png;base64,".base64_encode($image);
    }

    public function searchDiploma(Diploma $diploma)
    {
        // === fondo de plantilla ===
        $info_bg_decoded = json_decode($diploma->info_bg);
        $info_bg_decoded->src = $this->getMediaStreamBase64($info_bg_decoded->media_id);

        // === partes de plantilla ===
        $s_objects_decoded = json_decode($diploma->s_objects);
        $s_objects_decoded = collect($s_objects_decoded);

        $array_medias = $s_objects_decoded->where('type', 'image');
        $array_text = $s_objects_decoded->where('type', 'i-text');

        foreach ($array_medias as $media_row ) {
            // === media_id ===
            $media_row->src = $this->getMediaStreamBase64($media_row->media_id);
        }

        $diploma['s_object_bg'] = $info_bg_decoded;
        $diploma['s_objects_images'] = $array_medias;
        $diploma['s_objects_text'] = $array_text;

        // === imagen plantilla completa ===
        // info(['diploma' => $diploma->toArray() ]);
        $file = str_replace(" ", "%20", $diploma->path_image);

        $plantilla = Storage::disk('s3')->get($file);
        $plantilla = "data:image/png;base64," . base64_encode($plantilla);

        return response()->json(compact('diploma', 'plantilla'));
    }

    public function update(Diploma $diploma, Request $request)
    {
        $images_base64 = Diploma::getBasesFromImages($request->edit_plantilla);

        $objects = collect($request->info['objects']);
        $background = $request->info['backgroundImage'];

        $status = Diploma::storeRequest($request->nombre_plantilla, $background, $objects, $diploma, $images_base64);

        if($status)
        {
            $model_id = $request->model_id;
            $model_type = $request->model_type;
            if($model_type == 'Process') {
                $process = Process::where('id', $model_id)->first();
                if($process) {
                    $process->certificate_template_id = $diploma?->id;
                    $process->save();
                }
            }
            else if($model_type == 'Stage') {
                $stage = Stage::where('id', $model_id)->first();
                if($stage) {
                    $stage->certificate_template_id = $status;
                    $stage->save();
                }
            }

        }

        return response()->json(['error' => !$status]);
    }

    public function save(Request $request)
    {
        $objects = collect($request->info['objects']);
        $background = $request->info['backgroundImage'];

        $status = Diploma::storeRequest($request->nombre_plantilla, $background, $objects);

        if($status)
        {
            $model_id = $request->model_id;
            $model_type = $request->model_type;
            if($model_type == 'Process') {
                $process = Process::where('id', $model_id)->first();
                if($process) {
                    $process->certificate_template_id = $status;
                    $process->save();
                }
            }
            else if($model_type == 'Stage') {
                $stage = Stage::where('id', $model_id)->first();
                if($stage) {
                    $stage->certificate_template_id = $status;
                    $stage->save();
                }
            }
        }

        return response()->json(['error' => !$status]);
    }

    public function get_diploma($pathImage, $d_per, $bg_info, $real_info)
    {
        $e_dinamics = $d_per;
        $x = $bg_info['left'];
        $y = $bg_info['top'];

        $pathImage = str_replace(" ", "%20", $pathImage);
        $headers = get_headers(get_media_url($pathImage));

        if ($headers && strpos($headers[0], "200 OK") !== false) {

            $image = file_get_contents(get_media_url($pathImage));
            $image = imagecreatefromstring($image);
            $width = imagesx($image);

            $bg_info['image_width'] = $width;

            $image = Diploma::setDynamicsToImage($image, $e_dinamics, $bg_info, $real_info);
            $preview = Diploma::jpg_to_base64($image);

            return $preview;
        }

        return abort(404);
    }

    public function get_preview_data(Request $request)
    {
        $data = $request->get('info');
        $zoom = $request->get('zoom');
        $response = $request->get('response');
        $user_data = $request->get('user_data');

        $c_data = collect($data['objects']);
        $background = $data['backgroundImage'];

        $e_statics = $c_data->where('static', true);
        $e_dinamics = $c_data->where('static', false);

        if ($background) {
            $image = Diploma::image_create($background['src'],1,1,1,1);
            $x = $background['left'];
            $y = $background['top'];

            $background['image_width'] = $background['width'];

            foreach ($e_statics as $e_static) {
                switch ($e_static['type']) {
                    case 'i-text':

                        //  === para el font ===
                        $fontName = 'calisto-mt.ttf';
                        if ($e_static['fontStyle'] === 'italic' && $e_static['fontWeight'] === 'bold') {
                            $fontName = 'calisto-mt-bold-italic.ttf';
                        }else if($e_static['fontStyle'] === 'italic') {
                            $fontName = 'calisto-mt-italic.ttf';
                        }else if($e_static['fontWeight'] === 'bold') {
                            $fontName = 'calisto-mt-bold.ttf';
                        }

                        $font = realpath('.').'/fonts/diplomas/'.$fontName;
                        //  === para el font ===

                        $rgb = Diploma::convertHexadecimalToRGB($e_static['fill']);
                        $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);

                        // resize font;
                        $divideSize = $e_static['fontSize'] / 3.3;
                        $fontsize = $e_static['fontSize'] - $divideSize;

                        imagettftext(
                            $image,
                            $fontsize,
                            0,
                            $e_static['left'] - $x,
                            $e_static['top'] - $y + $fontsize,
                            $color,
                            $font,
                            utf8_decode($e_static['text'])
                        );

                    break;
                    case 'image':

                        $image2 = Diploma::image_create($e_static['src'], $e_static['scaleX'], $e_static['scaleY'], $e_static['width'], $e_static['height']);

                        Diploma::imagecopymerge_alpha(
                            $image, // destino base
                            $image2, // fuente base
                            $e_static['left']-$x,
                            $e_static['top']-$y,
                            0,
                            0,
                            $e_static['width']*$e_static['scaleX'],
                            $e_static['height']*$e_static['scaleY'],
                            100);

                    break;
                }
            }

            $image = Diploma::setDynamicsToImage($image, $e_dinamics, $background);

            // //Añadir marca de agua al 10% de la imagen total
            // $ambiente = DB::table('config_general')->select('marca_agua')->first();
            // if(!is_null($ambiente->marca_agua)){
            //     $marca_agua = json_decode($ambiente->marca_agua);
            //     if($marca_agua->estado){
            //         $watermark = imagecreatefrompng($marca_agua->url);
            //         $ancho = imagesx($im)*0.12;
            //         $alto = round($ancho  * imagesy($watermark) / imagesx($watermark) );
            //         $watermark = Diploma::getImageResized($watermark,$ancho,$alto);
            //         imagecopymerge($im, $watermark,$background['width'] - $ancho, $bg['height']-$alto, 0, 0,imagesx($watermark), imagesy($watermark), 40);
            //     }
            // }
        }

        $preview = Diploma::jpg_to_base64($image);

        if ($response == 'only-data') {
            return $preview;
        }

        return response()->json(compact('preview'));
    }


    public function destroy(Diploma $diploma)
    {
        $diploma->delete();
        return $this->success(['msg' => 'Diploma eliminada correctamente.']);
    }

    public function status(Diploma $diploma, Request $request)
    {
        $diploma->update(['active' => !$diploma->active]);
        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    public function downloadCertificate($id_user, $curso_id)
    {
        try {

            $data = $this->getDiplomaCursoData($id_user, $curso_id);
            $config = Ambiente::first();
            $download = request()->routeIs('diplomas.download');

            if ($data['old_template'] === false) {
                $data['image'] = $this->get_diploma($data['pathImage'], $data['dObjects'], $data['backgroundInfo'], $data);
            }

            return view('certificate_template', compact('data', 'config', 'download'));

        } catch (\Throwable $th) {

            info($th);

            $errorMessage = 'Este diploma no está disponible. Contacta con tu supervisor o soporte de la plataforma.';
            return view('error', compact('errorMessage'));
        }
    }

    public function downloadCertificateProcess($id_user, $process_id)
    {
        try {

            $data = $this->getDiplomaProcessData($id_user, $process_id);
            $config = Ambiente::first();
            $download = request()->routeIs('diplomas_induccion.download');

            if ($data['old_template'] === false) {
                $data['image'] = $this->get_diploma($data['pathImage'], $data['dObjects'], $data['backgroundInfo'], $data);
            }
            
            return view('certificate_template', compact('data', 'config', 'download'));

        } catch (\Throwable $th) {

            info($th);

            $errorMessage = 'Este diploma no está disponible. Contacta con tu supervisor o soporte de la plataforma.';
            return view('error', compact('errorMessage'));
        }
    }
    
    private function getDiplomaProcessData($user_id, $process_id)
    {
        $user = User::with('subworkspace')
            ->select('id', 'name', 'surname', 'lastname', 'subworkspace_id')
            ->where('id', $user_id)->first();

        if (!$user) abort(404);

        $process = Process::select('id', 'title', 'certificate_template_id')
                            ->where('id', $process_id)
                            ->first();

        $editableTemplate = Diploma::find($process?->certificate_template_id);

        if ($editableTemplate) {
            $backgroundInfo = json_decode($editableTemplate->info_bg, true);
            $dObjects = json_decode($editableTemplate->d_objects, true);
            $pathImage = $editableTemplate->path_image;
        } 

        $fecha = $user->summary_process()->where('process_id', $process_id)->first()?->completed_process_date;

        return array(
            'old_template' => $editableTemplate ? false : true,
            'show_certification_date' => null,
            'courses' => null,
            'processes' => $process?->title,
            'grade' => null,
            'course-average-grade' => null,
            'users' => $user->fullname,
            'fecha' => $fecha,
            'image' => NULL,
            'backgroundInfo' => $backgroundInfo ?? [],
            'dObjects' => $dObjects ?? [],
            'pathImage' => $pathImage ?? null,
        );
    }

    private function getDiplomaCursoData($user_id, $course_id)
    {
        $user = User::with('subworkspace')
            ->select('id', 'name', 'surname', 'lastname', 'subworkspace_id')
            ->where('id', $user_id)->first();

        if (!$user) abort(404);

        $course = Course::with([
            'qualification_type',
            'compatibilities_a:id',
            'compatibilities_b:id',
            'summaries' => function ($q) use ($user_id) {
                $q
                    ->with('status:id,name,code')
                    ->where('user_id', $user_id);
            },
        ])
            ->select('id', 'name', 'plantilla_diploma', 'show_certification_date', 'certificate_template_id', 'qualification_type_id')
            ->where('id', $course_id)->first();

        $course_to_export = $course;

        if (!$course && request()->has('original_id')) {

            $original_id = request()->original_id;
            $validate_compatible = Course::validateCompatibleCourse($user, $course, $original_id);

            if ($validate_compatible)
                $course_to_export = $validate_compatible;
        }

        $summary_course = SummaryCourse::getCurrentRow($course, $user);

        if (!$summary_course?->certification_issued_at) abort(404);

        // Load editable template from course,
        // school or module
        // ----------------------------------------

        $certificateTemplateId = $course_to_export->certificate_template_id;

        if (!$certificateTemplateId) {
            $school = $course_to_export->schools()->first();
            $certificateTemplateId = $school->certificate_template_id;
        }

        if (!$certificateTemplateId && $user->subworkspace->plantilla_diploma) {
            $certificateTemplateId = $user->subworkspace->certificate_template_id;
        }

        $editableTemplate = Diploma::find($certificateTemplateId);

        if ($editableTemplate) {
            $backgroundInfo = json_decode($editableTemplate->info_bg, true);
            $dObjects = json_decode($editableTemplate->d_objects, true);
            $pathImage = $editableTemplate->path_image;

        } else {

            // when course, school and module do not have
            // an editable template, load old certificate image
            // ----------------------------------------

            $plantilla_diploma = '';

            if ($course_to_export->plantilla_diploma) {
                $plantilla_diploma = $course_to_export->plantilla_diploma;
            }
            if (!$plantilla_diploma) {
                $school = $course_to_export->schools()->first();
                ($school && $school->plantilla_diploma) && $plantilla_diploma = $school->plantilla_diploma;
            }
            if (!$plantilla_diploma && $user->subworkspace->plantilla_diploma) {
                $plantilla_diploma = $user->subworkspace->plantilla_diploma;
            }

            $base64 = Diploma::parse_image($plantilla_diploma);
        }

        $fecha = $summary_course->certification_issued_at;

        $grade = calculateValueForQualification($summary_course->grade_average, $course->qualification_type?->position);

        return array(
            'old_template' => $editableTemplate ? false : true,
            'show_certification_date' => $course_to_export->show_certification_date,
            'courses' => removeUCModuleNameFromCourseName($course_to_export->name),
            'processes' => null,
            'grade' => (string) intval($grade),
            'course-average-grade' => (string) intval($grade),
            'users' => $user->fullname,
            'fecha' => $fecha,
            'image' => $base64 ?? NULL,
            'backgroundInfo' => $backgroundInfo ?? [],
            'dObjects' => $dObjects ?? [],
            'pathImage' => $pathImage ?? null,
        );
    }
}
