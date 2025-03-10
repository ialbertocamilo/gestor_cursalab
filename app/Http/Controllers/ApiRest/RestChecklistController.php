<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\User;
use App\Models\Jarvis;
use App\Models\Segment;
use App\Models\Taxonomy;
use App\Models\CheckList;
use App\Models\Workspace;
use App\Models\SegmentValue;
use Illuminate\Http\Request;
use App\Models\CheckListItem;
use App\Models\ChecklistRpta;
use App\Models\ChecklistAudit;
use App\Models\CriterionValue;
use App\Models\SummaryChecklist;
use App\Models\ChecklistRptaItem;
use App\Models\EntrenadorUsuario;
use App\Http\Controllers\Controller;
use App\Models\SummaryUserChecklist;
use Illuminate\Support\Facades\Auth;

class RestChecklistController extends Controller
{

    public function alumnos(Request $request)
    {
        $user = Auth::user();
        $data = [
            'entrenador_dni' => $user->document,
            'filtro' => $request->filtro,
            'page' => $request->page ?? null
        ];
        $apiResponse = EntrenadorUsuario::alumnosApi($data);

        return response()->json($apiResponse, 200);
    }

    public function getChecklistsByAlumno(User $alumno)
    {
        $response = CheckList::getChecklistsByAlumno($alumno->id);

        return response()->json($response, 200);
    }
    public function getChecklistInfo($checklist_id){
        $user = Auth::user();
        $response = CheckList::getChecklistInfo($checklist_id,$user);
        return response()->json($response, 200);
    }
    public function getChecklistsByTrainer(Request $request)
    {
        $trainer = Auth::user();
        $data = [
            'trainer' => $trainer,
            'filtro' => $request->filtro,
            'page' => $request->page ?? null
        ];
        $response = CheckList::getChecklistsByTrainer($data);

        return response()->json($response, 200);
    }

