<?php

namespace App\Http\Controllers\ApiRest;

use App\Models\User;
use App\Models\Segment;
use App\Models\Taxonomy;
use App\Models\CheckList;
use App\Models\Workspace;
use App\Models\SegmentValue;
use Illuminate\Http\Request;
use App\Models\ChecklistRpta;
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
        $user = auth()->user();
        $user_latitude = $request->lat;
        $user_longitude = $request->long;
        
        $checklists  = $user->getSegmentedByModelType(
                            model:CheckList::class,
                            withModelRelations:['modality:id,name,code,color,alias','type:id,name,color,code'],
                            unset_criterion_values:false
                        );
        $list_checklists_geolocalization = collect();
        $list_checklists_exclude_geolocalization = collect();
        $list_checklists_libres = [];
        $workspace_entity_criteria = Workspace::select('checklist_configuration')
                                        ->where('id',$user->subworkspace->parent->id)
                                        ->first()?->checklist_configuration?->entities_criteria;
        foreach ($checklists as $checklist) {
            $_checkist_data = [
                "id" => $checklist->id,
                "title" => $checklist->title,
                "status" => 'pendiente',
                "modality" => [
                    'id'=> $checklist->modality->id,
                    'name' => $checklist->modality->alias,
                    'code'=>$checklist->modality->code,
                    'color'=>$checklist->modality->color
                ],
                "type" =>$checklist->type,
            ];
            if($checklist->extra_attributes['required_geolocation']){
                $criterion_value_user_entity =  $user->criterion_values->whereIn('criterion_id',$workspace_entity_criteria)->first();
                $lat_long_entity = CriterionValue::select('value_text')->where('parent_id',$criterion_value_user_entity->id)->first()->value_text;
                // $criterion_value_id = Segment::where('model_type',CheckList::class)->where('model_id',$checklist->id)
                //             ->withWhereHas('values',function($q) use ($workspace_entity_criteria,$criterion_value_user_entity){
                //                 $q->whereIn('criterion_id', $workspace_entity_criteria)->where('criterion_value_id',$criterion_value_user_entity->id);
                //             })->first()->values()->first();
                [$reference_latitude,$reference_Longitude] = explode(',',$lat_long_entity);
                // Calcular la distancia entre las coordenadas del usuario y las coordenadas de referencia
                $distance = $this->calculateDistance($user_latitude, $user_longitude, $reference_latitude, $reference_Longitude);
                // Verificar si la distancia es menor o igual a 30 metros
                $withinRange = $distance <= 0.30;
                if($withinRange){
                    $entity = $list_checklists_geolocalization->where('nombre', $criterion_value_user_entity->value_text)->first();
                    if($entity){
                        $entity['lista'][] = $_checkist_data;
                        $list_checklists_geolocalization = $list_checklists_geolocalization->map(function ($item) use ($entity) {
                            if ($item['nombre'] === $entity['nombre']) {
                                return $entity;
                            }
                            return $item;
                        });
                    }else{
                        $list_checklists_geolocalization->push([
                            'nombre' => $criterion_value_user_entity->value_text,
                            'lista' => [$_checkist_data]
                        ]);
                    }
                }else{
                    $entity = $list_checklists_exclude_geolocalization->where('nombre', $criterion_value_user_entity->value_text)->first();
                    if($entity){
                        $entity['lista'][] = $_checkist_data;
                        $list_checklists_exclude_geolocalization = $list_checklists_exclude_geolocalization->map(function ($item) use ($entity) {
                            if ($item['nombre'] === $entity['nombre']) {
                                return $entity;
                            }
                            return $item;
                        });
                    }else{
                        $list_checklists_exclude_geolocalization->push([
                            'nombre' => $criterion_value_user_entity->value_text,
                            'lista' => [$_checkist_data]
                        ]);
                    }
                }
            }else{
                $entity = $list_checklists_exclude_geolocalization->where('nombre', 'Libre')->first();
                if($entity){
                    $entity['lista'][] = $_checkist_data;
                    $list_checklists_exclude_geolocalization = $list_checklists_exclude_geolocalization->map(function ($item) use ($entity) {
                        if ($item['nombre'] === $entity['nombre']) {
                            return $entity;
                        }
                        return $item;
                    });
                }else{
                    $list_checklists_exclude_geolocalization->push([
                        'nombre' => 'Libre',
                        'lista' => [$_checkist_data]
                    ]);
                }
            }
        }
        // dd($list_checklists_geolocalization->toArray());
        // $list_checklists_geolocalization = [
        //     [
        //         "nombre" => "Ecnorza ZC",
        //         "lista" => [
        //             [
        //                 "id" => 1,
        //                 "modality" => [
        //                     'id'=> 5886,
        //                     'name' => 'Entidad',
        //                     'code'=>'qualify_entity',
        //                     'color'=>'#9B98FE'
        //                 ],
        //                 "type" => [
        //                     'id'=> 5077,
        //                     'name' => 'Auditoria',
        //                     'code'=>'libre',
        //                     'color'=>'#CE98FE'
        //                 ],
        //                 "title" => "Trabajo realizado por el usuario en su estación.",
        //                 "status" => [
        //                     'code'=>'pending',
        //                     'name'=>'Pendiente',
        //                     'color'=>'#CDCDEB'
        //                 ]
        //             ]
        //         ]
        //     ],
        // ];

        // $list_checklists_libres = [
        //     [
        //         "nombre" => "Checklist libre",
        //         "lista" => [
        //             [
        //                 "id" => 2,
        //                 "modality" => [
        //                     'id'=> 5886,
        //                     'name' => 'Entidad',
        //                     'code'=>'qualify_entity',
        //                     'color'=>'#9B98FE'
        //                 ],
        //                 "type" => [
        //                     'id'=> 5077,
        //                     'name' => 'Auditoria',
        //                     'code'=>'libre',
        //                     'color'=>'#CE98FE'
        //                 ],
        //                 "title" => "Trabajo realizado por el usuario en su estación.",
        //                 "status" => "pendiente"
        //             ],
        //             [
        //                 "id" => 3,
        //                 "modality" => [
        //                     'id'=> 5887,
        //                     'name' => 'Usuario',
        //                     'code'=>'qualify_user',
        //                     'color'=>'#9B98FE'
        //                 ],
        //                 "type" => [
        //                     'id'=> 5077,
        //                     'name' => 'Curso',
        //                     'code'=>'curso',
        //                     'color'=>'#00E396'
        //                 ],
        //                 "title" => "Reconoce los puntos de salida de seguridad en caso de sismo.",
        //                 "status" => [
        //                     'code'=>'Continuo',
        //                     'name'=>'Continuo',
        //                     'color'=>'#6E73DA'
        //                 ]
        //             ]
        //         ]
        //     ]
        // ];

        // $list_checklists_exclude_geolocalization = [
        //     [
        //         "nombre" => "Ecnorza AB",
        //         "lista" => [
        //             [
        //                 "id" => 4,
        //                 "modality" => [
        //                     'id'=> 5886,
        //                     'name' => 'Entidad',
        //                     'code'=>'qualify_entity',
        //                     'color'=>'#9B98FE'
        //                 ],
        //                 "type" => [
        //                     'id'=> 5077,
        //                     'name' => 'Curso',
        //                     'code'=>'curso',
        //                     'color'=>'#00E396'
        //                 ],
        //                 "title" => "Reconoce los puntos de salida de seguridad en caso de sismo.",
        //                 "status" => [
        //                     'code'=>'Continuo',
        //                     'name'=>'Continuo',
        //                     'color'=>'#6E73DA'
        //                 ]
        //             ]
        //         ]
        //     ],
        //     [
        //         "nombre" => "Ecnorza AR",
        //         "lista" => [
        //             [
        //                 "id" => 5,
        //                 "modality" => [
        //                     'id'=> 5888,
        //                     'name' => 'Autoev.',
        //                     'code'=>'autoqualify',
        //                     'color'=>'#9B98FE'
        //                 ],
        //                 "type" => [
        //                     'id'=> 5077,
        //                     'name' => 'Auditoria',
        //                     'code'=>'libre',
        //                     'color'=>'#CE98FE'
        //                 ],
        //                 "title" => "Gestión de inventario.",
        //                 "status" => [
        //                     'code'=>'realizado',
        //                     'name'=>'Realizado 21 -03 - 2024',
        //                     'color'=>'#25B374'
        //                 ]
        //             ]
        //         ]
        //     ]
        // ];
        
        return $this->success(compact('list_checklists_geolocalization','list_checklists_libres','list_checklists_exclude_geolocalization'));
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
    public function activitiesByChecklist(Request $request){
        $activities = [
            [
                'id'=>1021,
                'name'=>'Actividad 01',
                'description'=> 'Identificar los riesgos específicos en el lugar de trabajo que requieren el uso de equipos de protección personal.',
                'can_comment'=> true,
                'can_upload_image'=>true,
                'type_system_calification'=>'scale_evaluation',
                'system_calification' => [
                    [
                        'id'=> 1293,
                        'name'=>'Excelente',
                        'color'=>'#00E396'
                    ],
                    [
                        'id'=> 1294,
                        'name'=>'En proceso',
                        'color'=>'#FFD600'
                    ],
                    [
                        'id'=> 1295,
                        'name'=>'Deficiente',
                        'color'=>'#FF4560'
                    ]
                ]
            ],
            [
                'id'=>1022,
                'name'=>'Actividad 01',
                'description'=> 'Identificar los riesgos específicos en el lugar de trabajo que requieren el uso de equipos de protección personal.',
                'can_comment'=> true,
                'can_upload_image'=>false,
                'system_calification' => [
                    [
                        'id'=> 1293,
                        'name'=>'Excelente',
                        'color'=>'#00E396'
                    ],
                    [
                        'id'=> 1294,
                        'name'=>'En proceso',
                        'color'=>'#FFD600'
                    ],
                    [
                        'id'=> 1295,
                        'name'=>'Deficiente',
                        'color'=>'#FF4560'
                    ]
                ]
            ],
            [
                'id'=>1023,
                'name'=>'Actividad 01',
                'description'=> 'Identificar los riesgos específicos en el lugar de trabajo que requieren el uso de equipos de protección personal.',
                'can_comment'=> true,
                'can_upload_image'=>true,
                'system_calification' => [
                    [
                        'id'=> 1293,
                        'name'=>'Excelente',
                        'color'=>'#00E396',
                        "percent" => "100%"
                    ],
                    [
                        'id'=> 1294,
                        'name'=>'En proceso',
                        'color'=>'#FFD600',
                        "percent" => "50%"
                    ],
                    [
                        'id'=> 1295,
                        'name'=>'Deficiente',
                        'color'=>'#FF4560',
                        "percent" => "10%"
                    ]
                ]
            ],
            [
                'id'=>1024,
                'name'=>'Actividad 01',
                'description'=> 'Identificar los riesgos específicos en el lugar de trabajo que requieren el uso de equipos de protección personal.',
                'can_comment'=> false,
                'can_upload_image'=>false,
                // 'laksjdlad'<- tipo de sistema de calificación
                'system_calification' => [
                    [
                        'id'=> 1293,
                        'name'=>'Excelente',
                        'color'=>'#00E396',
                        "percent" => "100%"
                    ],
                    [
                        'id'=> 1294,
                        'name'=>'En proceso',
                        'color'=>'#FFD600',
                        "percent" => "50%"
                    ],
                    [
                        'id'=> 1295,
                        'name'=>'Deficiente',
                        'color'=>'#FF4560',
                        "percent" => "10%"
                    ]
                ]
            ],
        ];
        return $this->success([
            'checklist'=>[
                "title" => "Trabajo realizado por el usuario en su estación.",
                "entity" =>[
                    "name"=>"Ecnorsa ZC",
                    "icon"=>"store",
                ],
                "required_geolocalization"=>true,
                "type" => [
                                'id'=> 5077,
                                'name' => 'Auditoria',
                                'code'=>'libre',
                                'color'=>'#CE98FE'
                ],
                // "image"=>"/",
                "activities"=>$activities
            ]
        ]);
    }
}
