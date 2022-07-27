<?php

namespace App\Http\Controllers;

use App\Exports\EncuestaxgypExport;
use App\Models\Ab_config;
use App\Models\Criterion;
use App\Models\Curso;
use App\Models\Poll;
use App\Models\PollQuestion;
use App\Models\PollQuestionAnswer;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function uploadImage($type, Request $request)
    {
        $data = $request->all();

        $folder =  isset($data['model_id']) ? "mce-uploads/{$type}/{$data['model_id']}" : "mce-uploads/{$type}";

        $path = $data['image']->storePublicly($folder, 'do_spaces');

        // $location = asset('storage/' . $path);

        $location = Storage::disk('do_spaces')->url($path);

        return compact('location');
    }

    /************************************** ENCUESTAS RESUMEN ***********************************/
    public function encuesta_filtrada_data($enc_id, $mod, $curso_id, $gru, $tipopreg){
        $cad1 = " WHERE 1 ";
        $cad2 = "";
        $cad3 = "";
        $cad4 = "";
        $cad5 = "";

        $ep_result = [];

        // filtro encuesta
        if ($enc_id != '' && $enc_id != 'ALL') {
            $cad1 = " WHERE ep.encuesta_id = '".$enc_id."'";
        }
        // firltro modulo
        if ($mod != '' && $mod != 'ALL') {
            $cad2 = " AND u.config_id = '".$mod."'";

            // filtra lista de cursos
            $cursos = DB::select( DB::raw("SELECT c.id AS id,  c.nombre AS nombre, cat.nombre AS escuela, m.codigo_matricula FROM cursos c INNER JOIN categorias cat ON cat.id = c.categoria_id INNER JOIN ab_config m ON m.id = c.config_id  WHERE categoria_id IN (SELECT id FROM categorias WHERE config_id = ".$mod.") AND c.id IN (SELECT curso_id FROM encuestas_respuestas GROUP BY curso_id) ORDER BY c.nombre"));

            $ep_result['cursos'] = $cursos;
        }
        // filtro curso
        if ($curso_id != '' && $curso_id != 'ALL') {
            $cad3 = " AND er.curso_id = '".$curso_id."'";
            // filtra lista de grupos
            $grupos = DB::select( DB::raw("SELECT grupo,grupo_nombre FROM usuarios WHERE id IN (SELECT usuario_id FROM encuestas_respuestas WHERE curso_id = ".$curso_id." AND tipo_pregunta = 'califica') GROUP BY grupo order by grupo_nombre asc"));

            $ep_result['grupos'] = $grupos;
        }
        // filtro grupo
        if ($gru != '' && $gru != 'ALL') {
            $cad4 = " AND u.grupo = '".$gru."'";
        }

        if ($tipopreg != "") {
            $cad5 = " AND ep.tipo_pregunta = '".$tipopreg."'";
        }

        // el group by
        $elgroupby = "";
        // if ($tipopreg == 'califica') {
        //     $elgroupby = "GROUP BY ep.id";
        // }

        // consulta maestra
        $ep_result['pgtas'] = DB::select( DB::raw("
            SELECT ep.id, ep.encuesta_id, ep.titulo, er.curso_id, u.config_id, u.grupo, ep.tipo_pregunta
            FROM encuestas_preguntas ep
            INNER JOIN encuestas_respuestas er ON er.encuesta_id = ep.encuesta_id
            INNER JOIN usuarios u ON u.id = er.usuario_id
            ".$cad1."
            ".$cad2."
            ".$cad3."
            ".$cad4."
            ".$cad5."
            ".$elgroupby."
            GROUP BY ep.id"));

        // dd($ep_result['pgtas']);

        return $ep_result;
    }

    // Pregunta de encuestas a procesar agrupadas por curso
    public function encuesta_filtrada_x_curso($enc_id, $mod, $curso_id, $gru, $tipopreg){
        $cad1 = " WHERE 1 ";
        $cad2 = "";
        $cad3 = " AND curso_id > 0";
        $cad4 = "";
        $cad5 = "";

        $ep_result = [];

        // filtro encuesta
        if ($enc_id != '' && $enc_id != 'ALL') {
            $cad1 = " WHERE er.encuesta_id = '".$enc_id."'";
        }
        // firltro modulo
        if ($mod != '' && $mod != 'ALL') {
            $cad2 = " AND u.config_id = '".$mod."'";
        }
        // filtro curso
        if ($curso_id != '' && $curso_id != 'ALL') {
            $cad3 = " AND er.curso_id = '".$curso_id."'";
        }
        // filtro grupo
        if ($gru != '' && $gru != 'ALL') {
            $cad4 = " AND u.grupo = '".$gru."'";
        }

        if ($tipopreg != "") {
            $cad5 = " AND er.tipo_pregunta = '".$tipopreg."'";
        }

        // el group by
        $elgroupby = "";
        // if ($tipopreg == 'califica') {
        //     $elgroupby = "GROUP BY ep.id";
        // }

        // consulta maestra
        $ep_result['pgtas'] = DB::select( DB::raw("
            SELECT er.curso_id, er.encuesta_id, er.pregunta_id, er.tipo_pregunta
            FROM encuestas_respuestas er
            INNER JOIN usuarios u ON u.id = er.usuario_id
            ".$cad1."
            ".$cad2."
            ".$cad3."
            ".$cad4."
            ".$cad5."
            ".$elgroupby."
            GROUP BY er.curso_id, er.pregunta_id"));

        // dd($ep_result['pgtas']);

        return $ep_result;
    }

    // Encuestas
    public function encuestas(Request $request){

        $cursos = [];
        $grupos = [];
        $encuestas = Poll::where('active', 1)->select()->get();
        $modulos = Criterion::getValuesForSelect('module');

        // $enc_libres = \DB::select( \DB::raw("SELECT id, titulo FROM encuestas WHERE id IN (SELECT encuesta_id FROM encuestas_respuestas WHERE curso_id <= 0 ) "));
        $enc_libres = [];

        $data = [
            'modulos' => $modulos,
            'encuestas' => $encuestas
        ];

        // Filto
        if ($request->input('t') == 'epc') { // encuesta por curso

            $enc_id = $request->input('encuesta');
            $mod = $request->input('mod');
            $curso_id= $request->input('p');
            $gru = $request->input('g');

            $enc_preg_res = $this->encuesta_filtrada_data(
                $enc_id, $mod, $curso_id, $gru, 'califica'
            );

            if ($mod != '' && $mod != 'ALL') {
                $cursos = $enc_preg_res['cursos'];
            }

            if ($curso_id != '' && $curso_id != 'ALL') {
                $grupos = $enc_preg_res['grupos'];
            }

            $ec_pgtas = $enc_preg_res['pgtas'];

            // return $data;
            $data = [
                'modulos' => $modulos,
                'encuestas' => $encuestas,
                'cursos' => $cursos,
                'grupos' => $grupos,
                'ec_pgtas' => $ec_pgtas
            ];
        }

        return view('resumen_encuesta.index', $data);
    }

    // SELECT Encuestas - cambia "encuesta"
    public function cambiar_encuesta_mod(Request $request)
    {
        $encuesta = $request->input('encuesta');
        $cad1 = "";
        if ($encuesta != '' && $encuesta != 'ALL') {
            $cad1 = "WHERE ce.encuesta_id = '".$encuesta."'";
        }
        $encuestas = DB::select( DB::raw("SELECT id, etapa FROM ab_config WHERE id IN
                                                (SELECT c.config_id FROM curso_encuesta ce
                                                INNER JOIN cursos c
                                                ON ce.curso_id = c.id
                                                ".$cad1."
                                                GROUP BY c.config_id)"
                                            ));

        return $encuestas;
    }
    // SELECT Encuestas - select cambia "curso"
    public function cambia_curso(Request $request)
    {
        $mod = $request->input('mod');
        $encuesta = $request->input('encuesta');

        $cad1 = " WHERE 1 ";
        $cad2 = "";

        if ($mod != '' && $mod != 'ALL') {
            $cad1 = " WHERE c.config_id = '".$mod."' ";
        }
        if ($encuesta != '' && $encuesta != 'ALL') {
            $cad2 = " AND e.encuesta_id = '".$encuesta."' ";
        }

        // $cursos = \DB::select( \DB::raw("SELECT c.id AS id, CONCAT_WS ('///', c.nombre, cat.nombre) AS curso_escuela , c.nombre AS nombre, cat.nombre AS escuela FROM cursos c INNER JOIN categorias cat ON cat.id = c.categoria_id  WHERE categoria_id IN (SELECT id FROM categorias WHERE config_id = ".$mod.") AND c.id IN (SELECT curso_id FROM encuestas_respuestas GROUP BY curso_id) ORDER BY c.nombre"));

        $cursos = DB::select( DB::raw("SELECT c.id, c.nombre, cat.nombre AS escuela, m.codigo_matricula FROM cursos c
                                            INNER JOIN categorias cat ON cat.id = c.categoria_id
                                            INNER JOIN ab_config m ON m.id = c.config_id
                                            INNER JOIN curso_encuesta e
                                            ON e.curso_id = c.id
                                            ".$cad1."
                                            ".$cad2."
                                            AND c.id IN (SELECT curso_id FROM encuestas_respuestas GROUP BY curso_id)
                                            ORDER BY c.orden"
                                        ));

        return $cursos;
    }

    // SELECT  Encuestas - select cambia "grupo"
    public function cambia_grupo(Request $request)
    {
        $tipo = $request->input('tipo');
        $curso = $request->input('curso');
        $encuesta = $request->input('encuesta');

        $cad1 = " WHERE 1 ";
        if ($curso != '' && $curso != 'ALL') {
            $cad1 = " WHERE curso_id = ".$curso;
        }

        // return $curso;
        // if ($tipo == 'epc') {
            $grupos = DB::select( DB::raw("
                SELECT grupo,grupo_nombre FROM usuarios
                WHERE id IN (
                    SELECT usuario_id FROM encuestas_respuestas
                    ".$cad1."
                    AND tipo_pregunta = 'califica')
                GROUP BY grupo order by grupo_nombre asc"
            ));
        // }
        // else{
        //     $grupos = \DB::select( \DB::raw("SELECT id, grupo FROM usuarios WHERE id IN (SELECT usuario_id FROM encuestas_respuestas WHERE curso_id <= 0 AND tipo_pregunta = 'califica') GROUP BY grupo"));
        // }
        return $grupos;
        // return response()->json(['success'=>'Got Simple Ajax Request.']);
    }

    // ENCUESTAS - RESUMEN CALIFICA
    // public static function resumenCalifica($curso_id, $grupo, $enc_id, $pregunta_id){
    public static function resumenCalifica($pregunta_id, $mod, $curso_id, $grupo){


        $cad1 = " WHERE 1 ";
        $cad2 = "";
        $cad3 = "";
        $cad4 = "";

        // filtro
        if ($pregunta_id != '' && $pregunta_id != 'ALL') {
            $cad1 = " WHERE er.pregunta_id = '".$pregunta_id."'";
        }
        // filtro
        if ($mod != '' && $mod != 'ALL') {
            $cad2 = " AND u.config_id = '".$mod."'";
        }
        // filtro
        if ($curso_id != '' && $curso_id != 'ALL') {
            $cad3 = " AND er.curso_id = '".$curso_id."'";
        }
        // filtro
        if ($grupo != '' && $grupo != 'ALL') {
            $cad4 = " AND u.grupo = '".$grupo."'";
        }

        // consulta maestra
        $rptas = DB::select( DB::raw("
            SELECT er.id, er.pregunta_id, er.respuestas
            FROM encuestas_respuestas er
            INNER JOIN usuarios u ON u.id = er.usuario_id
            ".$cad1."
            ".$cad2."
            ".$cad3."
            ".$cad4."
            AND er.tipo_pregunta = 'califica'"
        ));


        ///

        // $where_grupo = "u.grupo = '".$grupo."' ";
        // if ($grupo == 'ALL') {
        //     $where_grupo = '1';
        // }
        // $usuario_ids = \DB::table('encuestas_respuestas AS er')
        //                                     ->join('usuarios AS u', 'u.id', '=', 'er.usuario_id')
        //                                     ->select('u.id')
        //                                     // ->where("u.grupo", $grupo)
        //                                     ->where("er.curso_id", $curso_id)
        //                                     ->where('er.encuesta_id', $enc_id)
        //                                     ->whereRaw($where_grupo)
        //                                     ->groupBy('er.usuario_id')
        //                                     ->pluck('u.id')->toArray();
        // $tot_usu_x_enc = count($usuario_ids);

        // $rptas = \DB::table('encuestas_respuestas')
        //                                     ->select('*')
        //                                     ->where("curso_id", $curso_id)
        //                                     ->where('pregunta_id', $pregunta_id)
        //                                     ->whereIn('usuario_id', $usuario_ids)
        //                                     ->where('tipo_pregunta', 'califica')
        //                                     ->get();

        // $tot_rptas = $rptas->count();
        $tot_rptas = 0;

        $data_rptas = array();

        $criterio_valor_x_rpta = array();

        foreach ($rptas as $item) {
                $tot_rptas += 1;
            // if ($item->tipo_pregunta == 'califica') {

                $valores_rptas = json_decode($item->respuestas, true);

                $suma_cal = 0;
                $sum_t2b = 0;
                $sum_mb = 0;
                $sum_b = 0;
                $sum_r = 0;
                $sum_m = 0;
                $sum_mm = 0;

                foreach ($valores_rptas as $value) {

                    switch (intval($value['resp_cal'])) {
                        case 5:
                            $sum_mb += 1;
                            break;
                        case 4:
                            $sum_b += 1;
                            break;
                        case 3:
                            $sum_r += 1;
                            break;
                        case 2:
                            $sum_m += 1;
                            break;
                        case 1:
                            $sum_mm += 1;
                            break;
                    }

                    $suma_cal += intval($value['resp_cal']);
                    $sum_t2b = $sum_t2b + $sum_mb + $sum_b;

                    // Almacena
                    // $tema =$value['preg_cal'];
                    // $tema = $item->id;
                    $preg_id = $item->pregunta_id;
                    $criterio_valor_x_rpta[$preg_id][] = intval($value['resp_cal']);

                    if (array_key_exists($preg_id, $data_rptas)) {
                        $arr_upd = $data_rptas[$preg_id];

                        $data_rptas[$preg_id]['suma_cal'] = $arr_upd['suma_cal'] + $suma_cal;
                        $data_rptas[$preg_id]['sum_t2b'] = $arr_upd['sum_t2b'] + $sum_t2b;
                        $data_rptas[$preg_id]['sum_mb'] = $arr_upd['sum_mb'] + $sum_mb;
                        $data_rptas[$preg_id]['sum_b'] = $arr_upd['sum_b'] + $sum_b;
                        $data_rptas[$preg_id]['sum_r'] = $arr_upd['sum_r'] + $sum_r;
                        $data_rptas[$preg_id]['sum_m'] = $arr_upd['sum_m'] + $sum_m;
                        $data_rptas[$preg_id]['sum_mm'] = $arr_upd['sum_mm'] + $sum_mm;

                    }else{
                        // $data_rptas[$value['preg_cal']] = array(
                        $data_rptas[$item->pregunta_id] = array(
                            'suma_cal' => ($suma_cal),
                            'sum_t2b' => ($sum_t2b),
                            'sum_mb' => ($sum_mb),
                            'sum_b' => ($sum_b),
                            'sum_r' => ($sum_r),
                            'sum_m' => ($sum_m),
                            'sum_mm' => ($sum_mm)
                        );
                    }

                }

            // }
        }

        // $data = array('tot_rptas'=>$tot_usu_x_enc, 'data_rptas'=>$data_rptas,'criterio_valor_x_rpta'=>$criterio_valor_x_rpta);
        $data = array('tot_rptas'=>$tot_rptas, 'data_rptas'=>$data_rptas,'criterio_valor_x_rpta'=>$criterio_valor_x_rpta);

        // dd($data);
        return $data;
    }


    //******************** EXPORTAR ENCUESTAS ***************************//

    //******************** RESPUESTAS CALIFICA

    // Ver  Encuesta por Grupo y Curso
    public function verEncuentaxGrupoPosteo($enc, $mod, $curso, $grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $data['preguntas_arr'] = PollQuestion::select('id','titulo')->pluck('titulo','id');

            $data['cursos_arr'] = Curso::select('id','nombre')->pluck('nombre','id');
            $cate_cursos = DB::select( "SELECT c.nombre, cu.id FROM categorias c JOIN cursos cu ON c.id = cu.categoria_id");
            $data['categorias_arr'] = collect($cate_cursos)->pluck('nombre','id');

            $enc_preg_res = $this->encuesta_filtrada_x_curso($enc, $mod, $curso, $grupo, 'califica');

            $data['pgtas'] = $enc_preg_res['pgtas'];

            return view('exportar.encuestaxgyp', compact('data'));
        }
        abort(404);
    }
    //Exportar Encuesta por Grupo  y Curso
    public function exportarEncuentaxGrupoPosteo($enc, $mod, $curso,$grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $data['preguntas_arr'] = PollQuestion::select('id','titulo')->pluck('titulo','id');

            $data['cursos_arr'] = Curso::select('id','nombre')->pluck('nombre','id');
            $cate_cursos = DB::select( "SELECT c.nombre, cu.id FROM categorias c JOIN cursos cu ON c.id = cu.categoria_id");
            $data['categorias_arr'] = collect($cate_cursos)->pluck('nombre','id');

            $enc_preg_res = $this->encuesta_filtrada_x_curso($enc, $mod, $curso, $grupo, 'califica');

            $data['pgtas'] = $enc_preg_res['pgtas'];

            // try{
                ob_end_clean();
                ob_start();
                return Excel::download(new EncuestaxgypExport('exportar.encuestas_calificadas_export', $data), 'encuestas-calificadas.xlsx');
            // }catch (\Exception  $e) {
            //     report($e);
            //     // dd($e);
            //     // var_dump($e);
            //     // return $e;
            //     return false;
            // }

        }
        abort(404);
    }
    // Ver  Encuesta por Grupo  y Encuesta libre
    public function verEncuentaxGrupoEnc($enc, $mod, $curso, $grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $data['preguntas_arr'] = PollQuestion::select('id','titulo')->pluck('titulo','id');

            $data['cursos_arr'] = Curso::select('id','nombre')->pluck('nombre','id');
            $cate_cursos = DB::select( "SELECT c.nombre, cu.id FROM categorias c JOIN cursos cu ON c.id = cu.categoria_id");
            $data['categorias_arr'] = collect($cate_cursos)->pluck('nombre','id');

            $enc_preg_res = $this->encuesta_filtrada_x_curso($enc, $mod, $curso, $grupo, 'califica');

            $data['pgtas'] = $enc_preg_res['pgtas'];

            return view('exportar.encuestaxgyp', compact('data'));
        }
        abort(404);
    }
    //Exportar Encuesta por Grupo  y Encuesta libre
    public function exportarEncuentaxGrupoEnc($enc, $mod, $curso, $grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $data['preguntas_arr'] = PollQuestion::select('id','titulo')->pluck('titulo','id');

            $data['cursos_arr'] = Curso::select('id','nombre')->pluck('nombre','id');
            $cate_cursos = DB::select( "SELECT c.nombre, cu.id FROM categorias c JOIN cursos cu ON c.id = cu.categoria_id");
            $data['categorias_arr'] = collect($cate_cursos)->pluck('nombre','id');

            $enc_preg_res = $this->encuesta_filtrada_x_curso($enc, $mod, $curso, $grupo, 'califica');

            $data['pgtas'] = $enc_preg_res['pgtas'];
            ob_end_clean();
            ob_start();
            return Excel::download(new EncuestaxgypExport('exportar.encuestaxgyp', $data), 'encuestas-enclibre-califica.xlsx');
        }
        abort(404);
    }

    //******************** RESPUESTA UNICA

    // Resumen Encuestas - Rpta Unica
    public function verEncPostUnico($enc, $mod, $curso,$grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $enc_preg_res = $this->encuesta_filtrada_data($enc, $mod, $curso, $grupo, 'simple');

            $data['pgtas'] = $enc_preg_res['pgtas'];

            return view('exportar.enc_rpta_simple', compact('data'));
        }
        abort(404);
    }
    public function exportarEncPostUnico($enc, $mod, $curso,$grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $enc_preg_res = $this->encuesta_filtrada_data($enc, $mod, $curso, $grupo, 'simple');
            $data['pgtas'] = $enc_preg_res['pgtas'];
            ob_end_clean();
            ob_start();
            return Excel::download(new EncuestaxgypExport('exportar.enc_rpta_simple', $data), 'encuestas-post-simple.xlsx');
        }
        abort(404);
    }
    public function verEncLibUnico($enc, $mod, $curso, $grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $enc_preg_res = $this->encuesta_filtrada_data($enc, $mod, $curso, $grupo, 'simple');
            $data['pgtas'] = $enc_preg_res['pgtas'];

            return view('exportar.enc_rpta_simple', compact('data'));
        }
        abort(404);
    }
    public function exportarEncLibUnico($enc, $mod, $curso, $grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $enc_preg_res = $this->encuesta_filtrada_data($enc, $mod, $curso, $grupo, 'simple');
            $data['pgtas'] = $enc_preg_res['pgtas'];
            ob_end_clean();
            ob_start();
            return Excel::download(new EncuestaxgypExport('exportar.enc_rpta_simple', $data), 'encuestas-enclibre-simple.xlsx');
        }
        abort(404);
    }

    //******************** RESPUESTA MULTIPLE

    // Resumen Encuestas - Rpta multiple
    public function verEncPostMult($enc, $mod, $curso,$grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $enc_preg_res = $this->encuesta_filtrada_data($enc, $mod, $curso, $grupo, 'multiple');
            $data['pgtas'] = $enc_preg_res['pgtas'];

            return view('exportar.enc_rpta_multi', compact('data'));
        }
        abort(404);
    }
    public function exportarEncPostMult($enc, $mod, $curso,$grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $enc_preg_res = $this->encuesta_filtrada_data($enc, $mod, $curso, $grupo, 'multiple');
            $data['pgtas'] = $enc_preg_res['pgtas'];
            ob_end_clean();
            ob_start();
            return Excel::download(new EncuestaxgypExport('exportar.enc_rpta_multi', $data), 'encuestas-post-multiple.xlsx');
        }
        abort(404);
    }
    public function verEncLibMult($enc, $mod, $curso, $grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $enc_preg_res = $this->encuesta_filtrada_data($enc, $mod, $curso, $grupo, 'multiple');
            $data['pgtas'] = $enc_preg_res['pgtas'];

            return view('exportar.enc_rpta_multi', compact('data'));
        }
        abort(404);
    }
    public function exportarEncLibMult($enc, $mod, $curso, $grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $enc_preg_res = $this->encuesta_filtrada_data($enc, $mod, $curso, $grupo, 'multiple');
            $data['pgtas'] = $enc_preg_res['pgtas'];
            ob_end_clean();
            ob_start();
            return Excel::download(new EncuestaxgypExport('exportar.enc_rpta_multi', $data), 'encuestas-enclibre-multiple.xlsx');
        }
        abort(404);
    }

    //******************** RESPUESTA TEXTO

    // Resumen Encuestas - texto
    public function verEncPostText($enc, $mod, $curso,$grupo){
        if (!empty($enc)) {
            $data = PollQuestionAnswer::with(['pregunta'=>function($q){
                $q->select('id','titulo');
            },'curso'=>function($q){
                $q->select('id','nombre','config_id');
            },'curso.config'=>function($q){
                $q->select('id','etapa');
            }])->where('encuesta_id',$enc)->where('tipo_pregunta','texto');
            // filtro curso
            if ($curso != '' && $curso != 'ALL') {
                $data = $data->where('curso_id',$curso);
            }
            $usuarios = Usuario::select('id');
            // firltro modulo
            if ($mod != '' && $mod != 'ALL') {
                $usuarios = $usuarios->where('config_id',$mod);
            }
            // filtro grupo
            if ($grupo != '' && $grupo != 'ALL') {
                $usuarios = $usuarios->where('grupo',$grupo);
            }
            $usuarios = $usuarios->get();
            if(count($usuarios)>0){
                $data = $data->whereIn('usuario_id',$usuarios);
            }
            $data = $data->get();

            return view('exportar.enc_rpta_texto', compact('data'));
        }
        abort(404);
    }
    public function exportarEncPostText($enc, $mod, $curso,$grupo){
        if (!empty($enc)) {
            $data = PollQuestionAnswer::with(['pregunta'=>function($q){
                $q->select('id','titulo');
            },'curso'=>function($q){
                $q->select('id','nombre','config_id');
            },'curso.config'=>function($q){
                $q->select('id','etapa');
            }])->where('encuesta_id',$enc)->where('tipo_pregunta','texto');
            // filtro curso
            if ($curso != '' && $curso != 'ALL') {
                $data = $data->where('curso_id',$curso);
            }
            $usuarios = Usuario::select('id');
            // firltro modulo
            if ($mod != '' && $mod != 'ALL') {
                $usuarios = $usuarios->where('config_id',$mod);
            }
            // filtro grupo
            if ($grupo != '' && $grupo != 'ALL') {
                $usuarios = $usuarios->where('grupo',$grupo);
            }
            $usuarios = $usuarios->get();
            if(count($usuarios)>0){
                $data = $data->whereIn('usuario_id',$usuarios);
            }
            $data = $data->get();
            ob_end_clean();
            ob_start();
            return Excel::download(new EncuestaxgypExport('exportar.enc_rpta_texto', $data), 'encuestas-post-texto.xlsx');
        }
        abort(404);
    }
    public function verEncLibText($enc, $mod, $curso, $grupo){
        if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $data['preguntas_arr'] = PollQuestion::select('id','titulo')->pluck('titulo','id');

            $data['cursos_arr'] = Curso::select('id','nombre')->pluck('nombre','id');
            $cate_cursos = DB::select( "SELECT c.nombre, cu.id FROM categorias c JOIN cursos cu ON c.id = cu.categoria_id");
            $data['categorias_arr'] = collect($cate_cursos)->pluck('nombre','id');

            $enc_preg_res = $this->encuesta_filtrada_x_curso($enc, $mod, $curso, $grupo, 'texto');

            $data['pgtas'] = $enc_preg_res['pgtas'];

            return view('exportar.enc_rpta_texto', compact('data'));
        }
        abort(404);
    }
    public function exportarEncLibText($enc, $mod, $curso, $grupo){
       if (!empty($enc)) {
            $data['idg'] = $grupo;
            $data['curso_id'] = $curso;
            $data['enc_id'] = $enc;
            $data['mod'] = $mod;

            $data['preguntas_arr'] = PollQuestion::select('id','titulo')->pluck('titulo','id');

            $data['cursos_arr'] = Curso::select('id','nombre')->pluck('nombre','id');
            $cate_cursos = DB::select( "SELECT c.nombre, cu.id FROM categorias c JOIN cursos cu ON c.id = cu.categoria_id");
            $data['categorias_arr'] = collect($cate_cursos)->pluck('nombre','id');

            $enc_preg_res = $this->encuesta_filtrada_x_curso($enc, $mod, $curso, $grupo, 'texto');

            $data['pgtas'] = $enc_preg_res['pgtas'];
            ob_end_clean();
            ob_start();
            return Excel::download(new EncuestaxgypExport('exportar.enc_rpta_texto', $data), 'encuestas-enclibre-texto.xlsx');
        }
        abort(404);
    }

    // Respuestas por cada pregunta (en casos de respuesta de Texto)
    public static function respuestasEncuesta($pregunta_id, $mod, $curso_id, $grupo){
        // $where_grupo = "u.grupo = '".$grupo."' ";
        // if ($grupo == 'ALL') {
        //     $where_grupo = '1';
        // }

        // $usuario_ids = \DB::table('encuestas_respuestas AS er')
        //                                     ->join('usuarios AS u', 'u.id', '=', 'er.usuario_id')
        //                                     ->select('u.id')
        //                                     // ->where("u.grupo", $grupo)
        //                                     ->where("er.curso_id", $curso_id)
        //                                     ->where('er.encuesta_id', $enc_id)
        //                                     ->whereRaw($where_grupo)
        //                                     ->groupBy('er.usuario_id')
        //                                     ->pluck('u.id')->toArray();
        // $tot_usu_x_enc = count($usuario_ids);

        // $rptas = \DB::table('encuestas_respuestas')
        //                                     ->select('*')
        //                                     ->where("curso_id", $curso_id)
        //                                     ->where('pregunta_id', $pregunta_id)
        //                                     ->whereIn('usuario_id', $usuario_ids)
        //                                     ->get();

        ///////////////////////////////
        $cad1 = " WHERE 1 ";
        $cad2 = "";
        $cad3 = "";
        $cad4 = "";

        // filtro
        if ($pregunta_id != '' && $pregunta_id != 'ALL') {
            $cad1 = " WHERE er.pregunta_id = '".$pregunta_id."'";
        }
        // filtro
        if ($mod != '' && $mod != 'ALL') {
            $cad2 = " AND u.config_id = '".$mod."'";
        }
        // filtro
        if ($curso_id != '' && $curso_id != 'ALL') {
            $cad3 = " AND er.curso_id = '".$curso_id."'";
        }
        // filtro
        if ($grupo != '' && $grupo != 'ALL') {
            $cad4 = " AND u.grupo = '".$grupo."'";
        }

        // consulta maestra
        $rptas = DB::select( DB::raw("
            SELECT er.id, er.respuestas
            FROM encuestas_respuestas er
            INNER JOIN usuarios u ON u.id = er.usuario_id
            ".$cad1."
            ".$cad2."
            ".$cad3."
            ".$cad4
        ));

        return $rptas;
    }
}