    public function getStudentsByChecklist(Request $request)
    {
        $trainer = Auth::user();
        // $trainer = User::where('document',31313131)->first();
        // $response = CheckList::getStudentsByChecklist($checklist->id,$trainer->id);
        $data = [
            'trainer' => $trainer,
            'checklist_id' => $request->checklist_id,
            'page' => $request->page ?? null
        ];
        $response = CheckList::getStudentsByChecklist($data);

        return response()->json($response, 200);
    }
    public function marcarActividadOld(Request $request)
    {
        $alumno_id = $request->alumno_id;
        $entrenador = EntrenadorUsuario::where('user_id', $alumno_id)->where('active', 1)->first();
        $checklist_id = $request->checklist_id;

        $estado = $request->estado;
        $actividad_id = $request->actividad_id;

        $checklistRpta = ChecklistRpta::checklist($checklist_id)->alumno($alumno_id)->entrenador($entrenador->trainer_id)->first();

        $checklistRptaItem = ChecklistRptaItem::where('checklist_answer_id', $checklistRpta->id)->where('checklist_item_id', $actividad_id)->first();
        if (!$checklistRptaItem) {
            $checklistRptaItem = ChecklistRptaItem::create([
                'checklist_answer_id' => $checklistRpta->id,
                'checklist_item_id' => $actividad_id,
                'qualification' => $estado
            ]);
        }
        $checklistRptaItem->qualification = $estado;
        $checklistRptaItem->save();

        ChecklistRpta::actualizarChecklistRpta($checklistRpta);

        return response()->json([
            'error' => false,
            'msg' => [
                'titulo' => '',
                'texto' => ''
            ]
            /*'checklist_rpta_item' => $checklistRptaItem*/
        ], 200);
    }
    public function marcarActividad(Request $request)
    {
        //en caso de todos sacar los usuarios por el auth::user()->id
        // [
        //     'actividades' => [
        //          ['id'=>12,'estado'=>'pendiente'],
        //          ['id'=>14,'estado'=>'En proceso'],
        //          ['id'=>15,'estado'=>'Cumple'],
        //          ['id'=>15,'estado'=>'No Cumple'],
        //     ],
        //     'checklist_id'=>45,
        //     'alumnos_id' => [12,23,34],
        //     'alumnos_todos'=>true,
        //      'tipo' => 'entrenador_alumno ' o 'alumno_entrenador'
        // ]
        $checklist_id = $request->checklist_id;
        $checklist = Checklist::select('id','title','type_id')->with('type:id,code','actividades','actividades.type:id,code')->where('id',$checklist_id)->first();
        $actividades = $request->actividades;
        $alumnos_id = $request->alumnos_id;
        $alumnos_todos = $request->alumnos_todos;
        $tipo = $request->tipo;
        $feedback_entrador = $request->feedback_entrador ?? null;
        $message = [];

        $entrenador_id = ($tipo =='entrenador_alumno')
                        ? Auth::user()->id
                        : EntrenadorUsuario::where('user_id', $alumnos_id[0])->where('active', 1)->first()?->trainer_id;

        $query_entrenador_usuario =  EntrenadorUsuario::select('user_id')->with(['user:id,subworkspace_id','user.subworkspace:id,parent_id','user.subworkspace.parent:id']);
        if($alumnos_todos){
            if($checklist->type->code == 'curso'){
                $courses_id = $checklist->courses()->get()->pluck('id');
                if(count($courses_id)){
                    $status_id_completed = Taxonomy::where('group', 'course')->where('type','user-status')->where('code','aprobado')->first()?->id;
                    $alumnos = $query_entrenador_usuario->where('trainer_id',$entrenador_id)->where('active', 1)
                    ->whereHas('user.summary_courses',function($q)use ($courses_id,$status_id_completed) {
                        foreach ($courses_id as $key => $course_id) {
                            $q->where('course_id',$course_id);
                        }
                        $q->where('status_id',$status_id_completed);
                    })->get();
                }else{
                    $alumnos = [];
                }
            }else{
                $alumnos = $query_entrenador_usuario->where('trainer_id',$entrenador_id)->where('active', 1)->get();
            }
        }else{
            $alumnos = $query_entrenador_usuario->alumno($alumnos_id)->groupBy('user_id')->get();
        }
        $checklistRptas = ChecklistRpta::checklist($checklist_id)->alumno($alumnos->pluck('user_id')->toArray())->entrenador($entrenador_id)->get();
        foreach ($alumnos as $alumno) {
            $checklistRpta = $checklistRptas->where('student_id',$alumno->user_id)->first();
            if(is_null($checklistRpta)){
                $checklistRpta = ChecklistRpta::create([
                    'checklist_id' => $checklist_id,
                    'student_id' => $alumno->user_id,
                    'coach_id' => $entrenador_id,
                    'percent' => 0
                ]);
            }
            foreach ($actividades as $key => $actividad) {
                $checklistRptaItem = ChecklistRptaItem::where('checklist_answer_id', $checklistRpta->id)->where('checklist_item_id', $actividad['id'])->first();
                if (!$checklistRptaItem) {
                    $checklistRptaItem = ChecklistRptaItem::create([
                        'checklist_answer_id' => $checklistRpta->id,
                        'checklist_item_id' => $actividad['id'],
                        'qualification' => $actividad['estado']
                    ]);
                }
                $checklistRptaItem->qualification = $actividad['estado'];
                $checklistRptaItem->save();
            }
            if(($tipo =='alumno_entrenador') ){
                $checklistRpta->feedback_entrador = $feedback_entrador ;
                $checklistRpta->save();
            }
            ChecklistRpta::actualizarChecklistRpta($checklistRpta);
            SummaryUserChecklist::updateUserData($alumno->user);
        }

        // SummaryChecklist::updateData($checklist);
        //Personalizar respuestas.
        if($tipo == 'alumno_entrenador'){
            $message = [
                'title'=> 'Se ha realizado la evaluación',
                'body'=>'Se ha realizado la evaluación del checklist: <b>'.$checklist->title.'</b>'
            ];
        }else{
            $actividades = collect($actividades);
            $actividades_cumple = $actividades->where('estado','No cumple')->count();
            $actividades_no_cumple = $actividades->where('estado','Cumple')->count();
            if( $actividades_cumple + $actividades_no_cumple  == $checklist->actividades->where('type.code','trainer_user')->where('active',1)->count()){
                $message = [
                    "titulo" => "Checklist finalizado",
                    "mensaje" => 'Se han calificado todas las actividades del checklist: <b>'.$checklist->title.'</b>'
                ];
            }else{
                $message = [
                    "titulo" => "Cambios guardados",
                    "mensaje" => 'Se han guardado las calificaciones del checklist: <b>'.$checklist->title.'</b>'
                ];
            }
        }
        return response()->json([
            'error' => false,
            'message' => $message
            /*'checklist_rpta_item' => $checklistRptaItem*/
        ], 200);
    }
    //*CHECKLIST V3*/
    public function getInitData(){
        return [
            'is_required_geololization' => true
        ];
    }
    public function checklistsTrainer(Request $request){

        [$list_checklists_geolocalization,
        $list_checklists_exclude_geolocalization,
        $list_checklists_libres] = CheckList::listsChecklistsToTrainer($request); 

        return $this->success(compact('list_checklists_geolocalization','list_checklists_libres','list_checklists_exclude_geolocalization'));
    }
    
