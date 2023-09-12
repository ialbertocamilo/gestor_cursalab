<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Silber\Bouncer\Database\Concerns\IsAbility;

class Ability extends Model
{
    use IsAbility;
    // protected $rememberFor = WEEK_MINUTES;
    // protected $connection = 'pgsql';

    // protected $fillable = ['parent_id', 'name', 'slug', 'description', 'type', 'percentage', 'quantity'];
    // protected $fillable = [];
    protected $guarded = [];

    // protected $hidden=[
    //   'pivot'
    // ];

    // protected $with = ['children', 'parent'];

    // public function sluggable(): array
    // {
    //    return [
    //        'slug' => [ 'source' => 'name', 'onUpdate' => true, 'unique' => false]
    //    ];
    // }

    // public function type()
    // {
    //    return $this->belongsTo(Taxonomy::class, 'type_id');
    // }

    // public function service()
    // {
    //    return $this->belongsTo(Taxonomy::class, 'service_id');
    // }

    public function model()
    {
        // return $this->belongsTo(Taxonomy::class, 'entity_type', 'path')
        //     ->where('group', 'gestor')->where('type', 'submenu');
        return $this->belongsTo(Taxonomy::class, 'entity_id', 'id')
                ->where('group', 'gestor')->where('type', 'submenu');
    }

    protected function getAbilititesForTree()
    {
        $groups = Ability::with(['model:id,parent_id,name,position,icon'])->whereNotNull('entity_id')->get();
        $menus = Menu::list();
        $data = [];
        foreach ($menus as $menu) {
            $permissions = [];
            $groupsByMenu = $groups->where('model.parent_id',$menu->id)->sortBy('model.position')
            ->groupBy('entity_id');
            foreach ($groupsByMenu as $entity_type => $abilities) {
    
                $children = [];
    
                foreach ($abilities as $key => $ability) {
                    $children[] = [
                        // 'id' => $ability->name,
                        'id' => $ability->id,
                        'name' => $ability->title,
                        'icon' => $ability->icon ?? 'mdi-circle'
                    ];
                }
    
                $parent = [
                    'id' => $entity_type,
                    'name' => ($ability->model->name ?? 'X'),
                    'avatar' => '',
                    'icon' => $ability->model->icon ?? 'mdi-folder',
                    'children' => $children,
                ];
    
                $permissions[] = $parent;
            }
            if(count($permissions)>0){
                $data[] =[
                    'id' => $menu->id,
                    'name' => ($menu->name ?? 'X'),
                    'avatar' => '',
                    'icon' => $menu->icon ?? 'mdi-folder',
                    'children' => $permissions,
                ];
            }
        }

        // $data[] = ['id' => 'All', 'label' => 'Seleccionar módulos', 'children' => $permissions];
        // $data = $permissions;

        return $data;
    }
}
