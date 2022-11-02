<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\SummaryCourse;
use App\Models\User;
use Illuminate\Http\Request;
use DB;
use App\Models\Prueba;
use App\Models\Categoria;
use App\Models\Posteo;
use App\Models\Curso;
use App\Models\Usuario;
use App\Models\Usuario_vigencia;
use App\Models\Grupo;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EncuestaxgypExport;
use Illuminate\Support\Facades\Hash;

class GestorController extends Controller
{
    public function verCertificadoEscuela($usuario_id, $categoria_id)
    {
        try {
            //code...
            $data = $this->getDiplomaEscuelaData($usuario_id, $categoria_id);
            return view('ver_certificado', compact('data'));
        } catch (\Throwable $th) {
            abort(404);
        }
    }

    public function descargaCertificadoEscuela($usuario_id, $categoria_id)
    {
        try {
            $data = $this->getDiplomaEscuelaData($usuario_id, $categoria_id);
            return view('certificado', compact('data'));
        } catch (\Throwable $th) {
            abort(404);            //throw $th;
        }
    }

    public function verCertificado($user_id, $course_id)
    {
        try {
            $data = $this->getDiplomaCursoData($user_id, $course_id);
            return view('ver_certificado', compact('data'));
        } catch (\Exception $e) {
            info($e);
            abort(404);
        }
    }

    public function descargaCertificado($id_user, $curso_id)
    {
        try {
            $data = $this->getDiplomaCursoData($id_user, $curso_id);
            return view('certificado', compact('data'));
        } catch (\Throwable $th) {
            abort(404);            //throw $th;
        }
    }

    private function getDiplomaCursoData($user_id, $course_id)
    {
        $user = User::with('subworkspace')->select('id','name', 'surname', 'lastname', 'subworkspace_id')->where('id', $user_id)->first();
        if (!$user) abort(404);

        $course = Course::select('id', 'name', 'plantilla_diploma')->where('id', $course_id)->first();
        if (!$course) abort(404);

        $summary_course = SummaryCourse::getCurrentRow($course, $user);

        if (!$summary_course) abort(404);

        $plantilla_curso = $course->plantilla_diploma != null ? $course->plantilla_diploma : $user->subworkspace->plantilla_diploma;
        $base64 = $this->parse_image($plantilla_curso);
        return array('video' => $course->name, 'usuario' => $user->fullname, 'fecha' => $summary_course->certification_issued_at, 'image' => $base64);
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

    private function parse_image($plantilla)
    {
        $type = pathinfo($plantilla, PATHINFO_EXTENSION);
        $image = file_get_contents(get_media_url($plantilla));
        return 'data:image/' . $type . ';base64,' . base64_encode($image);
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


}
