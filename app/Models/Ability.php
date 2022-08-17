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
        return $this->belongsTo(Taxonomy::class, 'entity_type', 'name')
            ->where('group', 'system')->where('type', 'model');
    }

    protected function getAbilititesForTree()
    {
        $groups = Ability::with('model')->where('entity_type', '<>', '*')->get()->groupBy('entity_type');

        // $permissions = [];

        // foreach ($groups as $entity_type => $abilities) {

        //     $children = [];

        //     foreach ($abilities as $key => $ability) {
        //         $children[] = [
        //             // 'id' => $ability->name,
        //             'id' => $ability->id,
        //             'label' => $ability->title,
        //             'icon' => $ability->icon ?? 'folder_open'
        //         ];
        //     }

        //     $parent = [
        //         'id' => $entity_type,
        //         'label' => 'Módulo ' . ($ability->model->short_name ?? 'X'),
        //         'avatar' => '',
        //         'children' => $children,
        //     ];

        //     $permissions[] = $parent;
        // }

        // // $data[] = ['id' => 'All', 'label' => 'Seleccionar módulos', 'children' => $permissions];
        // $data = $permissions;

        return $groups;
    }
}
