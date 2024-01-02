<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table = 'tags';
    public $timestamps = false;

    protected $fillable = [
        'model_type', 'model_id', 'tag_id'
    ];
    
    public function model()
    {
        return $this->morphTo();
    }
    public function taxonomy()
    {
        return $this->belongsTo(taxonomy::class,'tag_id','id');
    }
    // protected $fillable = [
    //     'nombre', 'color'
    // ];

    // public function relationships()
    // {
    //     return $this->hasMany(Posteo::class, 'element_id');
    // }

    // protected function search($request)
    // {
    //     $query = self::query();

    //     if ($request->q)
    //         $query->where('nombre', 'like', "%$request->q%");


    //     $field = $request->sortBy ?? 'created_at';
    //     $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

    //     $query->orderBy($field, $sort);

    //     return $query->paginate($request->paginate);
    // }
    // protected function list(){

    // }
    public function storeRequest($data){
        dd($data);
    }
}
