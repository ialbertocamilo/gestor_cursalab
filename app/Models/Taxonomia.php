<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taxonomia extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'grupo', 'tipo', 'nombre', 'estado', 'parent_taxonomia_id', 'code'
    ];

    protected $hidden = [
         'grupo', 'tipo', 'estado',

        'created_at', 'updated_at', 'deleted_at', 'pivot'
    ];

    public function scopeCategoriaVideoteca($q)
    {
        return $q->where('grupo', 'videoteca')
                  ->where('tipo', 'categoria');
    }
    public function scopeCategoriaVademecum($q)
    {
        return $q->where('grupo', 'vademecum')
                  ->where('tipo', 'categoria');
    }
    public function scopeSubcategoriaVademecum($q, $parent_taxonomia_id)
    {
        return $q->where('grupo', 'vademecum')
                 ->where('tipo', 'subcategoria')
                 ->where('parent_taxonomia_id', $parent_taxonomia_id);
    }

    public function scopeGroup($q, $group)
    {
        return $q->where('grupo', $group);
    }

    public function scopeType($q, $type)
    {
        return $q->where('tipo', $type);
    }

    public function scopeCode($q, $code)
    {
        return $q->where('code', $code);
    }

    public function scopeVideotecaTags($q)
    {
        return $q->where('grupo', 'videoteca')->where('tipo', 'tag');
    }

    public function child()
    {
        return $this->hasMany(Taxonomia::class, 'parent_taxonomia_id');
    }

    public function parent()
    {
        return $this->belongsTo(Taxonomia::class, 'parent_taxonomia_id');
    }

    public function vademecums()
    {
        return $this->hasMany(Vademecum::class, 'category_id');
    }

    protected function getDataForSelect($group, $type)
    {
        $taxonomias = Taxonomia::where('tipo', $type)
                                ->where('grupo', $group)
                                ->where('estado', 1)
                                ->orderBy('nombre', 'ASC')
                                ->get(['nombre', 'id']);
                                // ->pluck('nombre', 'id')
                                // ->toArray();

        return $taxonomias;
    }

    protected function getOrCreate($group, $type, $name)
    {
        $name = trim($name);

        $taxonomia = Taxonomia::where('grupo', $group)->where('tipo', $type)->where('nombre', $name)->first();

        if ( ! $taxonomia ) :

            $data = [
                'grupo' => $group,
                'tipo' => $type,
                'nombre' => $name,
                'estado' => 1,
            ];

            $taxonomia = Taxonomia::create($data);

        endif;

        return $taxonomia;
    }

    protected function getDataByGroupAndType($group, $type)
    {
        $taxonomias = Taxonomia::where('tipo', $type)
                                ->where('grupo', $group)
                                ->where('estado', 1)
                                ->orderBy('nombre', 'ASC')
                                ->get();

        return $taxonomias;
    }

}
