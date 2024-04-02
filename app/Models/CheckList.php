<?php

namespace App\Models;

use App\Http\Controllers\ApiRest\HelperController;
use Illuminate\Support\Facades\Auth;

class CheckList extends BaseModel
{
    protected $table = 'checklists';

    protected $fillable = [
        'title',
        'description',
        'active',
        'workspace_id',
        'type_id',
        'starts_at',
        'finishes_at',
        'platform_id'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function checklist_actividades()
    {
        return $this->hasMany(CheckListItem::class, 'checklist_id', 'id');
    }

    public function actividades()
    {
        return $this->hasMany(CheckListItem::class, 'checklist_id', 'id');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'checklist_relationships', 'checklist_id', 'course_id');
    }
    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    /*======================================================= SCOPES ==================================================================== */

    public function scopeActive($q, $estado)
    {
        return $q->where('active', $estado);
    }

    public function scopeFilterByPlatform($q){
        $platform = session('platform');
        $type_id = $platform && $platform == 'induccion'
                    ? Taxonomy::getFirstData('project', 'platform', 'onboarding')->id
                    : Taxonomy::getFirstData('project', 'platform', 'training')->id;
        $q->where('platform_id',$type_id);
    }

    /*=================================================================================================================================== */

    protected function gridCheckList($data)
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $workspace = get_current_workspace();

        $queryChecklist = CheckList::FilterByPlatform()->where('workspace_id', $workspace->id);

        $field = 'created_at';
        $sort = 'DESC';

        $queryChecklist->orderBy($field, $sort);

        if (!is_null($filtro) && !empty($filtro)) {
            $queryChecklist->where(function ($query) use ($filtro) {
                $query->where('checklists.title', 'like', "%$filtro%");
                $query->orWhere('checklists.description', 'like', "%$filtro%");
            });
        }
        $checklists = $queryChecklist->paginate(request('paginate', 15));

        $response['data'] = $checklists->items();
        $response['lastPage'] = $checklists->lastPage();
        $response['current_page'] = $checklists->currentPage();
        $response['first_page_url'] = $checklists->url(1);
        $response['from'] = $checklists->firstItem();
        $response['last_page'] = $checklists->lastPage();
        $response['last_page_url'] = $checklists->url($checklists->lastPage());
        $response['next_page_url'] = $checklists->nextPageUrl();
        $response['path'] = $checklists->getOptions()['path'];
        $response['per_page'] = $checklists->perPage();
        $response['prev_page_url'] = $checklists->previousPageUrl();
        $response['to'] = $checklists->lastItem();
        $response['total'] = $checklists->total();

