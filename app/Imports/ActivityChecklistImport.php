<?php

namespace App\Imports;

use App\Models\Taxonomy;
use App\Models\CheckListItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ActivityChecklistImport implements ToCollection
 {
    /**
    * @param Collection $collection
    */
    public array $activities = [];
    public $checklist = null;
    public function collection( Collection $collection )
 {
        $isGroupedByArea = $this->checklist->isGroupedByArea();
        $type_response_activity = collect([
            ['code'=> 'scale_evaluation','label'=>'escala de evaluación'],
            ['code'=> 'custom_option','label'=>'opción única'],
            ['code'=> 'write_option','label'=>'texto'],
        ]);
        $workspace = get_current_workspace();
        for ( $i = 2; $i < count( $collection );$i++ ) {
            if($isGroupedByArea){
                $area = trim(strtolower( $collection[ $i ][ 0 ]));
                $tematica = trim(strtolower( $collection[ $i ][ 1 ]));
                $activity =  trim($collection[ $i ][ 2 ]);
                if($area && $tematica && $activity){
                    $label_evaluation = strtolower( $collection[ $i ][ 3 ]);
                    $code_evaluation = $type_response_activity->where('label',$label_evaluation)->first()['code'];
                    $checklist_response = Taxonomy::where('group','checklist')
                                                    ->where('type','type_response_activity')
                                                    ->where('code',$code_evaluation)
                                                    ->first(); 
                    $_area = Taxonomy::where('group','checklist')
                                    ->where('type','areas')
                                    ->where('workspace_id',$workspace->id)
                                    ->where('name',$area)->first();
                    if(!$_area){
                        $_area = Taxonomy::create(
                            [
                                'workspace_id' => $workspace->id,
                                'group' => 'checklist',
                                'type' => 'areas',
                                'name' => $area,
                                'active' => 1,
                            ]
                        );
                    }
                    $_tematica = Taxonomy::where('group','checklist')
                        ->where('type','tematicas')
                        ->where('parent_id',$_area->id)
                        ->where('name',$tematica)
                        ->first();
                    if(!$_tematica){
                        $_tematica = Taxonomy::create([
                            'workspace_id' => $workspace->id,
                            'group' => 'checklist',
                            'type' => 'tematicas',
                            'name' => $tematica,
                            'parent_id' => $_area->id,
                            'active'=>1,
                        ]);
                    }
                    $is_evaluable = isset( $collection[ $i ][ 4 ] ) && ( strtolower( $collection[ $i ][ 4 ] ) == 'sí' || strtolower( $collection[ $i ][ 4 ] ) == 'si' );
                    $photo_response = isset( $collection[ $i ][ 5 ] ) && ( strtolower( $collection[ $i ][ 5 ] ) == 'sí' || strtolower( $collection[ $i ][ 5 ] ) == 'si' );
                    $comment_activity = isset( $collection[ $i ][ 6 ] ) && ( strtolower( $collection[ $i ][ 6 ] ) == 'sí' || strtolower( $collection[ $i ][ 6 ] ) == 'si' );
                    $extra_attributes = [
                        "is_evaluable" => $is_evaluable,
                        "photo_response" => $photo_response,
                        "comment_activity" => $comment_activity,
                        "computational_vision" => false,
                        "type_computational_vision" => null,
                        "type_computational_value" => null
                    ];
                    $data_activity = [
                        'area_id' => $_area->id,
                        'tematica_id' => $_tematica->id,
                        'activity' => $activity,
                        'checklist_id' => $this->checklist->id,
                        'active' => 1,
                        // 'position' => $data_activity[ 'position' ],
                        'checklist_response_id'=> $checklist_response->id,
                        'extra_attributes' => json_encode($extra_attributes),
                    ];
                    CheckListItem::insert($data_activity);
                }
            }else{
                $activity =  trim($collection[ $i ][ 0 ]);
                if($activity){
                    $label_evaluation = strtolower( $collection[ $i ][ 1 ]);
                    $code_evaluation = $type_response_activity->where('label',$label_evaluation)->first()['code'];
                    $checklist_response = Taxonomy::where('group','checklist')
                                                    ->where('type','type_response_activity')
                                                    ->where('code',$code_evaluation)
                                                    ->first(); 
                 
                    $is_evaluable = isset( $collection[ $i ][ 2 ] ) && ( strtolower( $collection[ $i ][ 2 ] ) == 'sí' || strtolower( $collection[ $i ][ 2 ] ) == 'si' );
                    $photo_response = isset( $collection[ $i ][ 3 ] ) && ( strtolower( $collection[ $i ][ 3 ] ) == 'sí' || strtolower( $collection[ $i ][ 3 ] ) == 'si' );
                    $comment_activity = isset( $collection[ $i ][ 4 ] ) && ( strtolower( $collection[ $i ][ 4 ] ) == 'sí' || strtolower( $collection[ $i ][ 4 ] ) == 'si' );
                    $extra_attributes = [
                        "is_evaluable" => $is_evaluable,
                        "photo_response" => $photo_response,
                        "comment_activity" => $comment_activity,
                        "computational_vision" => false,
                        "type_computational_vision" => null,
                        "type_computational_value" => null
                    ];
                    $data_activity = [
                        'activity' => $activity,
                        'checklist_id' => $this->checklist->id,
                        'active' => 1,
                        // 'position' => $data_activity[ 'position' ],
                        'checklist_response_id'=> $checklist_response->id,
                        'extra_attributes' => json_encode($extra_attributes),
                    ];
                    CheckListItem::insert($data_activity);
                }
            }
        }
    }
}
