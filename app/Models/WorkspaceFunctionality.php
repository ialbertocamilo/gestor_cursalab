<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspaceFunctionality extends Model
{

    protected $table = 'workspace_functionalities';

    protected $fillable = [
        'workspace_id',
        'functionality_id',
        'active'
    ];

    public $timestamps = false;


    protected $casts = [
        'active' => 'boolean'
    ];

    public function functionality()
    {
        return $this->belongsTo(Taxonomy::class, 'functionality_id');
    }

    protected function functionalities( $workspace_id ) {

        $list = collect();

        $functionalities = WorkspaceFunctionality::with(['functionality' => function($c){
                                $c->select('id', 'code', 'name');
                            }])
                            ->where('workspace_id', $workspace_id)
                            ->get();

        $functionalities->each(function($q) use($list) {
            $list->push($q->functionality?->code);
        });
        return $list->toArray();
    }

    protected function sideMenuApp( $workspace_id ) {

        $new_side_menu = collect();

        $functionalities = WorkspaceFunctionality::where('workspace_id', $workspace_id)->get();

        $functionalities->each(function($func) use($new_side_menu) {

            $side_menu_functionality = FunctionalityConfig::with(['configvalue' => function($c){
                                            $c->select('id', 'name');
                                        }])
                                        ->whereHas('configvalue', function($q){
                                            $q->where('group', 'system');
                                            $q->where('type', 'side_menu');
                                        })
                                        ->where('functionality_id', $func->functionality?->id)
                                        ->get();

            $side_menu_functionality->each(function($t) use ($new_side_menu){
                $new_side_menu->push($t->configvalue);
            });

        });

        $new_side_menu->each(function ($item) {
            $item->active = false;
        });

        return $new_side_menu;
    }
}