        return $response;
    }

    protected function getChecklistById($checklist_id)
    {
        $response['checklist'] = null;

        $workspace = get_current_workspace();

        $checklist = CheckList::with([
            'checklist_actividades' => function ($q) {
                $q->orderBy('active', 'desc')->orderBy('position');
            },
            'courses' => function ($q) {
                $q->select('courses.id', 'courses.name');
            }
        ])
        ->where('workspace_id', $workspace->id)
        ->where('id', $checklist_id)
        ->first();

        if(!is_null($checklist)) {
            foreach ($checklist->checklist_actividades as $act) {
                $type_id = $act->type_id ?? null;
                $type_name = !is_null($type_id) ? Taxonomy::where('id', $type_id)->first() : null;
                $type_name = !is_null($type_name) ? $type_name->code : '';
                $act->type_name = $type_name;
            }
            foreach ($checklist->courses as $curso) {
                $workspace = !is_null($curso->workspaces) && count($curso->workspaces) ? $curso->workspaces[0]->name : '';
                $school = !is_null($curso->schools) && count($curso->schools) ? $curso->schools[0]->name : '';

                $curso->modulo = $workspace;
                $curso->curso = $curso->name;
                $curso->escuela = $school;
                $curso->nombre = $workspace . ' - ' . $school . ' - ' . $curso->name;
            }

            $criteria = Segment::getCriteriaByWorkspace(get_current_workspace());
            $segments = Segment::getSegmentsByModel($criteria, CheckList::class, $checklist->id);

            $checklist->segments = $segments;

            $segmentation_by_document_list = [];
            $segmentation_by_document = $segments->map(function ($item) {
                return ['segmentation_by_document'=> $item->segmentation_by_document];
            });

            foreach ($segmentation_by_document as $seg) {
                foreach ($seg['segmentation_by_document'] as $value) {
                    array_push($segmentation_by_document_list, $value);
                }
            }
            $checklist->segmentation_by_document = ['segmentation_by_document'=> $segmentation_by_document_list];

            $checklist->active = $checklist->active;
            $checklist->is_super_user = auth()->user()->isAn('super-user');
            $type_checklist = Taxonomy::where('id', $checklist->type_id)->first();
            $checklist->type_checklist = $type_checklist?->code;
        }

        $response['checklist'] = $checklist;

        return $response;
    }
    protected function getChecklistsByAlumno($alumno_id){
        $user = User::where('id', $alumno_id)->first();
        $entrenador = EntrenadorUsuario::where('user_id', $alumno_id)->where('active', 1)->first();
        $entrenador_id = !is_null($entrenador) ? $entrenador->trainer_id : null;
        $checklists_pendientes = collect();
        $checklists_realizados = collect();
        $checklists_response = collect();
        $checklistCompletados = 0;

        $checklists_assigned  = SummaryUserChecklist::getChecklistByUser(
                        $user,['id','title','description','active','type_id'],
                        withChecklistFreeRelations:['actividades:id,checklist_id,type_id,activity,position,active'],
                        withChecklistCourseRelations:[
                            'actividades:id,checklist_id,type_id,activity,position,active',
                        ],
                        mergeChecklist:true,
                    );
        $checklists_taxonomies = Taxonomy::select('id','name','code','type')->where('group','checklist')->get();
        $statuses_course = Taxonomy::select('id','name','code')->where('group','course')->where('type','user-status')->get();
        $aprobado = $statuses_course->where('code','aprobado')->first();
        $tax_trainer_user = $checklists_taxonomies->where('type','type')->where('code','trainer_user')->first();
        $tax_user_trainer = $checklists_taxonomies->where('type','type')->where('code','user_trainer')->first();

        $summaries_course_checklist = SummaryCourse::where('user_id', $alumno_id)
            ->whereIn('course_id',$checklists_assigned->pluck('courses.*.id')->flatten())
            ->select('id','course_id','status_id')
            ->get();
        $checklist_rptas_user = ChecklistRpta::select('id','feedback_entrador','coach_id','student_id','checklist_id','flag_congrats','percent')->with('rpta_items:id,checklist_answer_id,checklist_item_id,qualification')->alumno($alumno_id)->entrenador($entrenador_id)->get();
        foreach ($checklists_assigned as $checklist) {
            $type_checklist = $checklists_taxonomies->where('type','type_checklist')->where('id', $checklist->type_id)->first();
            $actividades_activas = $checklist->actividades->where('active', 1)->where('type_id', $tax_trainer_user->id)->sortBy('position');
            $actividades_activasFeedback = $checklist->actividades->where('active', 1)->where('type_id', $tax_user_trainer->id)->sortBy('position');
            if ($actividades_activas->count() > 0 && $checklist->active) {
                // $r_x_c = $summaries_course_checklist->where('course_id', $checklist->id)->first();
                // $disponible = $r_x_c && $r_x_c->status_id === $aprobado->id;
                $checklistRpta = $checklist_rptas_user->where('checklist_id',$checklist->id)->first();
                if (!$checklistRpta) {
                    $checklistRpta = ChecklistRpta::create([
                        'checklist_id' => $checklist->id,
                        'student_id' => $alumno_id,
                        // 'course_id' => $curso->id, //deprecated
                        // 'school_id' => $curso->categoria['id'], //deprecated
                        'coach_id' => $entrenador_id,
                        'percent' => 0
                    ]);
                }
                $progresoActividad = $this->getProgresoActividades($checklist, $checklistRpta, $actividades_activas,$checklists_taxonomies);
                $progresoActividadFeedback = $this->getProgresoActividadesFeedback($checklistRpta, $actividades_activasFeedback,$checklists_taxonomies);

                // $lista_cursos = $checklist->courses->with([
                //     'schools' => function ($query) {
                //         $query->select('id', 'name');
                //     }
                // ])->select('id', 'name')->get();
                $disponible = true;
                if($type_checklist?->code == 'curso'){
                    $courses_id = $checklist->courses->pluck('id')->toArray();
                    if(count($courses_id)>0){
                        //code...
                        $disponible = count($courses_id) ==  $summaries_course_checklist->where('status_id',$aprobado->id)->filter(function($q) use ($courses_id) {
                            return in_array($q->course_id, $courses_id);
                        })->count();

                    }
                }
                $lista_cursos = $checklist->courses->map(function($course) use ($user,$summaries_course_checklist,$statuses_course){
                    $status = 'Por validar';
                    $summary_course = $summaries_course_checklist->where('course_id',$course->id)->first();
                    if($summary_course){
                        $status = $statuses_course->where('id',$summary_course->status_id)->first()?->name ?? 'Por validar';
                    }
                    return [
                        'id' =>$course->id,
                        'name' => $course->name,
                        'status' => $status,
                        // 'status' => Course::getCourseStatusByUser($user, $course)['status'],
                        'schools' => $course->schools->map(fn($school)=> ['id'=>$school->id,'name'=>$school->name])
                    ];
                });

                // foreach($lista_cursos as $lc) {
                //     // $lc->makeHidden(['summaries', 'polls', 'requirements', 'pivot']);
                //     $status_c = Course::getCourseStatusByUser($user, $lc);
                //     $lc->status_c = $status_c['status'];
                // }
                $tempChecklist = [
                    'id' => $checklist->id,
                    'titulo' => $checklist->title,
                    'descripcion' => $checklist->description,
                    'type_checklist' => $type_checklist?->code,
                    'disponible' => $disponible,
                    'curso' => $lista_cursos,
                    'porcentaje' => $progresoActividad['porcentaje'],
                    'actividades_totales' => $progresoActividad['actividades_totales'],
                    'actividades_completadas' => $progresoActividad['actividades_completadas'],
                    'actividades' => collect($progresoActividad['actividades']),
                    'actividades_feedback' => $progresoActividadFeedback['actividades_feedback'],
                    'feedback_disponible' => $progresoActividad['feedback_disponible'],
                    'feedback_entrador'=> $checklistRpta?->feedback_entrador,
                    'mostrar_modal' => false
                ];
                if ($tempChecklist['porcentaje'] === 100.00) {
                    $tempChecklist['mostrar_modal'] = $tempChecklist['actividades_completadas'] == count($tempChecklist['actividades']->where('estado','Cumple'));
                    $checklistCompletados++;
                    $checklists_realizados->push($tempChecklist);
                }
                else {
                    $checklists_pendientes->push($tempChecklist);
                }
                $checklists_response->push($tempChecklist);
            }
        }
        $suc = SummaryUserChecklist::where('user_id',$alumno_id)->first();
        $response['checklists_totales'] = count($checklists_assigned) ?? 0;
        $response['checklists_completados'] = $suc?->completed ?? 0;
        $response['porcentaje'] = $suc?->advanced_percentage ?? 0;
        $response['checklists']['pendientes'] = $checklists_pendientes->sortByDesc('disponible')->values()->all();
        $response['checklists']['realizados'] = $checklists_realizados->sortByDesc('disponible')->values()->all();
        $response['active'] = !is_null($entrenador);
        return $response;
    }
    protected function getChecklistsByAlumno_old($alumno_id): array
    {
        $entrenador = EntrenadorUsuario::where('user_id', $alumno_id)->where('active', 1)->first();
        $entrenador_id = !is_null($entrenador) ? $entrenador->trainer_id : null;

        $response['error'] = false;
        $checklistCompletados = 0;

        $user = User::where('id', $alumno_id)->first();

        $cursos_x_user = $user->getCurrentCourses();
        $cursos_ids = $cursos_x_user->pluck('id')->toArray();

        $lista_cursos = collect();
        $new_lista_cursos = collect();

        // $cursos = Course::with('checklists', 'schools')->whereIn('id', $cursos_ids)->get();
        $checklist_course_assigned = Checklist::whereHas('courses',function($q) use ($cursos_ids){
            $q->whereIn('id',$cursos_ids);
        })->where('active',1)->select('id')->get();

        $checklist_libre_assigned = array_column($user->getSegmentedByModelType(CheckList::class),'id');

        foreach($checklist_course_assigned as $cca) {
            $lista_cursos->push($cca->courses->pluck('id')->toArray());
        }
        foreach($checklist_libre_assigned as $cla) {
            $cla = CheckList::with('courses')->where('id', $cla)->first();
            $lista_cursos->push($cla->courses->pluck('id')->toArray());
        }
        foreach($lista_cursos as $lisc){
            foreach($lisc as $lis) {
                $new_lista_cursos->push($lis);
            }
        }
        $cursos = $new_lista_cursos->unique();

        $checklists = collect();
        $checklists_pendientes = collect();
        $checklists_realizados = collect();
        $tax_trainer_user = Taxonomy::where('group', 'checklist')
        ->where('type', 'type')
        ->where('code', 'trainer_user')
        ->first();
        $tax_user_trainer = Taxonomy::where('group', 'checklist')
            ->where('type', 'type')
            ->where('code', 'user_trainer')
            ->first();
        foreach ($cursos as $curso) {
            $curso = Course::with('checklists', 'schools')->where('id', $curso)->first();
            $curso->categoria = $curso->schools->first()->only('id', 'name');
            if ($curso->checklists->count() > 0) {
                foreach ($curso->checklists as $checklist) {
                    if ($checklist->active) {
                        $type_checklist = Taxonomy::where('id', $checklist->type_id)->first();
                        $actividades_activas = $checklist->actividades->where('active', 1)->where('type_id', $tax_trainer_user->id)->sortBy('position');
                        $actividades_activasFeedback = $checklist->actividades->where('active', 1)->where('type_id', $tax_user_trainer->id)->sortBy('position');
                        if (!$checklists->where('id', $checklist->id)->first() && $actividades_activas->count() > 0 && $checklist->active) {
                            $r_x_c = SummaryCourse::where('user_id', $alumno_id)->where('course_id', $curso->id)->first();

                            $aprobado = Taxonomy::getFirstData('course', 'user-status', 'aprobado');

                            $disponible = $r_x_c && $r_x_c->status_id === $aprobado->id;
                            $checklistRpta = ChecklistRpta::checklist($checklist->id)->alumno($alumno_id)->entrenador($entrenador_id)->first();
                            if (!$checklistRpta) {
                                $checklistRpta = ChecklistRpta::create([
                                    'checklist_id' => $checklist->id,
                                    'student_id' => $alumno_id,
                                    'course_id' => $curso->id, //deprecated
                                    'school_id' => $curso->categoria['id'], //deprecated
                                    'coach_id' => $entrenador_id,
                                    'percent' => 0
                                ]);
                            }
                            $progresoActividad = $this->getProgresoActividades($checklist, $checklistRpta, $actividades_activas);
                            $progresoActividadFeedback = $this->getProgresoActividadesFeedback($checklistRpta, $actividades_activasFeedback);

                            $lista_cursos = $checklist->courses()->with([
                                'schools' => function ($query) {
                                    $query->select('id', 'name');
                                }
                            ])->select('id', 'name')->get();

                            foreach($lista_cursos as $lc) {
                                $lc->makeHidden(['summaries', 'polls', 'requirements', 'pivot']);
                                $status_c = Course::getCourseStatusByUser($user, $lc);
                                $lc->status_c = $status_c['status'];
                            }

                            $tempChecklist = [
                                'id' => $checklist->id,
                                'titulo' => $checklist->title,
                                'descripcion' => $checklist->description,
                                'type_checklist' => $type_checklist?->code,
                                'disponible' => $disponible,
                                'curso' => $lista_cursos,
                                'porcentaje' => $progresoActividad['porcentaje'],
                                'actividades_totales' => $progresoActividad['actividades_totales'],
                                'actividades_completadas' => $progresoActividad['actividades_completadas'],
                                'actividades' => $progresoActividad['actividades'],
                                'actividades_feedback' => $progresoActividadFeedback['actividades_feedback'],
                                'feedback_disponible' => $progresoActividad['feedback_disponible']
                            ];
                            if ($tempChecklist['porcentaje'] === 100.00) {
                                $checklistCompletados++;
                                $checklists_realizados->push($tempChecklist);
                            }
                            else {
                                $checklists_pendientes->push($tempChecklist);
                            }
                            $checklists->push($tempChecklist);
                        }
                    }
                }
            }
        }

        $suc = SummaryUserChecklist::where('user_id',$alumno_id)->first();

        $response['checklists_totales'] = count($checklists) ?? 0;
        $response['checklists_completados'] = $suc?->completed ?? 0;
        $response['porcentaje'] =  SummaryUserChecklist::getGeneralPercentage(count($checklists), $suc?->completed ?? 0);
        $response['checklists']['pendientes'] = $checklists_pendientes->sortByDesc('disponible')->values()->all();
        $response['checklists']['realizados'] = $checklists_realizados->sortByDesc('disponible')->values()->all();
        $response['active'] = !is_null($entrenador);
        return $response;
    }

    protected function getChecklistInfo($checklist_id,$trainer){
        $alumnos_ids = EntrenadorUsuario::entrenador($trainer->id)->where('active', 1)->select('user_id')->get()->pluck('user_id')->all();
        $checklist = Checklist::getChecklistsWorkspace(checklist_id:$checklist_id);
        $course = new Course();
        $users_assigned = $course->usersSegmented($checklist->segments, $type = 'users_id');
        $users_assigned_checklist_trainer = array_intersect($alumnos_ids);
        $completed = ChecklistRpta::where('checklist_id',$checklist_id)->whereIn('student_id',$users_assigned_checklist_trainer)->where('percent',100)->count();
        $assigned = count($users_assigned_checklist_trainer);
        $percent = ($assigned > 0) ? (($completed / $assigned) * 100) : 0;
        $percent = round(($percent > 100) ? 100 : $percent); // maximo porcentaje = 100

        $tax_trainer_user = Taxonomy::where('group', 'checklist')
            ->where('type', 'type')
            ->where('code', 'trainer_user')
            ->first();

        $actividades = CheckListItem::select('id','checklist_id','activity')->where('checklist_id', $checklist->id)->where('type_id',$tax_trainer_user->id)->active(1)->orderBy('position','ASC')->get();

        $response['checklist'] = [
            'id'=>$checklist->id,
            'description'=>$checklist->description,
            'assigned'=>$assigned,
            'completed'=>$completed,
            'percent' => $percent,
            'actividades' => $actividades
        ];
        return $response;
    }

    protected function getChecklistsByTrainer($data): array
    {
          //añadir cursos: en caso sea tipo curso,añadir tipos

        $filtro_nombre = $data['filtro']['nombre'];
        $workspace_id = $data['trainer']->subworkspace->parent->id;
        $page = $data['page'];
        $perPage = 10;

        // leftJoin('summary_checklists as sc', 'sc.checklist_id', '=', 'checklists.id')
        $codes_type_checklists = Taxonomy::select('id','code')
            ->where('group', 'checklist')
            ->where('type', 'type_checklist')
            ->where('active', 1)
            ->get();

        $checklists = self::select('checklists.id','checklists.title','checklists.description','type_id')->where('workspace_id', $workspace_id)
        ->with(['courses' => function($q) {
            $q->select('courses.id', 'courses.name');
        }])
        ->where('active', ACTIVE)
        ->when($filtro_nombre, function($q) use ($filtro_nombre){
            return  $q->where('title','like','%'.$filtro_nombre.'%');

         })->paginate($perPage, ['*'], 'page', $page);

        // $list_checklist = collect();

        if(count($checklists) > 0) {
            foreach ($checklists as $check) {
                $type = $codes_type_checklists->where('id',$check->type_id)->first();
                $check->type = $type?->code;
                if($type?->code != 'curso'){
                    $check->courses=[];
                }
                unset($check->type_id);
            }
        }
        $response['pagination'] = [
            'total' => $checklists->total(),
            'pages' => $checklists->lastPage(),
            'perPage' => $checklists->perPage(),
            'page' => $page
        ];
        $response['checklists'] = collect($checklists->items());
        // $response['data'] = $checklists;
        // $response['lastPage'] = $checklists->lastPage();

        // $response['current_page'] = $checklists->currentPage();
        // $response['first_page_url'] = $checklists->url(1);
        // $response['from'] = $checklists->firstItem();
        // $response['last_page'] = $checklists->lastPage();
        // $response['last_page_url'] = $checklists->url($checklists->lastPage());
        // $response['next_page_url'] = $checklists->nextPageUrl();
        // $response['path'] = $checklists->getOptions()['path'];
        // $response['per_page'] = $checklists->perPage();
        // $response['prev_page_url'] = $checklists->previousPageUrl();
        // $response['to'] = $checklists->lastItem();
        // $response['total'] = $checklists->total();

        return $response;
    }

    protected function getStudentsByChecklist($data): array
    {
        $trainer = $data['trainer'];
        $checklist_id = $data['checklist_id'];
        $page = $data['page'];
        $perPage = 10;
        //añadir porcentaje.
        $checklist = CheckList::select('id','type_id')->with('type:id,code')->where('id',$checklist_id)->first();
        if($checklist->type->code == 'curso'){
            $courses = $checklist->courses()->select('courses.id')->with('segments')->get('id');
            $status_id_completed = Taxonomy::where('group', 'course')->where('type','user-status')->where('code','aprobado')->first()?->id;
            $alumnos_ids = $trainer->students()
            // ->with([
            //     'criterion_values:id,value_text,criterion_id','criterion_values.criterion.field_type'
            // ])
            ->whereHas('summary_courses',function($q)use ($courses,$status_id_completed) {
                foreach ($courses as $course) {
                    $q->where('course_id',$course->id);
                }
                $q->where('status_id',$status_id_completed);
            })
            ->select('users.id')->where('users.active',1)->get()->pluck('id');
            // dd($alumnos_ids);
            // $alumnos_ids = [];
            // foreach ($alumnos as $alumno) {
            //     $hasAllCourses = true;
            //     foreach ($courses as $course) {
            //         if(!$alumno->userHasCourse($course)){
            //             $hasAllCourses = false;
            //         }
            //     }
            //     if($hasAllCourses){
            //         $alumnos_ids[] = $alumno->id;
            //     }
            // }
            // $alumnos_ids = User::whereIn('id',$final_list)->where('active', 1)
            //         ->select('id')
            //         ->whereHas('summary_courses',function($q)use ($courses_id,$status_id_completed) {
            //             foreach ($courses_id as $key => $course_id) {
            //                 $q->where('course_id',$course_id);
            //             }
            //             $q->where('status_id',$status_id_completed);
            //         })->pluck('id');
            // $status_id_completed = Taxonomy::where('group', 'course')->where('type','user-status')->where('code','aprobado')->first()?->id;
            // $usersSegmented = [];
            // foreach ($checklist->courses as $course) {
            //     $course_users = $course->usersSegmented($course->segments, $type = 'users_id');
            //     $usersSegmented = array_merge($usersSegmented,$course_users);
            // }
        }else{
            $alumnos_ids = $trainer->students()->select('users.id')->where('users.active',1)->pluck('id')->toArray();
            $checklist_segments = $checklist->segments()->get();
            $course = new Course();
            $checklist_users = $course->usersSegmented($checklist_segments, $type = 'users_id');
            $alumnos_ids = array_intersect($alumnos_ids,$checklist_users);
        }
        $list_students = User::leftJoin('workspaces as w', 'users.subworkspace_id', '=', 'w.id')
            ->leftJoin('summary_user_checklist as suc', 'suc.user_id', '=', 'users.id')
            ->whereIn('users.id',$alumnos_ids)
            ->select('users.id', 'users.name', 'users.fullname as full_name', 'users.document', 'w.name as subworkspace','suc.advanced_percentage')
            ->paginate($perPage, ['*'], 'page', $page);

        if(count($list_students) > 0) {
            foreach ($list_students as $student) {
                if(!$student->advanced_percentage){
                    $student->advanced_percentage = 0;
                }
                $student->makeHidden(['abilities', 'roles', 'age', 'fullname']);
            }
        }


        $response['pagination'] = [
            'total' => $list_students->total(),
            'pages' => $list_students->lastPage(),
            'perPage' => $list_students->perPage(),
            'page' => $page
        ];
        $response['alumnos'] = collect($list_students->items());


        return $response;
    }

    public static function getChecklistsWorkspace($checklist_id,$workspace_id=null,$with_segments=false,$select = ''){
        $query = self::when($select, function ($q) use($select) {
            $q->select($select)->addSelect('workspace_id');
        })->when($with_segments,function($q2){
            $q2->with(['segments' => function ($q) {
                $q
                    ->where('active', ACTIVE)
                    ->select('id', 'model_id')
                    ->with('values', function ($q) {
                        $q
                            ->with('criterion_value', function ($q) {
                                $q
                                    ->where('active', ACTIVE)
                                    ->select('id', 'value_text', 'value_date', 'value_boolean')
                                    ->with('criterion', function ($q) {
                                        $q->select('id', 'name', 'code');
                                    });
                            })
                            ->select('id', 'segment_id', 'starts_at', 'finishes_at', 'criterion_id', 'criterion_value_id');
                    });
            }]);
        })->when($workspace_id,function($q) use($workspace_id){
            $q->where('workspace_id', $workspace_id);
        })
        // ->whereRelation('segments', 'active', ACTIVE)
        ->where('active', ACTIVE);
        return $checklist_id ? $query->where('id', $checklist_id)->first() : $query->get();
    }
    public function getProgresoActividadesFeedback(ChecklistRpta $checklistRpta, $actividades,$checklists_taxonomies)
    {

        foreach ($actividades as $actividad) {
            $actividadProgreso = $checklistRpta->rpta_items->where('checklist_item_id', $actividad->id)->first();
            $actividad->disponible = !is_null($actividadProgreso);
            if (!is_null($actividadProgreso)) {
                $actividad->estado = $actividadProgreso->qualification;
            } else {
                $actividad->estado = 'Por validar';
            }
            $type_name = !is_null($actividad->type_id) ? $checklists_taxonomies->where('id', $actividad->type_id)->first() : null;
            $type_name = !is_null($type_name) ? $type_name->code : '';
            $actividad->tipo = $type_name;
        }
        return [
            'actividades_feedback' => $actividades->values()->all()
        ];
    }

    public function getProgresoActividades(Checklist $checkList, ChecklistRpta $checklistRpta, $actividades,$checklists_taxonomies)
    {
        $completadas = 0;
        if ($checklistRpta->porcentaje !== 100) {
            foreach ($actividades as $actividad) {
                $checklistRptaItem = $checklistRpta->rpta_items->where('checklist_item_id', $actividad->id)->first();
                if (!$checklistRptaItem) {
                    $checklistRptaItem = new ChecklistRptaItem();
                    $checklistRptaItem->checklist_answer_id =  $checklistRpta->id;
                    $checklistRptaItem->checklist_item_id =  $actividad->id;
                    $checklistRptaItem->qualification =   'Por validar';
                    $checklistRptaItem->save();
                    $checklistRpta->rpta_items->push($checklistRptaItem);
                }
                $actividad->estado = $checklistRptaItem->qualification;
                if (in_array($actividad->estado, ['Cumple', 'No cumple'])) $completadas++;
                $actividad->tipo =  $checklists_taxonomies->where('id', $actividad->type_id)->first()?->code ?? '';
            }
            // ChecklistRpta::actualizarChecklistRptaV2($checklistRpta,$actividades,$checklists_taxonomies);
            $porcentaje = ($actividades->count() > 0) ? (($completadas / $actividades->count()) * 100) : 0;
            $porcentaje = round(($porcentaje > 100) ? 100 : $porcentaje); // maximo porcentaje = 100
            $checklistRpta->percent = $porcentaje;
            $checklistRpta->update();
        } else {
            // $actividadProgreso = ChecklistRptaItem::with('rpta_items')->where('checklist_answer_id', $checklistRpta->id)->get();
            $actividadProgreso = $checklistRpta->rpta_items;
            $actividades = collect();
            foreach ($actividadProgreso->rpta_items as $rpta) {
                $actividades->push([
                    'id' => $rpta->actividad->id,
                    'checklist_id' => $rpta->actividad->checklist_id,
                    'posicion' => $rpta->actividad->position,
                    'tipo' => $rpta->actividad->tipo,
                    'estado' => $rpta->qualification
                ]);
            }
            $porcentaje = $checklistRpta->porcentaje;
        }
        $feedback_disponible = $actividades->count() === $completadas;
        return [
            'actividades_completadas' => $completadas,
            'actividades_totales' => $actividades->count(),
            'porcentaje' => $porcentaje,
            'actividades' => $actividades->values()->all(),
            'feedback_disponible' => $feedback_disponible
        ];
    }

    protected function getStudentChecklistInfoById($checklist_id, $student_id = null, $trainer_id = null){
        $alumno_id = $student_id ? $student_id : Auth::user()?->id;
        
        $user = User::where('id', $alumno_id)->first();
        if($trainer_id) {
            $entrenador = null;
            $entrenador_id = $trainer_id;
        }
        else {
            $entrenador = EntrenadorUsuario::where('user_id', $alumno_id)->where('active', 1)->first();
            $entrenador_id = !is_null($entrenador) ? $entrenador->trainer_id : null;
        }
        $checklists_pendientes = collect();
        $checklists_realizados = collect();
        $checklists_response = collect();
        $checklistCompletados = 0;

        $checklists_assigned  = SummaryUserChecklist::getChecklistByUser(
                        $user,['id','title','description','active','type_id'],
                        withChecklistFreeRelations:['actividades:id,checklist_id,type_id,activity,position,active'],
                        withChecklistCourseRelations:[
                            'actividades:id,checklist_id,type_id,activity,position,active',
                        ],
                        mergeChecklist:true,
                    );
        $checklists_taxonomies = Taxonomy::select('id','name','code','type')->where('group','checklist')->get();
        $statuses_course = Taxonomy::select('id','name','code')->where('group','course')->where('type','user-status')->get();
        $aprobado = $statuses_course->where('code','aprobado')->first();
        $tax_trainer_user = $checklists_taxonomies->where('type','type')->where('code','trainer_user')->first();
        $tax_user_trainer = $checklists_taxonomies->where('type','type')->where('code','user_trainer')->first();

        $summaries_course_checklist = SummaryCourse::where('user_id', $alumno_id)
            ->whereIn('course_id',$checklists_assigned->pluck('courses.*.id')->flatten())
            ->select('id','course_id','status_id')
            ->get();

        $checklist_rptas_user = ChecklistRpta::select('id','feedback_entrador','coach_id','student_id','checklist_id','flag_congrats','percent')->with('rpta_items:id,checklist_answer_id,checklist_item_id,qualification')->alumno($alumno_id)->entrenador($entrenador_id)->get();
        foreach ($checklists_assigned as $checklist) {
            if($checklist?->id == $checklist_id)
            {
                $type_checklist = $checklists_taxonomies->where('type','type_checklist')->where('id', $checklist->type_id)->first();
                $actividades_activas = $checklist->actividades->where('active', 1)->where('type_id', $tax_trainer_user->id)->sortBy('position');
                $actividades_activasFeedback = $checklist->actividades->where('active', 1)->where('type_id', $tax_user_trainer->id)->sortBy('position');
                if ($actividades_activas->count() > 0 && $checklist->active) {

                    $checklistRpta = $checklist_rptas_user->where('checklist_id',$checklist->id)->first();
                    if (!$checklistRpta) {
                        $checklistRpta = ChecklistRpta::create([
                            'checklist_id' => $checklist->id,
                            'student_id' => $alumno_id,
                            'coach_id' => $entrenador_id,
                            'percent' => 0
                        ]);
                    }
                    $progresoActividad = $this->getProgresoActividades($checklist, $checklistRpta, $actividades_activas,$checklists_taxonomies);
                    $progresoActividadFeedback = $this->getProgresoActividadesFeedback($checklistRpta, $actividades_activasFeedback,$checklists_taxonomies);

                    $disponible = true;
                    if($type_checklist?->code == 'curso'){
                        $courses_id = $checklist->courses->pluck('id')->toArray();
                        if(count($courses_id)>0){
                            //code...
                            $disponible = count($courses_id) ==  $summaries_course_checklist->where('status_id',$aprobado->id)->filter(function($q) use ($courses_id) {
                                return in_array($q->course_id, $courses_id);
                            })->count();

                        }
                    }
                    $lista_cursos = $checklist->courses->map(function($course) use ($user,$summaries_course_checklist,$statuses_course){
                        $status = 'Por validar';
                        $summary_course = $summaries_course_checklist->where('course_id',$course->id)->first();
                        if($summary_course){
                            $status = $statuses_course->where('id',$summary_course->status_id)->first()?->name ?? 'Por validar';
                        }
                        return [
                            'id' =>$course->id,
                            'name' => $course->name,
                            'status' => $status,
                            'schools' => $course->schools->map(fn($school)=> ['id'=>$school->id,'name'=>$school->name])
                        ];
                    });

                    $tempChecklist = [
                        'id' => $checklist->id,
                        'titulo' => $checklist->title,
                        'descripcion' => $checklist->description,
                        'type_checklist' => $type_checklist?->code,
                        'disponible' => $disponible,
                        'curso' => $lista_cursos,
                        'porcentaje' => $progresoActividad['porcentaje'],
                        'actividades_totales' => $progresoActividad['actividades_totales'],
                        'actividades_completadas' => $progresoActividad['actividades_completadas'],
                        'actividades' => collect($progresoActividad['actividades']),
                        'actividades_feedback' => $progresoActividadFeedback['actividades_feedback'],
                        'feedback_disponible' => $progresoActividad['feedback_disponible'],
                        'feedback_entrador'=> $checklistRpta?->feedback_entrador,
                        'mostrar_modal' => false
                    ];
                    if ($tempChecklist['porcentaje'] === 100.00) {
                        $tempChecklist['mostrar_modal'] = $tempChecklist['actividades_completadas'] == count($tempChecklist['actividades']->where('estado','Cumple'));
                        $checklistCompletados++;
                        $checklists_realizados->push($tempChecklist);
                    }
                    else {
                        $checklists_pendientes->push($tempChecklist);
                    }
                    $checklists_response->push($tempChecklist);
                }
                break;
            }
        }
        $checklist_collection = $checklists_response->merge($checklists_realizados, $checklists_pendientes);

        $response = $checklist_collection->sortByDesc('disponible')->values()->first();

        return $response;
    }
}