    public function listEntities(CheckList $checklist,Request $request){
       return $checklist->listEntities($request);
    }

    public function activitiesByChecklist(CheckList $checklist,Request $request){
        $data = CheckList::listActivities($checklist,$request);
        return $this->success($data);
    }
    public function saveActivities(CheckList $checklist,Request $request){
        $data = $request->all();
        ChecklistAudit::saveActivitiesAudits($checklist,$data,$request);
        return $this->success([
            'message' => 'Actividad guardada correctamente.'
        ]);
    }
    public function verifyPhoto(CheckListItem $activity,Request $request){
        $data = Jarvis::verifyPhoto($activity,$request); 
        return $this->success([
            'color' => $data['is_verified'] ? '#00E396' : '#FF0000',
            'percent' => $data['similarity'] ?? 0,
            'label' => $data['is_verified'] ? 'Excelente' : 'Necesita Mejorar',
            'verified' => $data['is_verified'],
        ]);
    }

    public function listProgress(CheckList $checklist){
        $activity_progress = ChecklistAudit::listProgress($checklist);
        return $this->success([
            'activity_progress' => $activity_progress
        ]);
    }

    public function listUsers(CheckList $checklist,Request $request){
        $data = CheckList::listUsers($checklist,$request);
        return $this->success($data);
    }

    public function saveActivity(CheckList $checklist,Request $request){
        $data = $request->all();
        $action_request = $request->action_request;
        $response = ChecklistAudit::saveActivity($checklist,$data,$action_request,$request);
        return $this->success($response);
    }

    public function listThemes(CheckList $checklist,Request $request){
        $response = $checklist->listThemes($request);
        return $this->success($response);
    }

    public function saveActionPlan(CheckList $checklist,Request $request){
        $response = ChecklistAudit::saveActionPlan($checklist,$request->all(),$request);
        return $this->success($response);
    }
    public function saveSignatureSupervised(CheckList $checklist,Request $request){
        $response = ChecklistAudit::saveSignatureSupervised($checklist,$request->all(),$request);
        return $this->success($response);
    }

    public function checklistsProgress(){
        $user = auth()->user();
        $data = [
            // 'fullname'=> $user,
            'entity_name' => 'Tienda',
            'graphic' => [
                'labels'=>[ 'Excelente', 'Regular', 'Deficiente' ],
                'colors' => [
                    'rgba(0, 227, 150, 1)',
                    'rgba(255, 183, 0, 1)',
                    'rgba(255, 69, 96, 1)'
                ],
                'data'=> [ 350, 450, 100 ],
            ],
            'checklists'=>[
                [
                    "id" => 1,
                    "title" => 'Checklist 1',
                    "status" => 'Pendiente',
                    "modality" => [
                        'id' => 1,
                        'name' => 'Entidad',
                        'code' => 'entity',
                        'color' => '#5C8D7D'
                    ],
                    "type" => 'curso',
                    'url_maps' =>''
                ],
                [
                    "id" => 2,
                    "title" => 'Checklist 1',
                    "status" => 'Pendiente',
                    "modality" => [
                        'id' => 1,
                        'name' => 'Entidad',
                        'code' => 'entity',
                        'color' => '#5C8D7D'
                    ],
                    "type" => 'curso',
                    'url_maps' =>''
                ],
                [
                    "id" => 3,
                    "title" => 'Checklist 1',
                    "status" => 'Pendiente',
                    "modality" => [
                        'id' => 1,
                        'name' => 'Entidad',
                        'code' => 'entity',
                        'color' => '#5C8D7D'
                    ],
                    "type" => 'curso',
                    'url_maps' =>''
                ],
            ]
        ];
        return $this->success($data);
    }
    
    public function listSupervisedChecklist(Checklist $checklist,CriterionValue $entity,Request $request){
        $data = Checklist::listSupervisedChecklist($checklist,$entity,$request);
        return $this->success($data);
    }
}
