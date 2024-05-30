<?php

namespace App\Models;


class CheckListItem extends BaseModel
{
    const TIPO_ITEM = [
        'trainer_user', // Actividad que evalúa el entrenador a un usuario
        'user_trainer' // Actividad de feedback por parte del usuario al entrenador en cada checklist
    ];

    protected $table = 'checklist_items';

    protected $fillable = [
        'checklist_id',
        'checklist_response_id',
        'extra_attributes',
        'activity',
        'type_id',
        'area_id',
        'tematica_id',
        'active',
        'position'
    ];
    protected $casts = [
        'extra_attributes'=>'json'
    ];
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function checklist()
    {
        return $this->belongsTo(CheckList::class, 'checklist_id');
    }
    public function checklist_response()
    {
        return $this->belongsTo(Taxonomy::class, 'checklist_response_id');
    }
    public function custom_options()
    {
        return $this->hasMany(Taxonomy::class, 'parent_id')->where('group','checklist')->where('type','activity_option');
    }
    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    /*======================================================= SCOPES ==================================================================== */

    public function scopeEstado($q, $estado)
    {
        return $q->where('estado', $estado);
    }

    public function scopeActive($q, $active)
    {
        return $q->where('active', $active);
    }

    /*=================================================================================================================================== */
    protected function chengePositionActivities($checklist,$activities){
        $_activities = [];
        foreach ($activities as $activity) {
            $_activities[] = [
                'id'=>$activity['id'],
                'position'=>$activity['position'],
            ];
        }
        batch()->update(new CheckListItem, $_activities, 'id');
    }
    protected function guardarActividadByID($data)
    {
        $response['error'] = false;
        $actividad  = CheckListItem::updateOrCreate(
            ['id' => $data['id']],
            [
                'activity' => $data['activity'],
                'active' => $data['active'],
                'type_id' => $data['type_id'],
                'checklist_id' => $data['checklist_id']
            ]
        );
        $response['actividad'] = $actividad;
        $response['msg']       = 'Actividad guardada';

        return $response;
    }
    protected function saveArea($data){
        foreach ($data['areas'] as $_area) {
            $workspace = get_current_workspace();
            $area = Taxonomy::firstOrCreate(
                ['id' => $_area['id']],
                [
                    'workspace_id' => $workspace->id,
                    'group' => 'checklist',
                    'type' => 'areas',
                    'name' => $_area['name'],
                    // 'parent_id' => $data['checklist_id'],
                    'active' => 1,
                ]
            );
            self::saveTematica($data['checklist_id'],$area->id,$workspace);
        }
    }
    protected function editArea($data){
        switch ($data['type_edition']) {
            case 'edit_area':
                    Taxonomy::where('id',$data['area']['id'])->update([
                       'name' =>  $data['area']['name']
                    ]);
                break;
            case 'create_area':
                $workspace = get_current_workspace();
                $area = Taxonomy::create(
                    [
                        'workspace_id' => $workspace->id,
                        'group' => 'checklist',
                        'type' => 'areas',
                        'name' => $data['area']['name'],
                        'active' => 1,
                    ]
                );
                Taxonomy::where('group','checklist')
                        ->where('type','tematicas')
                        ->where('parent_id',$data['area']['id'])->update([
                            'parent_id' => $area->id
                        ]);
                self::where('area_id',$data['area']['id'])->where('checklist_id',$data['checklist_id'])->update([
                    'area_id' => $area->id
                ]);
                break;
            default:
                # code...
                break;
        }
    }
    protected function saveTematica($checklist_id,$area_id,$workspace){
        $tematica = Taxonomy::create([
            'workspace_id' => $workspace->id,
            'group' => 'checklist',
            'type' => 'tematicas',
            'name' => 'Temática',
            'parent_id' => $area_id,
            'active'=>1,
        ]);
        $extra_attributes = '{"is_evaluable":true,"photo_response":false,"comment_activity":false,"computational_vision":false,"type_computational_vision":null,"type_computational_value":null}';
        $checklist_response = Taxonomy::getFirstData('checklist','type_response_activity','scale_evaluation'); 
        self::insert([
            'checklist_id' => $checklist_id,
            'area_id' => $area_id,
            'tematica_id' => $tematica->id,
            'checklist_response_id' => $checklist_response->id,
            'Activity' => 'Actividad 1',
            'extra_attributes' => $extra_attributes
        ]);
    }
    protected function groupByAreas($checklist){
        $checklist->load('activities','activities.checklist_response:id,name,code','activities.custom_options:id,parent_id,group,type,name,code');
        $gruped_by_areas_and_tematicas = isset($checklist->extra_attributes['gruped_by_areas_and_tematicas'])
                                        ? $checklist->extra_attributes['gruped_by_areas_and_tematicas'] 
                                        : false;
        if($gruped_by_areas_and_tematicas){
            $data = [];
            $activities_grouped_by_areas = $checklist->activities->groupBy('area_id');
            $taxonomy_areas = Taxonomy::whereIn('id',$checklist->activities->pluck('area_id'))->select('id','name','active')->get();
            $taxonomy_tematicas = Taxonomy::whereIn('id',$checklist->activities->pluck('tematica_id'))->select('id','name','active','parent_id')->get(); 
            foreach ($activities_grouped_by_areas as $area_id => $activities_grouped_by_area) {
                if($area_id){
                    $activities_grouped_by_tematicas = $activities_grouped_by_area->groupBy('tematica_id');
                    $area = $taxonomy_areas->where('id',$area_id)->first();
                    $tematicas = [];
                    foreach ($activities_grouped_by_tematicas as $tematica_id => $activities_grouped_by_tematica) {
                        if($tematica_id){
                            $tematica = $taxonomy_tematicas->where('id',$tematica_id)->first();
                            if($tematica){
                                $tematicas[] = [
                                    'id' => $tematica->id,
                                    'name' => $tematica->name,
                                    'parent_id' => $tematica->parent_id,
                                    'active' => $tematica->active,
                                    'activities' => $activities_grouped_by_tematica,
                                ];
                            }
                        }
                    }
                    $data[] = [
                        'id' => $area->id,
                        'name' => $area->name,
                        'tematicas'=> $tematicas,
                        'active' => $area->active
                    ];
                }
            }
        }else{
            $data['activities'] = $checklist->activities;
            // ->sortBy('position')->values()->all();
        }
        return $data;
    }
    protected function formSelectsActivities($checklist){
        $checklist_type_response = Taxonomy::getDataForSelect('checklist', 'type_response_activity');
        $is_checklist_premium = boolval(get_current_workspace()->functionalities()->where('code','checklist-premium')->first());
        $chips = [];
        $checklist_name = $checklist->title;
        $checklist->load('type:id,name','modality:id,name');
        $chips[] = $checklist->modality->name;
        $chips[] = $checklist->type->name;
        ($checklist->extra_attributes['view_360'] ?? null) && $chips[] = '360';
        ($checklist->extra_attributes['replicate'] ?? null) && $chips[] = 'Recurrente';
        ($checklist->extra_attributes['required_comments'] ?? null) && $chips[] = 'Comentarios';
        ($checklist->extra_attributes['required_geolocation'] ?? null) && $chips[] = 'Geolocalización';
        $gruped_by_areas_and_tematicas = isset($checklist->extra_attributes['gruped_by_areas_and_tematicas'])
                                        ? $checklist->extra_attributes['gruped_by_areas_and_tematicas'] 
                                        : false;
        return compact('checklist_type_response','is_checklist_premium','chips','checklist_name','gruped_by_areas_and_tematicas');
    }

