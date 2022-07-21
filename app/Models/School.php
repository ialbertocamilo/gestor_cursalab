<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class School extends Model
{
    protected $fillable = [
        'name', 'description', 'imagen', 'plantilla_diploma',
        'position', 'scheduled_restarts', 'active'
    ];

    public function workspace()
    {
        return $this->belongsToMany(Workspace::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    protected static function search($request)
    {
        $query = self::withCount(['cursos']);

        if ($request->q)
            $query->where('nombre', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'orden';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    protected static function storeRequest($data, $categoria = null)
    {
        try {

            DB::beginTransaction();

            if ($categoria) :
                $categoria->update($data);
            else :
                $categoria = self::create($data);
            endif;

            if (!empty($data['file_imagen'])) :
                $path = Media::uploadFile($data['file_imagen']);
                $categoria->imagen = $path;
            endif;

            if (!empty($data['file_plantilla_diploma'])) :
                $path = Media::uploadFile($data['file_plantilla_diploma']);
                $categoria->plantilla_diploma = $path;
            endif;

            if (!empty($data['nombre_ciclo_0'])) : (new Categoria())->guardarNombreCiclo0($categoria->id, $data['nombre_ciclo_0']);
            endif;


            $categoria->save();
            DB::commit();
            return $categoria;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }
}
