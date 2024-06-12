<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ActivityChecklistImport;
use App\Http\Controllers\ApiRest\HelperController;

class CheckList extends BaseModel
{
    protected $table = 'checklists';

    protected $fillable = [
        'title',
        'description',
        'active',
        'workspace_id',
        'course_id',
        'modality_id',
        'supervisor_criteria',
        'supervisor_ids',
        'extra_attributes',
        'type_id',
        'starts_at',
        'finishes_at',
        'platform_id',
        'imagen'
    ];

    protected $casts = [
        'active' => 'boolean',
        'extra_attributes'=>'json',
        'supervisor_criteria'=>'array',
        'supervisor_ids' => 'array',
        'finishes_at' => 'date'
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
    public function activities()
    {
        return $this->hasMany(CheckListItem::class, 'checklist_id', 'id');
    }
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'checklist_relationships', 'checklist_id', 'course_id');
    }
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }
    public function modality()
    {
        return $this->belongsTo(Taxonomy::class, 'modality_id');
    }
    public function getImagenAttribute($imagen){
        $default_image = 'https://sfo2.digitaloceanspaces.com/cursalab2-statics/cursalab-assets/images/default_image_checklist.png';
        return is_null($imagen) ? $default_image : $imagen; 
    }
    public function getFinishesAtAttribute($finishes_at) {
        if(!$finishes_at){
            return null;
        }
        try {
            return Carbon::parse($finishes_at)->format('Y-m-d');
        } catch (\Throwable $th) {
            info('catch'.$finishes_at);
            return null;
        }
        return null;
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
    protected function uploadMassive($checklist,$request){
        $import = new ActivityChecklistImport();
        $import->checklist = $checklist;
        Excel::import($import, $request->file('archivo'));
        return $import->activities;
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

    protected function duplicateChecklistFromWorkspace($current_workspace,$new_workspace){
        $checklists = CheckList::select('id','workspace_id','title','description','active','workspace_id','type_id','starts_at','finishes_at','platform_id')
        ->with('actividades:id,checklist_id,activity,type_id,active,position')->where('workspace_id',$current_workspace->id)->get();
        foreach ($checklists as $checklist) {
            $actividades = $checklist->actividades->map(function($actividad){
                unset($actividad['id']);
                unset($actividad['checklist_id']);
                return $actividad;
            })->toArray();
            $checklist = $checklist->toArray();
            $checklist['workspace_id'] = $new_workspace->id;
            unset($checklist['id']);
            $_checklist = new CheckList();
            $_checklist->fill($checklist);
            $_checklist->save();
            $_checklist->actividades()->createMany($actividades);
        }
    }

    protected function storeRequest($data, $checklist = null){
        $data = Media::requestUploadFile($data, 'imagen');
        $evaluation_types = collect(json_decode($data['evaluation_types']));
        $evaluation_types_id = [];
        foreach ($evaluation_types as $index => $evaluation_type) {
            $evaluation_types_id[] = Taxonomy::updateOrCreate(
                ['id' => $evaluation_type->id,'group'=>'checklist','type' => 'custom_ystem_calification'],
                [
                    'group' => 'checklist',
                    'type' => 'custom_ystem_calification',
                    "color" => $evaluation_type->color,
                    "icon" => $evaluation_type->icon,
                    "name" => $evaluation_type->name,
                    "position" => $index+1,
                    "extra_attributes" => $evaluation_type->extra_attributes,
                ]
            );
        }
        $data['extra_attributes']['evaluation_types_id'] =  array_column($evaluation_types_id,'id');
        if($checklist){
            unset($data['course']);
            $checklist->update($data);
            $next_step = self::nextStep($checklist);
        }else{
            $data['active'] = 0;
            $data['platform_id'] = currentPlatform()->id;
            $data['workspace_id'] = get_current_workspace()->id;
            $checklist = self::create($data);
            $next_step = 'create_activities';
        }
        $checklist->load('type:id,code');
        return [
            'next_step' => $next_step,
            'checklist' => $checklist
        ];
    }

    protected function saveActivities($checklist,$data){
        $has_themes = $checklist->isGroupedByArea();
        if($has_themes){
            $areas = $data['data'];
            foreach ($areas as $area) {
                $tematicas = $area['tematicas'];
                foreach ($tematicas as $tematica) {
                    $activities = $tematica['activities'];
                    foreach ($activities as $key => $activity) {
                        $activity['area_id'] = $area['id'];
                        $activity['tematica_id'] = $tematica['id'];
                        CheckListItem::saveActivity($checklist,$activity);
                    }
                }
            }
        }
        // $activities_to_insert = [];
        // $activities_id = [];
        // foreach ($activities as $data_activity) {
        //     $activity = CheckListItem::updateOrCreate(
        //         ['id' => $data_activity['id']],
        //         [
        //             'activity' => $data_activity['activity'],
        //             'active' => 1,
        //             // 'type_id' => $data['type_id'],
        //             'checklist_id' => $checklist->id,
        //             'position' => $data_activity['position'],
        //             'checklist_response_id'=>$data_activity['checklist_response']['id'],
        //             'extra_attributes' => $data_activity['extra_attributes'],
        //         ]
        //     );
        //     $activities_id[] = $activity->id;
        //     if(isset($data_activity['custom_options']) && count($data_activity['custom_options'])>0 ){
        //         $custom_options = $data_activity['custom_options'];
        //         foreach ($custom_options as $option) {
        //             $option = Taxonomy::updateOrCreate(
        //                 ['id' => $option['id'],'group' => 'checklist','type'=>'activity_option'],
        //                 [
        //                     'group' => 'checklist',
        //                     'type'  => 'activity_option',
        //                     'parent_id' => $activity->id,
        //                     'name'=> $option['name']
        //                 ]
        //             );
        //         }
        //     }
        // }
        // CheckListItem::where('checklist_id',$checklist->id)->whereNotIn('id',$activities_id)->delete();
        $next_step = self::nextStep($checklist);
        $checklist->load('type:id,code');
        return [
            'next_step' => $next_step,
            'checklist' => $checklist
        ];
    }

    protected function getSegments( $_checklist )
    {
        $checklist = null;

        $workspace = get_current_workspace();

        if(!is_null($_checklist)) {

            $criteria = Segment::getCriteriaByWorkspace(get_current_workspace());
            $segments = Segment::getSegmentsByModel($criteria, CheckList::class, $_checklist->id);
            
            $checklist['segments'] = $segments;

            if (is_array($segments)) {
                $segments = collect($segments);
            }

            $segmentation_by_document_list = [];
            $segmentation_by_document = $segments->map(function ($item) {
                return ['segmentation_by_document'=> $item->segmentation_by_document];
            });

            foreach ($segmentation_by_document as $seg) {
                foreach ($seg['segmentation_by_document'] as $value) {
                    array_push($segmentation_by_document_list, $value);
                }
            }
            $checklist['segmentation_by_document'] = ['segmentation_by_document'=> $segmentation_by_document_list];

        }

        return ['checklist' => $checklist];
    }
    protected function updateSupervisors($checklist,$data){
        $checklist->supervisor_criteria = $data['supervisor_criteria'];
        $checklist->supervisor_ids = $data['supervisor_ids'];
        $checklist->save();
        $next_step = self::nextStep($checklist);
        $checklist->load('type:id,code');
        return [
            'next_step' => $next_step,
            'checklist' => $checklist
        ];
    } 
    protected function nextStep($checklist){
        $next_step = '';
        $hasActivities = $checklist->activities()->first();
        if(!$hasActivities){
            $next_step = 'create_activities';
            return $next_step;
        }
        $checklist->loadMissing('type:id,name,code');
        if($checklist->type->code != 'curso'){
            $hasSegments = $checklist->segments()->first();
            if(!$hasSegments){
                $next_step = 'segmentation_card';
                return $next_step;
            }
        }
        $checklistSupervisor = ChecklistSupervisor::where('id',$checklist->id)->first();

        if(!$checklistSupervisor->segments($checklist->id)->first()){
            $next_step = 'supervisor_card';
            return $next_step;
        }
        return $next_step;
    }

    protected function listChecklists(){
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $workspace = get_current_workspace();

        $queryChecklist = CheckList::select('id','active','title','modality_id','type_id',
                                            'supervisor_criteria','supervisor_ids',
                                            'extra_attributes',
                                            DB::raw("
                                            DATE_FORMAT(finishes_at,'%d/%m/%Y') as finishes_at,
                                            extra_attributes->'$.replicate' as 'replicate'
                                            "),
                                    )
                                    ->with(['modality:id,name,code,alias','type:id,name,code'])
                                    ->withCount(['activities','segments'])
                                    ->FilterByPlatform()
                                    ->where('workspace_id', $workspace->id);

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

        return $checklists;
    }

    protected function searchCourses($request){
        $current_workspace = get_current_workspace();
        return Course::whereRelation('workspaces', 'id', $current_workspace->id)
                ->when($request->q, function($q) use ($request){
                    $q->where('name', 'like', "%$request->q%")
                    ->orWhere('id', $request->q);
                })
                ->where('courses.active', 1)
                ->select(
                    'courses.id',
                    DB::raw('CONCAT(courses.id," - ",courses.name) as name')
                )
                ->paginate(10)->items();
    }

    /* FUNCTION APIS*/
    protected function listsChecklistsToTrainer($request){
        $user = auth()->user();
        $user_latitude = $request->lat;
        $user_longitude = $request->long;
        $filter = $request->filter;
        $filters = [] ;
        if($request->search){
            $filters = [
                [
                    'statement'=>'where',
                    'field'=>'title',
                    'value'=>'%'.$request->search.'%',
                    'operator'=>'like',
                ],
            ];
        }
        $checklists = $user->getSegmentedByModelType(
            model: ChecklistSupervisor::class,
            withModelRelations: ['modality:id,name,code,color,alias', 'type:id,name,color,code'],
            unset_criterion_values: false,
            filters:$filters
        );

        $list_checklists_geolocalization = collect();
        $list_checklists_exclude_geolocalization = collect();
        $list_checklists_libres = collect();
        $workspace_entity_criteria = Workspace::select('checklist_configuration')
            ->where('id', $user->subworkspace->parent->id)
            ->first()?->checklist_configuration?->entities_criteria;

        foreach ($checklists as $checklist) {
            $_checkist_data = $this->buildChecklistData($checklist,$user);
            if($_checkist_data['status']['code'] != $filter){
                continue;
            }
            if ($checklist->extra_attributes['required_geolocation']) {
                $this->processChecklistWithGeolocation($user, $workspace_entity_criteria, $_checkist_data, $list_checklists_geolocalization, $list_checklists_exclude_geolocalization, $user_latitude, $user_longitude);
            } else {
                $this->processChecklistWithoutGeolocation($_checkist_data, $list_checklists_libres);
            }
        }
        return [$list_checklists_geolocalization,$list_checklists_exclude_geolocalization,$list_checklists_libres];
    }
    public function isGroupedByArea(){
        $checklist = $this;
        return isset($checklist->extra_attributes['gruped_by_areas_and_tematicas'])
                    ? $checklist->extra_attributes['gruped_by_areas_and_tematicas'] 
                    : false;
    }
    protected function listActivities($checklist,$request){
        $theme_id = $request?->theme_id;
        $user_id = $request?->user_id;

        $user = auth()->user();
        $checklist->loadMissing([
            'modality:id,code',
            'type:id,name,code,color',
            'activities:id,checklist_id,checklist_response_id,extra_attributes,activity,type_id,active,position,tematica_id,area_id',
            'activities.checklist_response:id,code',
        ]);
        $checklist_audit = null;
        $activities_progress = collect();
        $checklist->getProgressActivities($checklist,$user,$request->entity_id,$request->user_id,$checklist_audit,$activities_progress);
        
        $has_themes = $checklist->isGroupedByArea();

        $taxonomy_tematicas = Taxonomy::whereIn('id',$checklist->activities->pluck('tematica_id'))->select('id','name','active','parent_id')->get(); 
        $_activities = $checklist->activities;
        $theme = null;
        if($has_themes){
            if($request->theme_id){
                $_activities = $checklist->activities->where('tematica_id',$theme_id)->values();
            }else{
                $_activities = $checklist->activities->where('tematica_id',$taxonomy_tematicas->first()->id);
            }
        }
        foreach ($taxonomy_tematicas as $tematica) {
            $tematica->count_activities = $checklist->activities->where('tematica_id',$tematica->id)->count();
            $tematica->finished = false;
        }
        $counter_activity = 1;
        foreach ($_activities as $index => $activity) {
            $extra_attributes = $activity->extra_attributes;
            if($activity->checklist_response->code == 'scale_evaluation'){
                $system_calification = Taxonomy::select(
                                            'id', 'name', 'color', 
                                            DB::raw("JSON_UNQUOTE(extra_attributes->'$.percent') as percent"),
                                            DB::raw("JSON_UNQUOTE(extra_attributes->'$.emoji') as emoji")
                                        )
                                        ->whereIn('id',$checklist->extra_attributes['evaluation_types_id'])
                                        ->get()->map(function($system){
                                            if($system->emoji && $system->emoji != 'null'){
                                                // dd($system->emoji,$system->name);
                                                $system->name = $system->name.' '.$system->emoji;
                                            }
                                            return $system;
                                        });
            }else{
                $system_calification = $activity->custom_options()->select('id','name','color')->get();
            }
            $progress = $activities_progress->where('checklist_activity_id',$activity->id)->first();
            $photos = $progress?->photo;
            $list_photos = [];
            if($photos && count($photos) > 0){
                foreach ($photos as $photo) {
                    // $photo['url'] = get_media_url($photo['url']);
                    $photo['url'] = reportsSignedUrl($photo['url']);
                    $list_photos[] = $photo; 
                }
            }
            if($has_themes && !$theme){
                $theme = $taxonomy_tematicas->where('id',$activity->tematica_id)->first();
                $theme->area = Taxonomy::where('id',$theme->parent_id)->select('name')->first()?->name;
            }
            $qualification_response = '';
            if($activity->checklist_response->code == 'write_option' && $progress?->qualification_id){
                $qualification_response = Taxonomy::where('id',$progress->qualification_id)->select('name')->first()?->name;
            }
            $comments = $progress?->comments ? collect( $progress?->comments) : null;
            $activities[]  = [
                'id'=>$activity->id,
                'name'=> $has_themes ? $theme?->name.' - '.'Actividad '.($counter_activity) : 'Actividad '.($counter_activity),
                'description'=> $activity->activity,
                'can_comment'=> $extra_attributes['comment_activity'],
                'can_upload_image'=> $extra_attributes['photo_response'],
                'can_computational_vision' => $extra_attributes['computational_vision'],
                'type_system_calification'=> $activity->checklist_response->code,
                'system_calification' => $system_calification,
                'principal_comment' => $comments  ? $comments->where('principal',true)->first() : null,
                'comments' => $comments?->where('principal',false)->values() ?? [],
                'photo' => $list_photos,
                'qualification_response' => $qualification_response,
                'qualification_id'=> $progress?->qualification_id ?? null,
            ];
            $counter_activity = $counter_activity + 1;
        }
        $workspace_entity_criteria = Workspace::select('checklist_configuration')
        ->where('id', $user->subworkspace->parent->id)
        ->first()?->checklist_configuration?->entities_criteria;

        $criterion_value_user_entity = $user->criterion_values->whereIn('criterion_id', $workspace_entity_criteria)->first();
        $user_checklist = null;
        if($user_id){
            $user_checklist = User::select('name','lastname','surname')->where('id',$user_id)->first();
        }
        $activities_assigned  = $checklist->activities->count();
        $activities_reviewved  = $checklist_audit?->activities_reviewved ?? 0;
        $finished  = boolval($checklist_audit?->checklist_finished);

        $percent_progress  = round(($activities_reviewved/$activities_assigned),2)*100;
        return [
            'user'=>[
                'fullname' => $user_checklist?->fullname
            ],
            'checklist'=>[
                'id' => $checklist->id,
                "title" => $checklist->title,
                "entity" =>[
                    "name"=> $criterion_value_user_entity?->value_text,
                    "icon"=>"store",
                ],
                'has_themes' => $has_themes,
                'list_themes' => $taxonomy_tematicas,
                'required_signature_supervisor'=>$checklist->extra_attributes['required_signature_supervisor'],
                "imagen" => get_media_url($checklist->imagen),
                "description" => $checklist->description,
                'supervisor' => $user->fullname,
                "required_geolocalization"=> $checklist->extra_attributes['required_geolocation'],
                'required_action_plan' => $checklist->extra_attributes['required_action_plan'],
                "type" => $checklist->type,
                "theme"=>$theme,
                'activities' => $activities,
                'percent_progress' => $percent_progress,
                'activities_assigned' => $activities_assigned,
                'activities_reviewved' =>  $activities_reviewved,
                'finished' => $finished,
                'show_modal_action_plan' => $finished &&  $checklist->extra_attributes['required_action_plan'] && !boolval($checklist_audit?->action_plan)
            ]
            ];
    }

    public function listThemes($request){
        
        $user = auth()->user();
        $checklist = $this;
        $has_themes = $checklist->isGroupedByArea();
        if(!$has_themes){
            return [
                'has_themes'=> $has_themes,
                'themes' => []
            ];
        }
        $checklist_audit = null;
        $activities_progress = collect();
        $checklist->getProgressActivities($checklist,$user,$request->entity_id,$request->user_id,$checklist_audit,$activities_progress);

        $activitiesByTematica = CheckListItem::select('id','tematica_id')->with('tematica')->where('checklist_id',$checklist->id)->get()->groupBy('tematica_id');
        $themes = [];
        foreach ($activitiesByTematica as $tematica_id => $activities) {
            $count_activities_with_progress = $activities_progress->whereIn('checklist_activity_id',$activities->pluck('id'))->count();
            $themes[] = [
                'id' => $tematica_id,
                'name' => $activities->first()->tematica->name,
                'count_activities_finished'=> $count_activities_with_progress,
                'count_activities'=> $activities->count(),
                'finished' =>  $count_activities_with_progress == $activities->count()
            ];
        }
        return $themes;
    }

    public function getProgressActivities($checklist,$user,$entity_id=null,$user_id=[],&$checklist_audit,&$activities_progress){
        $checklist = $this;
        if ($checklist->modality->code != 'qualify_user') {
            $criterion_value_user_entity = ChecklistAudit::getCriterionValueUserEntity($checklist, $user);
            if($entity_id){
                $model_id = $entity_id;
            }else{
                $model_id = $checklist->modality->code === 'qualify_entity' ? $criterion_value_user_entity->id : $user->id;
            }
            
            $model_type = $checklist->modality->code === 'qualify_entity' ? CriterionValue::class : User::class;
            $checklist_audit =  ChecklistAudit::getCurrentChecklistAudit($checklist,$model_type,$model_id,$user,true);
            $activities_progress = $checklist_audit?->audit_activities ?? collect();
        }else{
            $model_id = $user_id;
            $model_type = User::class;
            $checklist_audit =  ChecklistAudit::getCurrentChecklistAudit($checklist,$model_type,$model_id,$user,true);
            $activities_progress = $checklist_audit?->audit_activities ?? collect();
        }
    }
    protected function listUsers($checklist,$request){
        $user = auth()->user();
        $user->load('criterion_values');
        $workspace_entity_criteria = Workspace::select('checklist_configuration')
        ->where('id', $user->subworkspace->parent->id)
        ->first()?->checklist_configuration?->entities_criteria;
        $user_relation = $user->criterion_values->whereIn('criterion_id',$workspace_entity_criteria)->first();
        // $criteria_segmented = ;
        $checklist->loadMissing('type:id,name,code,color,icon');
        $summaries = collect();
        $filters = [];
        if($request->search){
            $filters = [
                ['statement'=>'filterText','value'=>$request->search]
            ];
        }
        if($checklist->type->code == 'curso'){
            $_course = Course::select('id')->where('id',$checklist->course_id)->first();
            $_course->loadMissing(['segments','segments.values']);
            $segments_course = $_course->segments;
            foreach ($segments_course as $segment) {
                // dd($segment->values,$user_relation);
                $segment->values->push([
                    "id" => $user_relation->id,
                    "segment_id" => $segment->id,
                    "criterion_id" => $user_relation->criterion_id,
                    "criterion_value_id" => $user_relation->id,
                    "type_id" => null,
                    "starts_at" => null,
                    "finishes_at" => null,
                    "created_at" => "2022-11-01 18:49:45",
                    "updated_at" => "2023-05-19 00:38:21",
                    "deleted_at" => null,
                ]);
            }
            $users = $_course->usersSegmented(
                course_segments:$_course->segments,
                addSelect:['name','lastname','surname','document'],
                filters:$filters
            );
            if(count($users)){
                $summaries = SummaryCourse::select('id','user_id','advanced_percentage')->where('course_id',$checklist->course_id)
                                ->whereIn('user_id',$users->pluck('id'))
                                ->whereRelation('status', 'code', 'aprobado')->get();
            }
        }else{
            $_course =new Course();
            $checklist->loadMissing(['segments']);
            // añadir variable SEARCH
            $users = $_course->usersSegmented(
                                course_segments:$checklist->segments,
                                addSelect:['name','lastname','surname','document'],
                                filters:$filters
                            );
        }
        $checklist->load('activities:id,checklist_id');
        $count_activities = count($checklist->activities);
        $users_id_assigned = $users->pluck('id')->toArray();
        $status_activities = ChecklistAudit::getCurrentChecklistAudit($checklist,User::class,$users_id_assigned,$user,true);

        $count_users_completed = count($status_activities->where('checklist_finished',1));
        $percent_completed =  count($users)>0 ?  round($count_users_completed/count($users) * 100,2) : 0;
        $count_users_pending = count($users) - $count_users_completed;
        $percent_pending = count($users)>0 ? round($count_users_pending/count($users) * 100,2) : 0;
        $statuses = collect([
            [ 'code' => 'pendiente', 'color' => '#5458EA', 'icon' => 'mdi-account' ],
            [ 'code' => 'in-progress', 'color' => '#5458EA', 'icon' => 'mdi-account' ],
            [ 'code' => 'completed', 'color' => '#00E396', 'icon' => 'mdi-account-check' ],
            [ 'code' => 'blocked', 'color' => '#A9B2B9', 'icon' => 'mdi-account-lock' ],
        ]);
        
        foreach ($users as $user) {
            $status_code = 'pendiente';
            if ($checklist->type->code == 'curso') {
                $status_course = $summaries?->firstWhere('user_id', $user->id);
                if (!$status_course) {
                    $status_code = 'blocked';
                } else {
                    $status_user = $status_activities?->firstWhere('model_id', $user->id);
                    if ($status_user && $status_code != 'blocked') {
                        $status_code = floatval($status_user->percent_progress) >= 100.0 ? 'completed' : 'in-progress';
                    }
                }
            }
            $user['status'] = $statuses->firstWhere('code', $status_code);
        }
        /* Listar los bloqueados al final */
        $users = $users->sortBy(function ($user) {
            return $user->status['code'] === 'blocked';
        })->values();
        return [
            'checklist' =>[
                "id" => $checklist->id,
                "title" => $checklist->title,
                "description" => $checklist->description,
                "modality" => [
                    'id' => $checklist->modality->id,
                    'name' => $checklist->modality->alias,
                    'code' => $checklist->modality->code,
                    'color' => $checklist->modality->color
                ],
                "type" => $checklist->type,
                "required_geolocalization"=>$checklist->extra_attributes['required_geolocation'],
                "imagen" => get_media_url($checklist->imagen),
            ],
            'users' => $users,
            'users_assigned'=> count($users),
            'status' => [
                [
                    'name' => 'pendiente',
                    'code' => 'pending',
                    'quantity' => $count_users_pending,
                    'percent' => $percent_pending.'%'
                ] ,
                [
                    'name' => 'Realizado',
                    'code' => 'done',
                    'quantity' =>  $count_users_completed,
                    'percent' => $percent_completed.'%'
                ] 
            ] 
        ];
    }
    public function listEntities($request){
        $checklist = $this;
        $user = auth()->user();
        $entities_criteria = Workspace::where('id',$checklist->workspace_id)->select('checklist_configuration')
                            ->first()->checklist_configuration->entities_criteria;
        $segments = Segment::select('id')
                                ->where('model_id',$checklist->id)
                                ->where('model_type','App\\Models\\Checklist')
                                ->get();
        $segments_checklist = SegmentValue::whereIn('segment_id',$segments->pluck('id'))
                                ->whereIn('criterion_id',$entities_criteria)
                                ->with(['criterion_value:id,value_text,criterion_id'])
                                ->when($request->search,function($q)use($request){
                                    $q->whereHas('criterion_value',function($cv)use($request){
                                        $cv->where('value_text','like','%'.$request->search.'%');
                                    });
                                })
                                ->paginate(4, ['*'], 'page', (int) $request->page);
        
        $entities = $segments_checklist->map(function($entity)use ($checklist,$user){
                        $filters = [
                            ['statement'=>'where','field'=>'checklist_finished','value'=>1]
                        ];
                        $audit = ChecklistAudit::getCurrentChecklistAudit(
                                    $checklist,CriterionValue::class,$entity->criterion_value->id,$user,
                                    false,true,$filters
                                );
                        return [
                            'id' => $entity->criterion_value->id,
                            'name' => $entity->criterion_value->value_text,
                            'finished' => boolval($audit)
                        ];
                    });
       
        $checklist->load('type:id,name,code,color');
        $checklist->load('modality:id,name,code,color');
        return [
            'checklist' => [
                'id' => $checklist->id,
                "title" => $checklist->title,
                "imagen" => get_media_url($checklist->imagen),
                "required_geolocalization"=> $checklist->extra_attributes['required_geolocation'],
                "description" => $checklist->description,
                "type" => $checklist->type,
                'modality' => $checklist->modality
            ],
            'entities' => $entities,
            'paginate' =>[
                'current_page' => $segments_checklist->currentPage(),
                'last_page' => $segments_checklist->lastPage(),
                'total' => $segments_checklist->total()
            ]
        ];
    }
    //SUBFUNCTIONS
    function buildChecklistData($checklist,$user) {

        $audit  = ChecklistAudit::select('date_audit')
                                ->where('checklist_id',$checklist->id)
                                ->when($checklist->extra_attributes['replicate'],function($q){
                                    $now = now()->format('Y-m-d');
                                    $q->whereDate('date_audit','=',$now);
                                })
                                ->when($checklist->extra_attributes['view_360'], function($q) use($user){
                                    $q->where('auditor_id',$user->id);
                                })
                                ->first();

        $status = $audit ? [
                    'code' => 'realizado',
                    'name' => 'Realizado '.$audit->date_audit,
                    'color' => '#25B374'
                ] : [
                    'code' => 'pendiente',
                    'name' => 'Pendiente',
                    'color' => '#CDCDEB'
                ];
        $auditor_calificate_all_entity = $checklist->modality->code=='qualify_entity' 
                                            && isset($checklist->extra_attributes['auditor_calificate_all_entity'])
                                            && $checklist->extra_attributes['auditor_calificate_all_entity'];
        
        return [
            "id" => $checklist->id,
            "title" => $checklist->title,
            "status" => $status,
            "description" => $checklist->description,
            "modality" => [
                'id' => $checklist->modality->id,
                'name' => $checklist->modality->alias,
                'code' => $checklist->modality->code,
                'color' => $checklist->modality->color
            ],
            "type" => $checklist->type,
            'url_maps' =>'',
            'auditor_calificate_all_entity'=> $auditor_calificate_all_entity
        ];
    }
    
    function processChecklistWithGeolocation($user, $workspace_entity_criteria, $_checkist_data, &$list_checklists_geolocalization, &$list_checklists_exclude_geolocalization, $user_latitude, $user_longitude) {
        $criterion_value_user_entity = $user->criterion_values->whereIn('criterion_id', $workspace_entity_criteria)->first();
        $lat_long_entity = CriterionValue::select('value_text')->where('parent_id', $criterion_value_user_entity->id)->first()->value_text;
        [$reference_latitude, $reference_Longitude] = explode(',', $lat_long_entity);
        $_checkist_data['url_maps'] = "https://www.google.com/maps?q={$reference_latitude},{$reference_Longitude}";
        $distance = $this->calculateDistance($user_latitude, $user_longitude, $reference_latitude, $reference_Longitude);
        $withinRange = $distance <= 0.05;
        $entity = $withinRange ? $list_checklists_geolocalization->where('nombre', $criterion_value_user_entity->value_text)->first() : $list_checklists_exclude_geolocalization->where('nombre', $criterion_value_user_entity->value_text)->first();
        if($withinRange){
            $this->updateChecklistsList($entity, $_checkist_data, $list_checklists_geolocalization, $criterion_value_user_entity->value_text);
        }else{
            $this->updateChecklistsList($entity, $_checkist_data, $list_checklists_exclude_geolocalization, $criterion_value_user_entity->value_text);
        }
    }
    
    function processChecklistWithoutGeolocation($_checkist_data, &$list_checklists_libres) {
        $entity = $list_checklists_libres->where('nombre', 'Libre')->first();
        $this->updateChecklistsList($entity, $_checkist_data, $list_checklists_libres, 'Libre');
    }
    
    function updateChecklistsList($entity, $_checkist_data, &$listToUpdate, $entityName) {
        if ($entity) {
            $entity['lista'][] = $_checkist_data;
            $listToUpdate = $listToUpdate->map(function ($item) use ($entity) {
                if ($item['nombre'] === $entity['nombre']) {
                    return $entity;
                }
                return $item;
            });
        } else {
            $listToUpdate->push([
                'nombre' => $entityName,
                'lista' => [$_checkist_data]
            ]);
        }
    }
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // Radio de la Tierra en kilómetros

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return $distance;
    }
}
