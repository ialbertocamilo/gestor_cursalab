<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Models\Prueba;
use App\Models\Categoria;
use App\Models\Curso;
use App\Models\Perfil;
use App\Models\Grupo;
use App\Models\Abconfig;
use App\Models\Carrera;
use App\Models\Matricula;
use App\Models\Visita;
use App\Models\Posteo;
use App\Models\Curricula;
use App\Models\Vademecum;

use Maatwebsite\Excel\Facades\Excel;

use App\Exports\PruebasExport;
use App\Exports\TemasExport;
use App\Exports\PreguntasExport;
use App\Exports\UsuariosExport;
use App\Exports\NovisitasExport;
use App\Exports\VisitasCompletoExport;
use App\Exports\ReporteTotalExport;
use App\Exports\ReporteTotalxFechaExport;
use App\Exports\ReporteFiltradoExport;
use App\Exports\ReportexCursoExport;
use App\Exports\ReporteVisitasTotalExport;
use App\Exports\ReporteVisitasSinEVExport;
use App\Exports\ReiniciosExport;
use App\Exports\ReporteEvAbiertasExport;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Auth;

class ExportarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $modulos = \DB::select( \DB::raw("SELECT id, etapa FROM `ab_config` WHERE estado = 1"));
        $modulos_evabiertas = \DB::select( \DB::raw("SELECT id, etapa FROM `ab_config` WHERE estado = 1 AND id IN (SELECT config_id FROM categorias WHERE id IN (SELECT categoria_id FROM posteos WHERE tipo_ev = 'abierta'))"));
        $escuelas = \DB::select( \DB::raw("SELECT id, nombre FROM categorias WHERE modalidad = 'regular' AND estado = 1"));
        $cursos = \DB::select( \DB::raw("SELECT id, nombre FROM cursos WHERE estado = 1"));
        $temas = \DB::select( \DB::raw("SELECT id, nombre FROM posteos WHERE estado = 1 AND evaluable = 'si'"));
        // $gruposis = \DB::select( \DB::raw("SELECT id, nombre FROM grupos WHERE estado = 1"));
        $grupos = \DB::select( \DB::raw("SELECT grupo FROM `usuarios` WHERE grupo != '' GROUP BY grupo"));
        // $perfiles = \DB::select( \DB::raw("SELECT id, nombre FROM perfiles WHERE estado = 1 ORDER BY id DESC"));
        $carreras = \DB::select( \DB::raw("SELECT id, nombre FROM carreras WHERE estado = 1 ORDER BY nombre"));
        $users = \DB::select( \DB::raw("SELECT id, name FROM users WHERE id IN (select admin_id FROM reinicios) ORDER BY name"));

        // return view('exportar.index', compact('modulos', 'escuelas', 'cursos','temas','gruposis', 'grupos', 'perfiles', 'users', 'modulos_evabiertas'));
        return view('exportar.index', compact('modulos', 'escuelas', 'cursos','temas', 'grupos', 'users', 'modulos_evabiertas', 'carreras'));
    }

    public function obtenerDatos(Request $request)
    {
        $modulos = \DB::table('ab_config')->select('id', 'etapa')->where('estado', 1)->get();
        $users = \DB::select("SELECT id, name FROM users WHERE id IN (select admin_id FROM reinicios) ORDER BY name");
        $user_id = Auth::user()->id;
        $user = \DB::select("SELECT u.name, u.email, r.name as rol FROM users as u inner join role_user as ru on u.id=ru.user_id inner join roles as r on r.id=ru.role_id WHERE u.id = " . $user_id);
        $vademecums = Vademecum::where('active', 1)->get(['id', 'name']);
        // $user = \DB::select("SELECT u.name, u.email, r.name as rol FROM users as u inner join role_user as ru on u.id=ru.user_id inner join roles as r on r.id=ru.role_id WHERE u.id = " . $user_id);
        return compact('modulos', 'users', 'user', 'vademecums');
    }

    public function index2()
    {
        $modulos = \DB::select("SELECT id, etapa FROM `ab_config` WHERE estado = 1");
        return view('exportar_vue.index', compact('modulos'));
    }

    public function indexReport($layout)
    {
        $modulos = \DB::select("SELECT id, etapa FROM `ab_config` WHERE estado = 1");

        return view('exportar_vue.list', compact('modulos', 'layout'));
    }

    //
    public function get_user_data(Request $request)
    {
        if(!$request->has("dni")){
            return redirect()->back()->with('info', 'Ingresar un valor para DNI');
        }

        $data = "";
        if ($request->has("dni") && $request->dni != "") {
            $modulo_col = Abconfig::select('id','etapa')->pluck('etapa','id');
            $result = Usuario::where('dni',$request->dni)->select(\DB::raw('id, config_id, nombre'))
                                ->first();

            $matricula = $this->usuario_matricula_actual($result->id);

            $data = "<h4>".$result->nombre."</h4>
                    <ul class=''>
                        <li><strong>Módulo:</strong> ".$modulo_col[$result->config_id]."</li>
                        <li><strong>Carrera:</strong> ".$matricula->carrera->nombre."</li>
                        <li><strong>Ciclo:</strong> ".$matricula->ciclo->nombre."</li>
                    </ul>";
        }

        return $data;
    }

    //
    public function usuario_notas_x_curso(Request $request)
    {
        if(!$request->has("dni")){
            return redirect()->back()->with('info', 'Ingresar un valor para DNI');
        }

        $modulo_col = Abconfig::select('id','etapa')->pluck('etapa','id');
        $curso_col = Curso::select('id','nombre')->pluck('nombre','id');
        $categoria_col = Categoria::select('id','nombre')->pluck('nombre','id');
        $grupo_col = Grupo::select('id','nombre')->pluck('nombre','id');
        $tema_col = Posteo::select('id','nombre')->pluck('nombre','id');

        ///return $request;
        $w1 = " 1 ";

        if ($request->has("dni") && $request->dni != "") {
            $w1 = "u.dni = '".$request->dni."'";
        }
        $data = \DB::table('usuarios AS u')
            ->select(\DB::raw('u.grupo_id, u.grupo AS grupo_usu, u.dni, u.nombre nombre, u.sexo, u.botica, u.config_id, u.ultima_sesion, pu.categoria_id, pu.curso_id, pu.posteo_id, pu.resultado, u.id AS user_id, pu.nota, pu.created_at, pu.updated_at'))
            ->join('pruebas AS pu', 'u.id', '=', 'pu.usuario_id')
            ->whereRaw($w1)
            ->orderBy('pu.created_at')
            ->get();

        // return $data;
        return view('exportar.usuario_notas_x_curso', compact('data', 'grupo_col', 'modulo_col','categoria_col','curso_col','tema_col'));
    }

    // reporte de usuarios - descargar
    public function exportUsuariosDW(Request $request)
    {
        setCookie("downloadStarted", 1, time() + 20, '/', "", false, false);
        ///return $request;
        $w1 = "WHERE 1";
        $w2 = $w3 = "";

        if ($request->has("m") && $request->m != "" && $request->m != 'ALL') {
            $w1 = "WHERE u.config_id = ".$request->m;
        }
        if ($request->has("g") && $request->g != "" && $request->g != 'ALL') {
            $w2 = "AND u.grupo = '".$request->g."'";
        }
        // if ($request->has("per") && $request->per != "" && $request->per != 'ALL') {
        //     $w3 = "AND u.perfil_id = ".$request->per;
        // }
        if ($request->has("carre") && $request->carre != "" && $request->carre != 'ALL') {
            // $w3 = "AND i.carrera_id = ".$request->carre;
            $usu_en_carre = Matricula::where('carrera_id', $request->carre)->pluck('usuario_id')->toArray();
            // dd($usu_en_carre);
            $usu_ids = implode(',', $usu_en_carre);
            $w3 = "AND u.id IN (".$usu_ids.")";
        }

        // $result = Usuario::select()->get();

        $datos = \DB::select( \DB::raw("
            SELECT u.id, u.config_id, u.grupo_id, u.grupo as grupo_usu, u.botica, u.dni, u.nombre, u.sexo
            FROM usuarios u
            ".$w1."
            ".$w2."
            ".$w3."
            ORDER BY u.nombre"));

            // return $datos;
        ob_end_clean();
        ob_start();
        return Excel::download(new UsuariosExport($datos), 'Usuarios  - '.date('d-m-Y h:i').'.xlsx');
    }

    // reporte por curso - descargar
    public function exportVisitas(Request $request)
    {
        setCookie("downloadStarted", 1, time() + 20, '/', "", false, false);

        // FastExcel
        function dataGenerator($request) {

            $modulo_col = Abconfig::select('id','etapa')->pluck('etapa', 'id');
            $posteo_col = Posteo::select('id','nombre')->pluck('nombre','id');
            $posteo_curso = Posteo::select('posteos.id','cursos.nombre')
                                    ->join('cursos', 'posteos.curso_id', '=', 'cursos.id')
                                    ->pluck('cursos.nombre','posteos.id');
            $posteo_cate = Posteo::select('posteos.id','categorias.nombre')
                            ->join('categorias', 'posteos.categoria_id', '=', 'categorias.id')
                            ->pluck('categorias.nombre','posteos.id');

            //
            $filtro = $request;
            $w1 = " 1 ";
            $w2 = $w3 = " 1 ";

            if ($filtro != "" && isset($filtro)) {

                if ($filtro->m != "" && $filtro->m != 'ALL') {
                    $w1 = "config_id = ".$filtro->m;
                }
                if ($filtro->g != "" && $filtro->g != 'ALL') {
                    $w2 = "grupo = '".$filtro->g."'";
                }
                if ($filtro->carre != "" && $filtro->carre != 'ALL') {
                    $usu_en_carre = Matricula::where('carrera_id', $filtro->carre)->pluck('usuario_id')->toArray();
                    $usu_ids = implode(',', $usu_en_carre);
                    $w3 = "id IN (".$usu_ids.")";
                }
            }

            //
            $result = Usuario::select(\DB::raw('id, config_id, grupo_id, grupo as grupo_usu, botica, dni, nombre, sexo'))
                        ->whereRaw($w1)
                        ->whereRaw($w2)
                        ->whereRaw($w3)
                        ->orderBy('nombre')
                        ->cursor();
            //
            foreach ($result as $item) {
                // dd($item);
                $visitas = $item->visitas;
                if($visitas){
                    $mod = isset($modulo_col[$item->config_id]) ? $modulo_col[$item->config_id] : "";
                    $matricula = ExportarController::usuario_matricula_actual($item->id);
                    $carre = isset($matricula->carrera) ? $matricula->carrera->nombre : "";
                    $ciclo = isset($matricula->ciclo) ? $matricula->ciclo->nombre : "";

                    foreach($visitas as $visi){
                        $cate = isset($posteo_cate[$visi->post_id]) ? $posteo_cate[$visi->post_id] : "";
                        $curso = isset($posteo_curso[$visi->post_id]) ? $posteo_curso[$visi->post_id] : "";
                        $tema = isset($posteo_col[$visi->post_id]) ? $posteo_col[$visi->post_id] : "";
                        $vi_cant = $visi->sumatoria;

                        $arr = [
                            'Módulo' => $mod,
                            'DNI' => $item->dni,
                            'Nombres' => $item->nombre,
                            'Carrera' => $carre,
                            'Ciclo' => $ciclo,
                            'Escuela' => $cate,
                            'Curso' => $curso,
                            'Tema' => $tema,
                            'Cantidad Visitas' => $vi_cant
                        ];

                        yield $arr;

                    }
                }

            }
        }

        $dataex = dataGenerator($request);
        // (new FastExcel($dataex))->export('exports/visitas.xlsx');
        return (new FastExcel($dataex))->download('exports/visitas.xlsx');
    }

    //reporte total de notas - descargar
    public function exportReporteTotalDW(Request $request)
    {
        setCookie("downloadStarted", 1, time() + 20, '/', "", false, false);
        $filtros = $request;

        // (new TemasExport)->queue('temas.xlsx');
        // return back()->withSuccess('Export started!');
        return (new TemasExport)->download('te.xlsx');

        // Excel::queue(new ReporteTotalExport($filtros), 'Reporte.xlsx');
        // // (new ReporteTotalExport($filtros))->queue('Reporte.xlsx');
        // return back()->withSuccess('Export started!');

        //return Excel::download(new ReporteTotalExport($filtros), 'Reporte consolidado de notas - '.date('d-m-Y h:i').'.xlsx');
    }

    // reporte por curso - descargar
    public function exportReportexCursoDW(Request $request)
    {
        setCookie("downloadStarted", 1, time() + 20, '/', "", false, false);
        // $filtros = '';
        // if (isset($request->rep) && $request->rep == 'xcurso') {
            $filtros = $request;
        // }
        ob_end_clean();
        ob_start();
        return Excel::download(new ReportexCursoExport($filtros), 'Reporte consolidado por curso - '.date('d-m-Y h:i').'.xlsx');
    }

    // reporte EVALUACIONES ABIERTAS
    public function exportReporteEvAbiertasDW(Request $request)
    {
        setCookie("downloadStarted", 1, time() + 20, '/', "", false, false);
        // $filtros = '';
        // if (isset($request->rep) && $request->rep == 'xcurso') {
            $filtros = $request;
        // }
        ob_end_clean();
        ob_start();
        return Excel::download(new ReporteEvAbiertasExport($filtros), 'Reporte evaluaciones abiertas - '.date('d-m-Y h:i').'.xlsx');
    }

    // reporte de reinicios - descargar
    public function reiniciosDW(Request $request)
    {
        setCookie("downloadStarted", 1, time() + 20, '/', "", false, false);
        $filtros = $request;
        // return $filtros;
        ob_end_clean();
        ob_start();
        return Excel::download(new ReiniciosExport($filtros), 'Reporte de reinicios - '.date('d-m-Y h:i').'.xlsx');
    }

    // reporte por curso - descargar
    public function exportVersiones(Request $request)
    {
        setCookie("downloadStarted", 1, time() + 20, '/', "", false, false);
        // FastExcel
        function dataGenerator($request) {

            $modulo_col = Abconfig::select('id','etapa')->pluck('etapa', 'id');
            //
            $result = \DB::table('usuario_versiones')->orderBy('updated_at', 'DESC')->cursor();
            //
            foreach ($result as $item) {
                // dd($item);
                $usuario = Usuario::where('id', $item->usuario_id)->first();
                if($usuario){
                    $mod = isset($modulo_col[$usuario->config_id]) ? $modulo_col[$usuario->config_id] : "";

                    $arr = [
                        'Módulo' => $mod,
                        'DNI' => $usuario->dni,
                        'Nombres' => $usuario->nombre,
                        'Género' => $usuario->sexo,
                        'Grupo' => $usuario->grupo,
                        'Botica' => $usuario->botica,
                        'Cargo' => $usuario->cargo,
                        'Versión Android' => $item->v_android,
                        'Última sesión' => date("d/m/Y H:i", strtotime($item->updated_at))
                    ];
                    yield $arr;
                }

            }
        }

        $dataex = dataGenerator($request);
        return (new FastExcel($dataex))->download('exports/versiones.xlsx');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //**/
    // obtiene carrera del usuario
    public static function usuario_matricula_actual($user_id){

        // $result = \DB::select( \DB::raw("
                // SELECT SUM(sumatoria) visitas_x_curso FROM `visitas` WHERE usuario_id = ".$user_id." AND post_id in (SELECT id FROM posteos WHERE curso_id = ".$curso_id." )
                // "));

        // $result = Matricula::
        //             join('ciclos', 'matricula.ciclo_id', '=', 'ciclos.id')
        //             ->where('usuario_id', $user_id)
        //             ->where('matricula.estado', 1)
        //             ->orderBy('ciclos.numeracion', 'DESC')->first();

        $result = Matricula::where('usuario_id', $user_id)->where('estado', 1)->orderBy('secuencia_ciclo', 'DESC')->first();

        return $result;
    }

    // Obtiene curricula del curso (ciclo y carrera)
    public static function curso_curricula($carrera_id, $curso_id){
        $result = Curricula::select('id')->where('carrera_id', $carrera_id)->where('curso_id', $curso_id)->first();

        return $result;
    }


    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //**/

    // public function cambia_modulo_carga_grupo(Request $request)
    // {
    //     $m = $request->input('m');
    //     if ($m != '' && $m != 'ALL') {
    //         $grupos = \DB::select( \DB::raw("SELECT grupo FROM usuarios WHERE config_id = ".$m." AND grupo != '' GROUP BY grupo"));
    //     }else{
    //         $grupos = \DB::select( \DB::raw("SELECT grupo FROM usuarios WHERE grupo != '' GROUP BY grupo"));
    //     }

    //     return $grupos;
    // }

    public function cambia_modulo_carga_carrera(Request $request)
    {
        $m = $request->input('m');
        if ($m != '' && $m != 'ALL') {
            $carreras = \DB::select( \DB::raw("SELECT id, nombre FROM carreras WHERE config_id = ".$m.""));
        }else{
            $carreras = \DB::select( \DB::raw("SELECT id, nombre FROM carreras"));
        }

        return $carreras;
    }

    public function cambia_carrera_carga_grupo(Request $request)
    {
        $m = $request->input('m');
        $carre = $request->input('carre');

        $w1 = ' WHERE 1 '; $w2 = '';

        if ($m != '' && $m != 'ALL' ) {
            $w1 = " WHERE p.config_id = ".$m;
        }
        if ($carre != '' && $carre != 'ALL' ) {
            $w2 = " AND u.carrera_id LIKE '".$carre."'";
        }
        // \DB::enableQueryLog();
        $result = \DB::select( \DB::raw("SELECT p.id, p.grupo FROM usuarios AS p
                                            INNER JOIN matricula AS u ON p.id = u.usuario_id
                                            ".$w1."
                                            ".$w2."
                                            GROUP BY p.grupo
                                            ORDER BY p.grupo"
                                        ));
        // dd(\DB::getQueryLog());
        return $result;
    }


    // public function cambia_grupo_carga_perfil(Request $request)
    // {
    //     $m = $request->input('m');
    //     $g = $request->input('g');

    //     $w1 = ' WHERE 1 '; $w2 = '';

    //     if ($m != '' && $m != 'ALL' ) {
    //         $w1 = " WHERE u.config_id = ".$m;
    //     }
    //     if ($g != '' && $g != 'ALL' ) {
    //         $w2 = " AND u.grupo LIKE '".$g."'";
    //     }
    //     // \DB::enableQueryLog();
    //     $result = \DB::select( \DB::raw("SELECT p.id, p.nombre FROM perfiles AS p
    //                                         INNER JOIN usuarios AS u ON p.id = u.perfil_id
    //                                         ".$w1."
    //                                         ".$w2."
    //                                         GROUP BY p.id
    //                                         ORDER BY p.id DESC"
    //                                     ));
    //     // dd(\DB::getQueryLog());
    //     return $result;
    // }

    // MODULO - ESCUELA
    public function cambia_modulo_carga_escuela(Request $request)
    {
        $mod = $request->input('mod');
        $w1 = ' WHERE 1 ';

        if ($mod != '' && $mod != 'ALL' ) {
            $w1 = " WHERE a.config_id = ".$mod;
        }

        $result = \DB::select( \DB::raw("
            SELECT a.id, a.nombre FROM categorias AS a
            INNER JOIN cursos AS c ON c.categoria_id = a.id
            INNER JOIN posteos AS p ON p.curso_id = c.id
            ".$w1."
            AND a.estado = 1
            AND c.estado = 1
            AND p.evaluable = 'si'
            AND p.tipo_ev = 'calificada'
            AND p.estado = 1
            GROUP BY c.categoria_id
            ORDER BY a.nombre
        "));
        // AND a.modalidad = 'regular'

        return $result;
    }

    // ESCUELA - CURSO : EVALUACIONES CALIFICADAS
    public function cambia_escuela_carga_curso(Request $request)
    {
        $esc = $request->input('esc');
        $mod = $request->input('mod');
        $mod_w = ($mod != 'ALL' && $mod != '') ? "c.config_id = ".$mod : "1";

        if ($esc != '' && $esc != 'ALL') {
            $result = \DB::select( \DB::raw("
                                            SELECT c.id, c.nombre FROM cursos AS c
                                            INNER JOIN posteos AS p ON p.curso_id = c.id
                                            WHERE c.categoria_id = ".$esc."
                                            AND p.evaluable = 'si'
                                            AND p.tipo_ev = 'calificada'
                                            AND c.estado = 1
                                            GROUP BY p.curso_id
                                            ORDER BY c.nombre
                                            "));
        }else{
            $result = \DB::select( \DB::raw("
                                            SELECT c.id, c.nombre FROM cursos AS c
                                            INNER JOIN posteos AS p ON p.curso_id = c.id
                                            WHERE ".$mod_w."
                                            AND p.evaluable = 'si'
                                            AND p.tipo_ev = 'calificada'
                                            AND c.estado = 1
                                            GROUP BY p.curso_id
                                            ORDER BY c.nombre
                                            "));
        }
        return $result;
    }

    // MODULO - ESCUELA
    public function cambia_modulo_carga_escuela_ev_ab(Request $request)
    {
        $mod = $request->input('mod');

        $w1 = ' WHERE 1 ';

        if ($mod != '' && $mod != 'ALL' ) {
            $w1 = " WHERE a.config_id = ".$mod;
        }

        $result = \DB::select( \DB::raw("
            SELECT a.id, a.nombre FROM categorias AS a
            INNER JOIN cursos AS c ON c.categoria_id = a.id
            INNER JOIN posteos AS p ON p.curso_id = c.id
            ".$w1."
            AND a.estado = 1
            AND c.estado = 1
            AND p.evaluable = 'si'
            AND p.tipo_ev = 'abierta'
            AND p.estado = 1
            GROUP BY c.categoria_id
            ORDER BY a.nombre
        "));
        // AND a.modalidad = 'regular'

        return $result;
    }

    // ESCUELA - CURSO: EVALUACIONES ABIERTAS
    public function cambia_escuela_carga_curso_ev_ab(Request $request)
    {
        $esc = $request->input('esc');
        $mod = $request->input('mod');

        $mod_w = ($mod != 'ALL' && $mod != '') ? " WHERE c.config_id = ".$mod." " : " WHERE 1 ";
        $esc_w = "";

        if ($esc != '' && $esc != 'ALL') {
            $esc_w = " AND c.categoria_id = ".$esc." ";
        }

        $result = \DB::select( \DB::raw("
                                        SELECT c.id, c.nombre FROM cursos AS c
                                        INNER JOIN posteos AS p ON p.curso_id = c.id
                                        ".$mod_w."
                                        ".$esc_w."
                                        AND p.evaluable = 'si'
                                        AND p.tipo_ev = 'abierta'
                                        AND c.estado = 1
                                        GROUP BY p.curso_id
                                        ORDER BY c.nombre
                                        "));

        return $result;
    }

    // CURSO - TEMA
    public function cambia_curso_carga_tema(Request $request)
    {
        $cur = $request->input('cur');
        $esc = $request->input('esc');
        $esc_w = ($esc != 'ALL' && $esc != '') ? " WHERE categoria_id = " . $esc." " : " WHERE 1 ";

        $cur_w = " ";
        if ($cur != '' && $cur != 'ALL') {
            $cur_w = " AND curso_id = ".$cur." ";
        }

        $result = \DB::select(\DB::raw("
            SELECT id, nombre FROM posteos
            " . $esc_w . "
            " . $cur_w . "
            AND estado = 1
            AND tipo_ev = 'calificada'
            "));

        return $result;
    }

    // EV ABIERTA: CURSO - TEMA
    public function cambia_curso_carga_tema_abiertas(Request $request)
    {
        $cur = $request->input('cur');
        $esc = $request->input('esc');

        $esc_w = ($esc != 'ALL' && $esc != '') ? " WHERE categoria_id = " . $esc." " : " WHERE 1 ";

        $cur_w = " ";
        if ($cur != '' && $cur != 'ALL') {
            $cur_w = " AND curso_id = ".$cur." ";
        }

        $result = \DB::select(\DB::raw("
            SELECT id, nombre FROM posteos
            " . $esc_w . "
            " . $cur_w . "
            AND estado = 1
            AND tipo_ev = 'abierta'
            "));

        return $result;
    }

    ///////////////////

}
