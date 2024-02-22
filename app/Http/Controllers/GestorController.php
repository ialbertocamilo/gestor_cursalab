<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Course;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Usuario;
use App\Models\Categoria;
use Illuminate\Http\Request;
use App\Models\SummaryCourse;
use App\Services\FileService;
use App\Models\Usuario_vigencia;
use App\Exports\EncuestaxgypExport;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class GestorController extends Controller
{
    public function verCertificadoEscuela($usuario_id, $categoria_id)
    {
        try {
            //code...
            $data = $this->getDiplomaEscuelaData($usuario_id, $categoria_id);
            return view('ver_certificado', compact('data'));
        } catch (\Throwable $th) {
            $errorMessage = 'Este diploma no está disponible. Contacta con tu supervisor o soporte de la plataforma.';
            return view('error', compact('errorMessage'));
        }
    }

    public function descargaCertificadoEscuela($usuario_id, $categoria_id)
    {
        try {
            $data = $this->getDiplomaEscuelaData($usuario_id, $categoria_id);
            return view('certificado', compact('data'));
        } catch (\Throwable $th) {
            $errorMessage = 'Este diploma no está disponible. Contacta con tu supervisor o soporte de la plataforma.';
            return view('error', compact('errorMessage'));
        }
    }

    public function verCertificado($user_id, $course_id)
    {
        try {
            $data = $this->getDiplomaCursoData($user_id, $course_id);

            return view('ver_certificado', compact('data'));
        } catch (\Exception $e) {
            info($e);
            $errorMessage = 'Este diploma no está disponible. Contacta con tu supervisor o soporte de la plataforma.';
            return view('error', compact('errorMessage'));
        }
    }

    public function descargaCertificado($id_user, $curso_id)
    {
        try {
            $data = $this->getDiplomaCursoData($id_user, $curso_id);
            return view('certificado', compact('data'));
        } catch (\Throwable $th) {
            $errorMessage = 'Este diploma no está disponible. Contacta con tu supervisor o soporte de la plataforma.';
            return view('error', compact('errorMessage'));
        }
    }

    private function getDiplomaCursoData($user_id, $course_id)
    {
        $user = User::with('subworkspace')
            ->select('id', 'name', 'surname', 'lastname', 'subworkspace_id')
            ->where('id', $user_id)->first();
        if (!$user) abort(404);

        // D3
        $course = Course::with([
            'compatibilities_a:id',
            'compatibilities_b:id',
            'summaries' => function ($q) use ($user_id) {
                $q
                    ->with('status:id,name,code')
                    ->where('user_id', $user_id);
            },
        ])
            ->select('id', 'name', 'plantilla_diploma', 'show_certification_date', 'certificate_template_id')
            ->where('id', $course_id)->first();

        $course_to_export = $course;

        if (!$course && request()->has('original_id')) {
            $original_id = request()->original_id;

            $validate_compatible = $this->validateCompatibleCourse($user, $course, $original_id);

            if ($validate_compatible)
                $course_to_export = $validate_compatible;

        }

        $summary_course = SummaryCourse::getCurrentRow($course, $user);

        if (!$summary_course?->certification_issued_at) abort(404);

        // $template_certification = '';

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
        $editableTemplate = Certificate::find($certificateTemplateId);

        if ($editableTemplate) {
            $backgroundInfo = json_decode($editableTemplate->info_bg);
            $dObjects = json_decode($editableTemplate->d_objects);

            $position_grade = collect($dObjects)
                ->where('id', 'course-average-grade')
                ->first();

            $position_course = collect($dObjects)
                ->where('id', 'courses')
                ->first();

            $position_user = collect($dObjects)
                ->where('id', 'users')
                ->first();

            $position_date = collect($dObjects)
                ->where('id', 'fecha')
                ->first();

            $backgroundImage = $this->parse_image($editableTemplate->path_image, true);

            $base64 = $backgroundImage['base64'];
            $backgroundWidth = $backgroundImage['width'];
            $backgroundHeight = $backgroundImage['height'];

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

            $base64 = $this->parse_image($plantilla_diploma);
        }


        $fecha = $summary_course->certification_issued_at;
        // $base64 = $this->parse_image($template_certification);

        return array(
            'show_certification_date' => $course_to_export->show_certification_date,
            //            'video' => $course->name,
            'video' => removeUCModuleNameFromCourseName($course_to_export->name),
            'grade' => (int)$summary_course->grade_average,
            'usuario' => $user->fullname,
            'fecha' => $fecha,
            'image' => $base64,
            'background_width' => $backgroundWidth ?? null,
            'background_height' => $backgroundHeight ?? null,

            'position_certificate' => $backgroundInfo ?? null,
            'position_user' => $position_user ?? null,
            'position_course' => $position_course ?? null,
            'position_grade' => $position_grade ?? null,
            'position_date' => $position_date ?? null
        );
    }

    private function getDiplomaEscuelaData($usuario_id, $categoria_id)
    {
        if (is_null($usuario = Usuario::select('nombre', 'config_id')->find($usuario_id)))
            abort(404);

        if (is_null($categoria = Categoria::select('nombre', 'plantilla_diploma')->find($categoria_id)))
            abort(404);

        if (!$eva = DB::table('diplomas')->where('categoria_id', $categoria_id)->where('usuario_id', $usuario_id)->orderBy('id', 'DESC')->first())
            abort(404);

        //viendo si existe plantilla de la categoria
        $plantilla_categoria = $categoria->plantilla_diploma != null ? $categoria->plantilla_diploma : $usuario->config->plantilla_diploma;
        //Procesar imagen por el lado del servidor
        $base64 = $this->parse_image($plantilla_categoria);

        return array('image' => $base64, 'video' => $categoria->nombre, 'usuario' => $usuario->nombre, 'fecha' => $eva->fecha_emision);
    }

    private function parse_image($plantilla, $calculateSize = false)
    {
        $type = pathinfo($plantilla, PATHINFO_EXTENSION);
        $plantilla = str_replace(" ", "%20", $plantilla);

        $headers = get_headers(get_media_url($plantilla));
        if ($headers && strpos($headers[0], "200 OK") !== false) {
            $image = file_get_contents(get_media_url($plantilla));
            // Return image's base64 and dimensions

            if ($calculateSize) {
                $imageBinary = imagecreatefromstring($image);
                $width = imagesx($imageBinary);
                $height = imagesy($imageBinary);

                return [
                    'width' => $width,
                    'height' => $height,
                    'base64' => 'data:image/' . $type . ';base64,' . base64_encode($image)
                ];

            } else if ($image !== false) {
                return 'data:image/' . $type . ';base64,' . base64_encode($image);
            }
        }

        return  abort(404);
    }

    public function descargaArchivo($id)
    {
        if (is_null($posteo = Posteo::where('id', $id)->first())) {
            return "Registro no existe";
        }
        if (!$posteo->archivo) {
            return "";
        }
        $ruta = json_decode($posteo->archivo, true);
        $archivo = explode("/", $posteo->archivo);
        $nombre_archivo = '';
        if (is_array($archivo) && count($archivo) > 0) {
            for ($i = 0; $i < count($archivo); $i++) {
                $nombre_archivo = $archivo[$i];
            }
        }
        // return response()->download($_SERVER['DOCUMENT_ROOT']."/uploads/".$ruta[0]['download_link'], $ruta[0]['original_name']);
        // return response()->download($_SERVER['DOCUMENT_ROOT']."/".$posteo->archivo, $nombre_archivo);
        return Storage::download($posteo->archivo);
    }

    public function descargaVideo($id)
    {
        if (is_null($posteo = Posteo::where('id', $id)->first())) {
            return "Registro no existe";
        }
        if (!$posteo->video) {
            return "";
        }
        $ruta = json_decode($posteo->video, true);
        $video = explode("/", $posteo->video);
        $nombre_video = '';
        if (is_array($video) && count($video) > 0) {
            for ($i = 0; $i < count($video); $i++) {
                $nombre_video = $video[$i];
            }
        }
        // return response()->download($_SERVER['DOCUMENT_ROOT']."/uploads/".$ruta[0]['download_link'], $ruta[0]['original_name']);
        // return response()->download($_SERVER['DOCUMENT_ROOT']."/".$posteo->video, $nombre_video);
        return Storage::download($posteo->video);
    }


    //******************** EXPORTAR JEFE ***************************//
    // Exporta data de Colaboradores, desde aplicacion web
    // public function exportColabs($id){
    // 	return "";
    // }


    //******************** IMPORTAR ***************************//
    // Generar password a nuevos usuarios
    public function generarPass()
    {
        $usuarios = Usuario::select('dni')->whereNull('password')->take(1000)->get();
        if ($usuarios->count() > 0) {
            foreach ($usuarios as $user) {
                \DB::table('usuarios')->where('dni', $user->dni)->update(['password' => Hash::make($user->dni)]);
            }

            return "Pass generados";
        } else {
            return "No existen usuarios sin Pass";
        }
    }

    //
    public function generarVigencia()
    {
        // Antes hay que borrar todos las vigencias
        $usuarios = Usuario::select('id', 'config_id')
            ->whereNotIn('id', \DB::table("usuario_vigencia")->select('usuario_id')->pluck('usuario_id'))
            ->get();
        if ($usuarios->count() > 0) {
            foreach ($usuarios as $user) {
                // Dias de configuracion
                $dias = $user->config->duracion_dias;
                date_default_timezone_set('America/Lima');

                // if ($user->vigencia) {
                //insertar vigencia
                $vigencia = new Usuario_vigencia;
                $vigencia->usuario_id = $user->id;
                $vigencia->fecha_inicio = date('Y-m-d');
                $vigencia->fecha_fin = date('Y-m-d', strtotime($vigencia->fecha_inicio . ' + ' . $dias . ' days'));
                $vigencia->save();
                // }

            }
            return "UV vigencia actualizado";
        } else {
            return "No existen usuarios sin UV";
        }
    }

    public function importar()
    {
        if (\Auth::check()) {
            return view('importar.index');
        } else {
            return redirect('/login');
        }
    }

    // IMPORTAR CSV USUARIOS
    public function importarUsuarios(Request $request)
    {
        // return $request;
        $input = $request->all();
        $rules = array(
            'file_usuarios' => 'required',
        );

        $validator = \Validator::make($input, $rules);
        if ($validator->fails()) {
            return \Redirect::back()->withInput()->withErrors($validator);
        }

        if ($request->hasFile("file_usuarios")) {
            // return "s";
            $file = $request->file('file_usuarios');
            $filename = date('dmY') . str_random(8) . '.' . $file->getClientOriginalExtension();
            if ($file->move('imports', $filename)) {

                // if ($request->disableu == 1) {
                //     // Poner como INACTIVOS a todos, para llenar la lista nueva con ACTIVOS
                //     \DB::table('usuarios')->update(['estado' => 0]);
                // }

                // GUARDAR DATOS IMPORTACION
                Excel::load('imports/' . $filename, function ($reader) {
                    $usuario = array();
                    foreach ($reader->get() as $fila) {

                        print_r($fila);
                        // \Log::error($fila->dni);
                        // if ($uxistente = Usuarios::where('dni',$fila->dni)->first()) {
                        //     $uxistente->estado = 1;
                        //     $uxistente->save();
                        // return $fila->dni;

                        // if ($uxistente = Usuario::where('dni',$fila->dni)->first()) {
                        //     $uxistente->estado = $fila->estado;
                        //     $uxistente->save();
                        // }else{
                        //     $usuario[] = json_decode($fila);
                        //     $usuario = (array) $usuario[0];
                        //     unset($usuario['area_id']);
                        //     $usuario = array_add($usuario, 'grupo_id', $fila->area_id);
                        //     // \Log::error($usuario);
                        //     Usuario::create($usuario);
                        // }
                    }
                });

                //Eliminar ruta
                $ruta = 'imports/' . $filename;
                \File::delete($ruta);
                back()->with('success', 'Subido.');
            }
        }

        back()->with('error', 'Error.');
    }

    private function validateCompatibleCourse($user, $course_compatible, $original_course_id)
    {
        // C3
        $original_course = Course::with([
            'compatibilities_a:id',
            'compatibilities_b:id',
            'summaries' => function ($q) use ($user) {
                $q
                    ->with('status:id,name,code')
                    ->where('user_id', $user->id);
            },
        ])
            ->select('id', 'name', 'plantilla_diploma', 'show_certification_date')
            ->where('id', $original_course_id)->first();

        if (!$original_course) abort(404);
        // TODO: Si llega compatible validar que sea su compatible el curso de la ruta ($course_id)
        // Compatible de C3
        $compatible = $original_course->getCourseCompatibilityByUser($user);

        if (!$compatible) abort(404);

        // D3 !== Compatible de C3
        if ($course_compatible->id !== $compatible->course->id) abort(404);

        return $original_course;
    }

    public function switchPlatform( Request $request) {
        $platform = $request->platform ?? 'capacitacion';
        session()->put('platform', $platform);
        return $this->success(['platform' => $platform]);
    }

}
