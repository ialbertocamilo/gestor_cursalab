<?php

namespace App\Http\Controllers;

use App\Exports\EncuestaxgypExport;
use App\Models\Course;
use App\Models\Criterion;
use App\Models\CriterionValue;
use App\Models\Curso;
use App\Models\Poll;
use App\Models\PollQuestion;
use App\Models\PollQuestionAnswer;
use App\Models\Taxonomy;
use App\Models\Usuario;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{

    public function loadCoursePolls() {



    }

    public function loadModules() {

        // Load modules list

        $_modules = Criterion::getValuesForSelect('module');

        // Add the "etapa" key for every single module

        foreach ($_modules as &$module) {
            $module['etapa'] = $module['nombre'];
        }

        return $_modules;
    }

    public function loadGroups() {

        $groups = Criterion::getValuesForSelect('group');

        // Add the "etapa" key for every single module

        foreach ($groups as &$group) {
            $group['group'] = $group['id'];
            $group['grupo_nombre'] = $group['nombre'];
        }

        return $groups;
    }

    /**
     * @param $moduleId
     * @param $pollId
     * @return Builder[]|Collection
     */
    public function loadCourses($moduleId = null, $pollId = null) {

        if ($pollId) {

            return Course::join('course_poll', 'course_poll.course_id', '=', 'courses.id')
                ->where('course_poll.poll_id', $pollId)
                ->select([
                    'courses.id',
                    'courses.name as nombre',
                    DB::raw('"Nombre escuela" as escuela'),
                    DB::raw('"000" as codigo_matricula'),
                ])
                ->get();

        } else {

            return Course::select([
                'id',
                'name as nombre',
                DB::raw('"Nombre escuela" as escuela'),
                DB::raw('"000" as codigo_matricula'),
            ])
            ->get();
        }
    }

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

        $location = Storage::url($path);

        return compact('location');
    }

    /************************************** ENCUESTAS RESUMEN ***********************************/
    public function encuesta_filtrada_data(
        $pollId, $module, $course_id, $group, $tipopreg
    ){

        $cad1 = " WHERE 1 ";
        $cad2 = "";
        $cad3 = "";
        $cad4 = "";
        $cad5 = "";

        $ep_result = [];

        // filtro encuesta
        if ($pollId != '' && $pollId != 'ALL') {
            $cad1 = " WHERE quest.poll_id = '".$pollId."'";
        }

        // filtro modulo
        if ($module != '' && $module != 'ALL') {

            // filtra lista de cursos
            $cursos = $this->loadCourses();
            $ep_result['cursos'] = $cursos;
        }

        // filtro curso
        if ($course_id != '' && $course_id != 'ALL') {
            $cad3 = " AND ans.course_id = '".$course_id."'";
            $ep_result['grupos'] = $this->loadGroups();
        }

        // filtro grupo
        if ($group != '' && $group != 'ALL') {
            $groupCriterion = Criterion::where('code', 'group')->first();
            // get criterion value id
            $criterionValue = CriterionValue::where('value_text', $group)
                                       ->where('criterion_id', $groupCriterion->id)
                                       ->first();
            $cad4 = " AND crit_val_u.criterion_value_id = " . $criterionValue->id;
        }

        if ($tipopreg != "") {
            // Get id of 'tipo-pregunta' taxonomy
            $taxonomyTipoPregunta = Taxonomy::getFirstData(
                'poll', 'tipo-pregunta', $tipopreg
            );
            $cad5 = " AND quest.type_id = " . $taxonomyTipoPregunta->id;
        }

        // el group by
        $elgroupby = "";

        $ep_result['pgtas'] = DB::select(
            DB::raw("
                SELECT
                    quest.id,
                    quest.poll_id encuesta_id,
                    quest.titulo,
                    ans.course_id curso_id,
                    '' as config_id,
                    '' as grupo, -- getting group name implies more JOINS, this is temporary solution
                    -- quest.tipo_pregunta
                    '" . $tipopreg . "' as tipo_pregunta
                FROM poll_questions quest
                INNER JOIN poll_question_answers ans ON ans.poll_question_id = quest.id
                INNER JOIN users u ON u.id = ans.user_id
                INNER JOIN criterion_value_user crit_val_u ON crit_val_u.user_id = u.id

                    ".$cad1."
                    ".$cad2."
                    ".$cad3."
                    ".$cad4."
                    ".$cad5."
                    ".$elgroupby."
                GROUP BY quest.id"
            )
        );

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


    /**
     * Process request to load polls data
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function encuestas(Request $request){

        $cursos = [];
        $grupos = [];

        // todo: filter polls according workspace?
        $polls = Poll::loadCoursePolls();
        $modules = $this->loadModules();

        $data = [
            'modulos' => $modules,
            'encuestas' => $polls
        ];

        // Load polls data with filter

        if ($request->input('t') == 'epc') { // polls by course

            $pollId = $request->input('encuesta');
            $module = $request->input('mod');
            $courseId = $request->input('p');
            $groupId = $request->input('g');

            $enc_preg_res = $this->encuesta_filtrada_data(
                $pollId, $module, $courseId , $groupId, 'califica'
            );

            if ($module != '' && $module != 'ALL') {
                $cursos = $enc_preg_res['cursos'];
            }

            if ($courseId  != '' && $courseId  != 'ALL') {
                $grupos = $enc_preg_res['grupos'];
            }

            $ec_pgtas = $enc_preg_res['pgtas'];

            // Returns polls data only

            $data = [
                'modulos' => $modules,
                'encuestas' => $polls,
                'cursos' => $cursos,
                'grupos' => $grupos,
                'ec_pgtas' => $ec_pgtas
            ];
        }

        // Returns polls results view

        return view('resumen_encuesta.index', $data);
    }


    /**
     * Process request to load list of modules
     *
     * @param Request $request
     * @return mixed
     */
    public function cambiar_encuesta_mod(Request $request)
    {

        $pollId = $request->input('encuesta');
        return $this->loadModules();



//        $encuesta = $request->input('encuesta');
//        $cad1 = "";
//        if ($encuesta != '' && $encuesta != 'ALL') {
//            $cad1 = "WHERE ce.encuesta_id = '".$encuesta."'";
//        }
//        $encuestas = \DB::select( \DB::raw(
//            "SELECT id, etapa FROM ab_config WHERE id IN
//                (SELECT c.config_id FROM curso_encuesta ce
//                INNER JOIN cursos c
//                ON ce.curso_id = c.id
//                ".$cad1."
//                GROUP BY c.config_id)"
//        ));
//
//        return $encuestas;
    }

    /**
     * Process request to load courses list
     *
     * @param Request $request
     * @return array
     */
    public function cambia_curso(Request $request)
    {
        $moduleId = $request->input('mod');
        $pollId = $request->input('encuesta');

//        $cad1 = " WHERE 1 ";
//        $cad2 = "";
//
//        if ($moduleId != '' && $moduleId != 'ALL') {
//            $cad1 = " WHERE c.config_id = '".$moduleId."' ";
//        }
//        if ($pollId != '' && $pollId != 'ALL') {
//            $cad2 = " AND e.encuesta_id = '".$pollId."' ";
//        }
//
//
//        $cursos = DB::select(
//            DB::raw(
//                "SELECT c.id, c.nombre, cat.nombre AS escuela, m.codigo_matricula
//                FROM cursos c
//                INNER JOIN categorias cat ON cat.id = c.categoria_id
//                INNER JOIN ab_config m ON m.id = c.config_id
//                INNER JOIN curso_encuesta e
//                ON e.curso_id = c.id
//                ".$cad1."
//                ".$cad2."
//                AND c.id IN (
//                    SELECT curso_id FROM encuestas_respuestas GROUP BY curso_id
//                )
//                ORDER BY c.orden"
//            )
//        );


        $cursos = $this->loadCourses($moduleId, $pollId);

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

        return $this->loadGroups();
    }

    // ENCUESTAS - RESUMEN CALIFICA
    // public static function resumenCalifica($curso_id, $grupo, $enc_id, $pregunta_id){
    public static function resumenCalifica(
        $pollQuestionId, $mod, $courseId, $grupo
    ){


        $cad1 = " WHERE 1 ";
        $cad2 = "";
        $cad3 = "";
        $cad4 = "";

        // filtro
        if ($pollQuestionId != '' && $pollQuestionId != 'ALL') {
            $cad1 = " WHERE ans.poll_question_id = '".$pollQuestionId."'";
        }
        // filtro
        if ($mod != '' && $mod != 'ALL') {
            //$cad2 = " AND u.config_id = '".$mod."'";
        }
        // filtro
        if ($courseId != '' && $courseId != 'ALL') {
            $cad3 = " AND ans.course_id = '".$courseId."'";
        }
        // filtro
        if ($grupo != '' && $grupo != 'ALL') {
            $groupCriterion = Criterion::where('code', 'group')->first();
            // get criterion value id
            $criterionValue = CriterionValue::where('value_text', $grupo)
                                        ->where('criterion_id', $groupCriterion->id)
                                        ->first();
            $cad4 = " AND crit_val_u.criterion_value_id = " . $criterionValue->id;
        }

//        $rptas = DB::select( DB::raw("
//            SELECT er.id, er.pregunta_id, er.respuestas
//            FROM encuestas_respuestas er
//            INNER JOIN usuarios u ON u.id = er.usuario_id
//            ".$cad1."
//            ".$cad2."
//            ".$cad3."
//            ".$cad4."
//            AND er.tipo_pregunta = 'califica'"
//        ));

        // Get id of 'tipo-pregunta' taxonomy
        $taxonomyTipoPregunta = Taxonomy::getFirstData(
            'poll', 'tipo-pregunta', 'califica'
        );

        // consulta maestra
        $rptas = DB::select( DB::raw("
            SELECT
                   ans.id,
                   ans.poll_question_id pregunta_id,
                   ans.respuestas
            FROM poll_question_answers ans
            INNER JOIN poll_questions quest ON ans.poll_question_id = quest.id
            INNER JOIN users u ON u.id = ans.user_id
            INNER JOIN criterion_value_user crit_val_u ON crit_val_u.user_id = u.id
            ".$cad1."
            ".$cad2."
            ".$cad3."
            ".$cad4."
            AND quest.type_id = " . $taxonomyTipoPregunta->id
        ));

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