    protected function saveActivity($checklist,$data_activity){
        $data_to_insert = [
            'activity' => $data_activity['activity'],
            'active' => 1,
            // 'type_id' => $data['type_id'],
            'checklist_id' => $checklist->id,
            'position' => $data_activity['position'],
            'checklist_response_id'=>$data_activity['checklist_response']['id'],
            'extra_attributes' => $data_activity['extra_attributes'],
        ];
     
        isset($data_activity['area_id']) && $data_to_insert['area_id'] = $data_activity['area_id'];

        isset($data_activity['tematica_id']) && $data_to_insert['tematica_id'] = $data_activity['tematica_id'];

        $activity = CheckListItem::updateOrCreate(
            ['id' => $data_activity['id']],
            $data_to_insert
        );
        if(isset($data_activity['custom_options']) && count($data_activity['custom_options'])>0 ){
            $custom_options = $data_activity['custom_options'];
            $options_id = [];
            foreach ($custom_options as $option) {
                $option = Taxonomy::updateOrCreate(
                    ['id' => $option['id'],'group' => 'checklist','type'=>'activity_option'],
                    [
                        'group' => 'checklist',
                        'type'  => 'activity_option',
                        'parent_id' => $activity->id,
                        'name'=> $option['name']
                    ]
                );
                $options_id[] = $option->id;
            }
            Taxonomy::where('group','checklist')
                ->where('type','activity_option')
                ->where('parent_id',$activity->id)
                ->whereNotIn('id',$options_id)
                ->delete();
        }
        return [
            'activity_id' =>  $activity->id
        ];
    }

    protected function editTematica($tematica){
        Taxonomy::where('id',$tematica['id'])->update([
            'name' => $tematica['name']
        ]);
    }
    protected function deleteTematica($taxonomy){
        self::where('tematica_id',$taxonomy->id)->delete();
        $taxonomy->delete();
    }
    protected function changeAgrupation($checklist){
        $extra_attributes = $checklist->extra_attributes ;
        $extra_attributes['gruped_by_areas_and_tematicas'] = isset($extra_attributes['gruped_by_areas_and_tematicas'])
        ? !$extra_attributes['gruped_by_areas_and_tematicas']
        : true;
        $checklist->extra_attributes = $extra_attributes;
        $checklist->save();
    }
}
