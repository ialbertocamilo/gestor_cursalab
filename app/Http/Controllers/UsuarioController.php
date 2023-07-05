<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cargo;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Topic;
use App\Models\Botica;
use App\Models\Course;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\School;
use App\Models\Carrera;
use App\Models\Ingreso;
use App\Models\Usuario;
use App\Models\Abconfig;
use App\Models\Criterio;
use App\Models\Reinicio;
use App\Models\Taxonomy;
use App\Models\Categoria;
use App\Models\Criterion;
use App\Models\Matricula;
use App\Models\Workspace;
use App\Models\AssignedRole;
use App\Models\SegmentValue;
use App\Models\SummaryTopic;
use Illuminate\Http\Request;
use App\Models\SummaryCourse;
use App\Services\FileService;
use App\Models\CriterionValue;
use App\Models\Resumen_general;
use App\Models\Resumen_x_curso;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserStoreRequest;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\ApiRest\HelperController;
use App\Http\Resources\Usuario\UsuarioSearchResource;
use App\Http\Controllers\ApiRest\RestAvanceController;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Crypt;

use Altek\Accountant\Facades\Accountant;

// use App\Perfil;

class UsuarioController extends Controller
{

    /**
     * Process request to load authenticated user and its workspace
     */
    public function session(Request $request)
    {

        if (Auth::check()) {

            $user = Auth::user();
            $workspace = session('workspace');
            $workspace['logo'] = FileService::generateUrl($workspace['logo'] ?? '');
            $roles = AssignedRole::getUserAssignedRoles($user->id);

            return [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'fullname' => $user->fullname,
                    'roles' => $roles
                ],
                'session' => [
                    'workspace' => $workspace
                ]
            ];
        }
    }

    /**
     * Process request to update workspace in session
     *
     * @param Request $request
     * @param Workspace $workspace
     */
    public function updateWorkspaceInSession(Request $request, Workspace $workspace)
    {

        // update workspace value in Session

        Session::put('workspace', $workspace);
    }

    public function search(Request $request)
    {
        $workspace = get_current_workspace();
        $sub_workspaces_id = $workspace?->subworkspaces?->pluck('id');

        $request->merge(['sub_workspaces_id' => $sub_workspaces_id, 'superuser' => auth()->user()->isA('super-user')]);

        $users = User::search($request, withAdvancedFilters: true);

        UsuarioSearchResource::collection($users);

        return $this->success($users);
    }

    public function getListSelects()
    {
        $workspace = get_current_workspace();

        $sub_workspaces = Workspace::where('parent_id', $workspace?->id)
            ->select('id', 'name')->get();

        $criteria_workspace = Criterion::select('id', 'name', 'field_id', 'code', 'multiple')
            ->with([
                'field_type:id,name,code',
                'values' => function ($q) use ($workspace) {
                    $q
                        ->select('id', 'criterion_id', 'value_date', 'value_text as name')
                        ->whereRelation('workspaces', 'id', $workspace->id);
                }
            ])
            ->where('is_default', INACTIVE)
            ->whereRelation('workspaces', 'id', $workspace->id)
            ->orderBy('name')
            ->get();

        $criteria_template = Criterion::select('id', 'name', 'field_id', 'code', 'multiple')
            ->with('field_type:id,name,code')
            ->where('is_default', INACTIVE)
            ->whereIn('id', $criteria_workspace->pluck('id'))
            ->orderBy('name')
            ->get();
        $criteriaIds = SegmentValue::loadWorkspaceSegmentationCriteriaIds($workspace->id);
        $users =  CriterionValue::findUsersWithIncompleteCriteriaValues($workspace->id, $criteriaIds);
        $usersWithEmptyCriteria = count($users);
        return $this->success([
            'sub_workspaces' => $sub_workspaces,
            'criteria_workspace' => $criteria_workspace,
            'criteria_template' => $criteria_template,
            'users_with_empty_criteria' => $usersWithEmptyCriteria
        ]);
    }

    public function edit(User $user)
    {
        $user->load('criterion_values');

        $current_workspace_criterion_list = $this->getFormSelects(true);
        $user_criteria = [];

        foreach ($current_workspace_criterion_list as $criterion) {

            $value = $user->criterion_values->where('criterion_id', $criterion->id);

            $user_criterion_value = $criterion->multiple ? $value->pluck('id') : $value?->first()?->id;

            if ($criterion->field_type?->code == 'date') {
                $user_criteria[$criterion->code] = $value?->first()?->value_text;
            } else {
                $user_criteria[$criterion->code] = $user_criterion_value;
            }
        }

//        $criterion_grouped = $user->criterion_values()->with('criterion')->get()
//            ->groupBy('criterion.code')->toArray();
//
//        $criterion_list = [];
//        foreach ($criterion_grouped as $code => $criterion_values) {
//            if (count($criterion_values) == 1 || count($criterion_values) == 0) {
//                $criterion_list[$code] = $criterion_values[0]['id'];
//            } else if(count($criterion_values) > 1){
//                foreach ($criterion_values as $criterion_value) {
//                    $criterion_list[$code][] = [
//                        'id' => $criterion_value['id'],
//                        'value_text' => $criterion_value['value_text']
//                    ];
//                }
//            }
//        }
        $user->criterion_list = $user_criteria;

//        $user->criterion_list = $criterion_grouped;
        return $this->success([
            'usuario' => $user,
            'criteria' => $current_workspace_criterion_list
        ]);
    }

    public function getFormSelects($compactResponse = false)
    {
        $current_workspace = get_current_workspace();
        $criteria = Criterion::query()
            ->with([
                'values' => function ($q) use ($current_workspace) {
                    // $q->with('parents:id,criterion_id,value_text')
                        $q->whereHas('workspaces', function ($q2) use ($current_workspace) {
                            $q2->where('id', $current_workspace->id);
                        });
                        $q->select('id', 'criterion_id', 'exclusive_criterion_id', 'parent_id',
                            'value_text');
                },
                'field_type:id,code'
            ])
            ->whereRelation('workspaces', 'id', $current_workspace?->id)
            ->select('id', 'name', 'code', 'parent_id', 'multiple', 'required','field_id')
            ->orderBy('position')
            ->get();

        $response = compact('criteria');

        return $compactResponse ? $criteria : $this->success($response);
    }

    public function store(UserStoreRequest $request)
    {
        $data = $request->validated();
        // $data['subworkspace_id'] = get_current_workspace()?->id;

        User::storeRequest($data);

        return $this->success(['msg' => 'Usuario creado correctamente.']);
    }

    public function update(UserStoreRequest $request, User $user)
    {
        $data = $request->validated();
        // $data['subworkspace_id'] = get_current_workspace()?->id;
        // info($data);
        User::storeRequest($data, $user);

        return $this->success(['msg' => 'Usuario actualizado correctamente.']);
    }

    public function destroy(Usuario $usuario)
    {
        // \File::delete(public_path().'/'.$usuario->imagen);

        // $usuario->matricula()->delete();
        // $usuario->delete();

        // return back()->with('info', 'Eliminado Correctamente');

        \File::delete(public_path() . '/' . $usuario->imagen);
        $matriculas = Matricula::where('usuario_id', $usuario->id)->select(['id']);
        foreach ($matriculas as $matricula) {
            \Log::info($matricula->id);

            Matricula_criterio::where('matricula_id', $matricula->id)->destroy();
            Matricula::destroy($matricula->id);
        }
        // $usuario->matricula()->delete();
        $usuario->delete();

        return back()->with('info', 'Eliminado Correctamente');
    }

    // ==========================================================================================


    /**
     * List failed ("desaprobados") topics and courses for user
     *
     * @param User $user
     * @return JsonResponse
     */
    public function reset(User $user): JsonResponse
    {

        // $subworkspace = Workspace::find($user->subworkspace_id);
        // $mod_eval = $subworkspace->mod_evaluaciones;
        $courses_id = $user->getCurrentCourses(only_ids:true);
        $topics = SummaryTopic::query()
            ->join('topics', 'topics.id', '=', 'summary_topics.topic_id')
            ->join('courses','courses.id','topics.course_id')
            // ->where('summary_topics.passed', 0)
            // ->where('summary_topics.attempts', '>=', $mod_eval['nro_intentos'] ?? 3)
            ->where('summary_topics.user_id', $user->id)
            ->whereIn('topics.course_id',$courses_id)
            ->whereNotNull('summary_topics.attempts')
            ->where('summary_topics.attempts', '>=', DB::raw('CONVERT(courses.mod_evaluaciones->"$.nro_intentos",UNSIGNED)'))
            ->whereRelationIn('status', 'code', ['desaprobado', 'por-iniciar'])
            ->select('topics.id', 'topics.name')
            ->get();
            // ->filter(function ($summary) {
            //     $course = Course::select('mod_evaluaciones')->where('id',$summary->course_id);

            // });

        $courses = [];

        return $this->success(
            compact('topics', 'courses', 'user')
        );
    }

    /**
     * Reset by topic
     *
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function reset_x_tema(User $user, Request $request)
    {
        $admin = null;
        if (Auth::check()) {

            $admin = Auth::user();
        }

        if ($request->has('p')) {

            $topicId = $request->input('p');

            $topic = Topic::find($topicId);

            // Reset topics attempts

            SummaryTopic::resetUserTopicsAttempts($user->id, [$topicId]);

            // Update restarts count

            SummaryTopic::updateTopicRestartsCount(
                $topicId, $user->id, $admin->id
            );

            SummaryCourse::updateCourseRestartsCount($topic->course_id, $admin->id, $user->id);

            return $this->success(['msg' => 'Reinicio por tema exitoso']);
        }

        return $this->error('No se pudo completar el reinicio');
    }

    public function reset_x_curso(User $user, Request $request)
    {
        $admin = auth()->user();
        $course = Course::find($request->c);

        if ($course) {

            $summary_course = SummaryCourse::getCurrentRow($course, $user);

            $summary_topics = SummaryTopic::where('user_id', $user->id)
                ->where('passed', '<>', 1)
                ->whereRelation('topic', 'course_id', $course->id)
                ->get();

            $summary_topics->increment('restarts', 1, ['attempts' => 0, 'restarter_id' => $admin->id]);

            $course->increment('restarts', 1, ['restarter_id' => $admin->id]);

            return $this->success(['msg' => 'Reinicio por curso exitoso']);
        }

        return $this->error('No se pudo completar el reinicio');
    }

    public function reset_total(Usuario $usuario)
    {
        // $rptas = $usuario->rpta_pruebas()->get();
        // $rptas = $usuario->rpta_encuestas()->delete();

        // $rptas = $usuario->rpta_pruebas()->delete();

        $rptas = $usuario->rpta_pruebas()
            ->update([
                'intentos' => 0,
                // 'rptas_ok' => NULL,
                // 'rptas_fail' => NULL,
                // 'nota' => NULL,
                //  'resultado' => NULL,
                //  'usu_rptas' => NULL,
                'fuente' => 'reset'
            ]);

        // Registrar reinicios
        $user = \Auth::user();
        $res = Reinicio::where('usuario_id', $usuario->id)->where('tipo', 'total')->first();
        if (!$res) {
            $dip = new Reinicio;
            $dip->tipo = 'total';
            $dip->usuario_id = $usuario->id;
            $dip->admin_id = $user->id;
            $dip->acumulado = 1;
            $dip->save();
        } else {
            $res->acumulado = $res->acumulado + 1;
            $res->save();
        }
        // Fin registro reinicios
        return $this->success(['msg' => 'Reinicio total exitoso']);
    }

    public function crear(Request $request)
    {
        // return $request->all();
//        info($request->all());
        if ($request->usuario['id'] == 0) {

            $already_exist = Usuario::where('dni', trim($request->usuario['dni']))->first();

            if ($already_exist)
                return response()->json(['error' => true, 'msg' => 'El DNI ya ha sido registrado.'], 422);

            $modulo = Abconfig::findOrFail($request->usuario['modulo']['id']);

            $codigo = $modulo->codigo_matricula . '-' . date('dmy');
            $grupo_sistema = Grupo::firstOrCreate(['nombre' => $codigo, 'estado' => 1]);

            // CREAR
            $nuevo_usuario = new Usuario();
            $nuevo_usuario->config_id = $request->usuario['modulo']['id'];
            $nuevo_usuario->nombre = $request->usuario['nombre'];
            $nuevo_usuario->grupo_id = $grupo_sistema->id;
            // $nuevo_usuario->grupo_id = $request->usuario['grupo_sistema'];
//            $nuevo_usuario->botica_id =  $request->usuario['botica_id'];
            $nuevo_usuario->botica_id = $request->usuario['botica']['id'];
//            $botica = Botica::with('criterio')->where('id', $request->usuario['botica_id'])->first();
            $botica = Botica::with('criterio')->where('id', $request->usuario['botica']['id'])->first();
            $nuevo_usuario->botica = $botica->nombre;
            $nuevo_usuario->grupo = $botica->criterio_id;
            $nuevo_usuario->grupo_nombre = $botica->criterio->valor;
            $nuevo_usuario->dni = $request->usuario['dni'];
            $nuevo_usuario->password = Hash::make($request->usuario['password']);
            $nuevo_usuario->sexo = $request->usuario['sexo'] == 'M' ? 'MASCULINO' : 'FEMENINO';
            $nuevo_usuario->cargo = $request->usuario['cargo'];

            $nuevo_usuario->estado = $request->usuario['estado'];
            $nuevo_usuario->save();

            // //Consultar dias de configuracion
            // $dias = $nuevo_usuario->config->duracion_dias;
            // date_default_timezone_set('America/Lima');
            // //insertar vigencia
            // $vigencia = new Usuario_vigencia;
            // $vigencia->usuario_id = $nuevo_usuario->id;
            // $vigencia->fecha_inicio = date('Y-m-d');
            // $vigencia->fecha_fin = date('Y-m-d', strtotime($vigencia->fecha_inicio. ' + '.$dias.' days'));
            // $vigencia->save();

            // ENCONTRAR EL ULTIMO CICLO ACTIVO
            $ciclos = $request->ciclos;
            $_ciclos = collect($request->ciclos);
            $ciclos_activos = $_ciclos->where('estado', true);
            $ultimo_ciclo_activo = $ciclos_activos->last();

            foreach ($ciclos as $ciclo) {
                $matricula = new Matricula;
                $matricula->usuario_id = $nuevo_usuario->id;
                $matricula->carrera_id = $request->carrera_id;
                $matricula->ciclo_id = $ciclo['id'];
                $matricula->secuencia_ciclo = $ciclo['secuencia'];
                $matricula->presente = ($ultimo_ciclo_activo['id'] == $ciclo['id']) ? 1 : 0;
                if (count($ciclos_activos) == 1 && $ciclo['estado'] == true) {
                    $matricula->estado = 1;
                } else if ($ciclo['secuencia'] == 0) {
                    $matricula->estado = 0;
                } else {
                    $matricula->estado = $ciclo['estado'] ? 1 : 0;
                }
                $matricula->save();
                DB::table('matricula_criterio')->insert([
                    'matricula_id' => $matricula->id,
                    'criterio_id' => $request->grupo
                ]);
            }
            $hel = new HelperController();
            Resumen_general::insert([
                'usuario_id' => $nuevo_usuario->id,
                'cur_asignados' => count($hel->help_cursos_x_matricula($nuevo_usuario->id)),
            ]);
            return $this->success(['msg' => 'Usuario creado con éxito.']);
//            return response()->json(['msg'=> 'Usuario creado con éxito.'], 200);
        } else {
            // return $request->all();
            // EDITAR
            $usuario_editar = Usuario::find($request->usuario['id']);
//            $usuario_editar->config_id = $request->usuario['modulo'];
            $usuario_editar->nombre = $request->usuario['nombre'];
            // $usuario_editar->grupo_id = $request->usuario['grupo_sistema'];
            if ($usuario_editar->dni !== trim($request->usuario['dni'])) {
                $already_exist = Usuario::where('dni', trim($request->usuario['dni']))->first();

                if ($already_exist)
                    return response()->json(['msg' => 'El DNI ya ha sido registrado.'], 422);

                $usuario_editar->dni = trim($request->usuario['dni']);
            }

            if (isset($request->usuario['password']) and $request->usuario['password'] != "") {
                $usuario_editar->password = Hash::make($request->usuario['password']);
            }

            $usuario_editar->sexo = $request->usuario['sexo'] == 'M' ? 'MASCULINO' : 'FEMENINO';
            $usuario_editar->cargo = $request->usuario['cargo'];
            $usuario_editar->estado = $request->usuario['estado'];
//            $usuario_editar->botica_id =  $request->usuario['botica_id'];
            $usuario_editar->botica_id = $request->usuario['botica']['id'];
//            $botica = Botica::with('criterio')->where('id', $request->usuario['botica_id'])->first();
            $botica = Botica::with('criterio')->where('id', $request->usuario['botica']['id'])->first();
            $usuario_editar->botica = $botica->nombre;
            $usuario_editar->grupo = $botica->criterio_id;
            $usuario_editar->grupo_nombre = $botica->criterio->valor;
            $usuario_editar->save();
            // //Consultar dias de configuracion
            // $dias = $usuario_editar->config->duracion_dias;
            // date_default_timezone_set('America/Lima');
            // //insertar vigencia
            // $vigencia = $usuario_editar->vigencia;
            // $vigencia->usuario_id = $usuario_editar->id;
            // $vigencia->fecha_inicio = date('Y-m-d');
            // $vigencia->fecha_fin = date('Y-m-d', strtotime($vigencia->fecha_inicio. ' + '.$dias.' days'));
            // $vigencia->save();

            // ENCONTRAR EL ULTIMO CICLO ACTIVO
            $ciclos = $request->ciclos;
            $_ciclos = collect($request->ciclos);
            $ciclos_activos = $_ciclos->where('estado', true);
            $ultimo_ciclo_activo = $ciclos_activos->last();

            foreach ($ciclos as $ciclo) {
                $matricula = Matricula::find($ciclo['matricula_id']);
                $matricula->presente = ($ultimo_ciclo_activo['id'] == $ciclo['id']) ? 1 : 0;
                if (count($ciclos_activos) == 1 && $ciclo['estado'] == true) {
                    $matricula->estado = 1;
                } else if ($ciclo['secuencia'] == 0) {
                    $matricula->estado = 0;
                } else {
                    $matricula->estado = $ciclo['estado'] ? 1 : 0;
                }
                $matricula->save();
            }
            $helper = new HelperController();
            $curso_ids_matricula = $helper->help_cursos_x_matricula($usuario_editar->id);
            //LIMPIAR TABLAS RESUMENES
            Resumen_x_curso::where('usuario_id', $usuario_editar->id)->whereNotIn('curso_id', $curso_ids_matricula)->update(['estado_rxc' => 0]);
            // Resumen_general::where('usuario_id',$usuario_editar->id)->delete();
            //ACTUALIZAR TABLAS RESUMENES
            $rest_avance = new RestAvanceController();
            $ab_conf = Abconfig::select('mod_evaluaciones')->where('id', $request->usuario['modulo'])->first(['mod_evaluaciones']);
            $mod_eval = json_decode($ab_conf->mod_evaluaciones, true);
            foreach ($curso_ids_matricula as $cur_id) {
                // ACTUALIZAR RESUMENES
                $rest_avance->actualizar_resumen_x_curso($usuario_editar->id, $cur_id, $mod_eval['nro_intentos']);
            }
            $rest_avance->actualizar_resumen_general($usuario_editar->id);
            return $this->success(['msg' => 'Usuario actualizado con éxito.']);
//            return response()->json(['msg'=> 'Usuario actualizado con éxito.'], 200);

        }
    }

    public function getCoursesByUser(User $user)
    {
        // Update flag to force update users courses

        $user->required_update_at = now();
        $user->save();

        info('getCoursesByUser INICIO');
        $courses = $user->getCurrentCourses(withRelations: 'course-view-app-user');
        info('getCoursesByUser FIN');

        info('getDataToCoursesViewAppByUser INICIO');
        $schools = Course::getDataToCoursesViewAppByUser($user, $courses);
        info('getDataToCoursesViewAppByUser FIN');

        return $this->success([
            'user' => [
                'id' => $user->id,
                'fullname' => $user->fullname,
                'schools' => $schools
            ]
        ]);
    }

    public function status($usuario_id)
    {
        $user = Usuario::find($usuario_id);
        $estado = ($user->estado == 1) ? 0 : 1;
        $user->estado = $estado;
        $user->save();
        return back()->with(['info' => 'Estado actualizado con éxito.']);
    }

    public function updateStatus(User $user)
    {
        // info(!$user->active);
        $status = ($user->active == 1) ? 0 : 1;

        $current_workspace = get_current_workspace();

        if ($status && !$current_workspace->verifyLimitAllowedUsers()){
            $error_msg = config('errors.limit-errors.limit-user-allowed');

            return $this->error($error_msg, 422);
        }

        $user->update(['active' => $status]);

        return $this->success(['msg' => 'Estado actualizado correctamente.']);
    }

    /*

        REINICIO MASIVOS DE NOTAS POR CURSO

     =========================================================================*/

    public function index_reinicios()
    {
        return view('masivo.index_reinicios');
    }

    public function reinicios_data()
    {
        // Get workspace saved in session
        $workspace = get_current_workspace();
        // Load modules
        $modules = Workspace::where('parent_id', $workspace->id)
            ->select('id', 'name')
            ->get();
        $modules_id = $workspace->subworkspaces->pluck('id')->toArray();
        // Load workspace's schools
        $schools = School::whereHas('subworkspaces', function ($j) use ($modules_id) {
            $j->whereIn('subworkspace_id', $modules_id);
        })->get();
        return response()->json(compact('schools', 'modules'), 200);
    }

    public function buscarCursosxEscuela($school_id)
    {
        $cursos = Curso::join('course_school', 'course_school.course_id', '=', 'courses.id')
            ->where('course_school.school_id', $school_id)
            ->whereNull('courses.deleted_at')
            ->where('courses.active', ACTIVE)
            ->select('courses.*')
            ->get();

        return response()->json(compact('cursos'), 200);
    }

    public function buscarTemasxCurso($course_id)
    {
        $posteos = Posteo::where('course_id', $course_id)
            ->get();

        return response()->json(compact('posteos'), 200);
    }

    public function validarReinicioIntentos(Request $request)
    {

        $tipo = $request->tipo; // TODOS o SOLO DESAPROBADOS
        $admin = $request->admin;
        $curso = $request->curso;
        $tema = $request->tema;
        $subworkspaceId = $request->modulo;

        // Load workspace configuration

        // $subworkspace = Workspace::find($subworkspaceId);
        // $mod_eval = $subworkspace->mod_evaluaciones;
        $mod_eval = Course::getModEval($curso);

        $data = $this->validarDetallesReinicioIntentosMasivo(
            $curso, $tema, $subworkspaceId, $tipo, $mod_eval
        );

        return response()->json([
            'data' => $data,
            'mod_eval' => $mod_eval
        ], 200);
    }

    public function validarDetallesReinicioIntentosMasivo(
        $courseId, $topicId, $subworkspaceId, $tipo, $mod_eval
    ): array
    {
        $attempts = $mod_eval['nro_intentos'] ?? 3;

        if ($topicId == null) {

            // $query = SummaryCourse::query()
            //     ->join('users', 'users.id', '=', 'summary_courses.user_id')
            //     ->with('user')
            //     ->where('summary_courses.course_id', $courseId)
            //     ->where('users.subworkspace_id', $subworkspaceId)
            //     ->select('summary_courses.*');

            // "Desaprobados" only
            $query = SummaryTopic::query()
                    ->join('users', 'users.id', '=', 'summary_topics.user_id')
                    ->with('user')
                    // ->where('summary_topics.source_id')
                    ->where('users.subworkspace_id', $subworkspaceId)
                    ->select('summary_topics.*')
                    ->whereHas('topic',function($q) use ($courseId){
                        $q->where('course_id',$courseId)->where('active',ACTIVE);
                    });
            if ($tipo['id'] == 1) {

                // Get "Desaprobado" status from taxonomies

                $desaprobado = Taxonomy::getFirstData('topic', 'user-status', 'desaprobado');

                // Add conditions to filter "desaprobados" only

                $query->where('summary_topics.status_id', $desaprobado->id)
                    ->where('summary_topics.attempts', '>=', $attempts);
            }

            $users = $query->orderBy('summary_topics.grade')->get();

        } else {

            $query = SummaryTopic::query()
                ->join('users', 'users.id', '=', 'summary_topics.user_id')
                ->with('user')
                ->where('summary_topics.topic_id', $topicId)
                // ->where('summary_topics.source_id')
                ->where('users.subworkspace_id', $subworkspaceId)
                ->select('summary_topics.*');

            // "Desaprobados" only

            if ($tipo['id'] == 1) {

                // Get "Desaprobado" status from taxonomies

                $desaprobado = Taxonomy::getFirstData('topic', 'user-status', 'desaprobado');

                // Add conditions to filter "desaprobados" only

                $query->where('summary_topics.status_id', $desaprobado->id)
                    ->where('summary_topics.attempts', '>=', $attempts);
            }

            $users = $query->orderBy('summary_topics.grade')->get();
        }

        // Add "selected" property to each item

        foreach ($users as $key => $user) {
            $user->selected = false;
        }

        return [
            'pruebas' => $users,
            'count_usuarios' => $users->count()
        ];
    }

    public function reiniciarIntentosMasivos(Request $request): JsonResponse
    {

        $admin = $request->admin;
        $subworkspaceId = $request->modulo;
        $users = $request->usuarios;
        $usersCount = count($users);
        $topicId = $request->tema;
        $courseId = $request->curso;

        // Load workspace's "evaluaciones" configuration

        // $subworkspace = Workspace::find($subworkspaceId);
        // $mod_eval = $subworkspace->mod_evaluaciones;
        $mod_eval = Course::getModEval($courseId);
        if ($topicId == null) {

            $curso = Curso::where('id', $courseId)->first();
            $this->reinicioMasivoxCurso(
                $courseId, $admin['id'], $users
            );
            $msg = "Se reiniciaron los intentos de " . $usersCount . " usuario(s) para el curso $curso->name.";

        } else {

            $topic = Posteo::where('id', $topicId)->first();
            $this->reinicioMasivoxTema(
                $topicId, $admin['id'], $users
            );
            $msg = "Se reiniciaron los intentos de " . $usersCount . " usuario(s) para el tema $topic->name.";
        }

        return response()->json([
            'data' => [],
            'mod_eval' => $mod_eval,
            'msg' => $msg
        ], 200);
    }

    /**
     * Reset attempts by course
     *
     * @param $courseId
     * @param $adminId
     * @param $users
     * @return void
     */
    public function reinicioMasivoxCurso($courseId, $adminId, $users): void
    {

        foreach ($users as $key => $user) {

            $userId = $user['user']['id'];

            // Reset course attempts

            SummaryCourse::resetUserCoursesAttempts($userId, [$courseId]);

            // Update restarts count

            SummaryCourse::updateCourseRestartsCount(
                $courseId, $adminId, $userId
            );

            // Reset topics attempts

            $topicsIds = SummaryCourse::getCourseTopicsIds($courseId, $userId);

            SummaryTopic::resetUserTopicsAttempts($userId, $topicsIds);

            // Update resets count

            foreach ($topicsIds as $topicId) {
                SummaryTopic::updateTopicRestartsCount(
                    $topicId, $userId, $adminId
                );
            }
        }
    }

    /**
     * Reset attempts by topic
     *
     * @param $topicId
     * @param $adminId
     * @param $users
     * @return void
     */
    public function reinicioMasivoxTema($topicId, $adminId, $users)
    {
        foreach ($users as $key => $user) {

            $userId = $user['user']['id'];

            // Reset topics attempts

            SummaryTopic::resetUserTopicsAttempts($userId, [$topicId]);

            // Update restarts count

            SummaryTopic::updateTopicRestartsCount(
                $topicId, $userId, $adminId
            );
        }
    }

    public function updatePasswordUser(ResetPasswordRequest $request)
    {
        $data = $request->validated();

        $request->validated();

        $actualPassword = $request->currpassword;
        $currentPassword = $request->password;
        $currentRePassword = $request->repassword;

        $user = auth()->user();
        // verficamos su actual contraseña
        // if(!Auth::attempt([ 'email' => $user->email,
        //                     'password' => $actualPassword])) {

        //     throw ValidationException::withMessages([
        //         'currpassword' => 'La contraseña actual no coincide.'
        //     ]);
        // }
        // verficamos que no sea la misma
        if($actualPassword === $currentPassword) {
            throw ValidationException::withMessages([
                'password' => 'La nueva contraseña debe ser distinta a la actual.',
            ]);
        }

        if($currentPassword === $currentRePassword) {
            $user->updatePasswordUser($currentPassword);

            return redirect('/reset_password')
                             ->with('info', 'Contraseña actualizada correctamente');
        }

        throw ValidationException::withMessages([
            'password' => 'El campo nueva contraseña no coincide.',
            'repassword' => 'El campo repetir nueva contraseña no coincide.'
        ]);
    }

    public function resetPassword(User $user, Request $request)
    {
        $data = [
            'password' => $user->document,
            'last_pass_updated_at' => now(),
            'attempts' => 0,
            'attempts_lock_time' => NULL,
        ];

        $user->update($data);

        return $this->success(['msg' => 'Contraseña restaurada correctamente.']);
    }

    public function getSignature(User $user, Request $request)
    {
        $enabled = config('app.impersonation.enabled');
        $duration = config('app.impersonation.link_duration');

        if (!$enabled) return $this->error('Service not available.', 422, [['Servicio no disponible.']]);

        $token = $user->id . '-' . auth()->user()->id;

        $token = Crypt::encryptString($token);

        $signed_url = URL::temporarySignedRoute(
            'login.external', now()->addSeconds($duration), ['token' => $token]
        );

        $parts = parse_url($signed_url);
        parse_str($parts['query'], $query);

        $signature = $query['signature'] ?? null;
        $expires = $query['expires'] ?? null;

        $web_url = config('app.web_url');

        $url = $web_url . "auth/login/external?token={$token}&expires={$expires}&signature={$signature}";

        Accountant::record($user, 'impersonated');

        $data = compact('url', 'signature', 'expires', 'token', 'signed_url');

        return $this->success(['msg' => 'Autenticando...', 'config' => $data]);
    }
}
