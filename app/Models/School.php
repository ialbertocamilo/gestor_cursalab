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
        $query = self::join('school_workspace', 'school_workspace.school_id', '=', 'schools.id')->withCount(['courses']);

        if ($request->workspace_id)
            $query->where('school_workspace.workspace_id', $request->workspace_id);

        if ($request->q)
            $query->where('schools.name', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'schools.position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    protected static function storeRequest($data, $escuela = null)
    {
        try {

            DB::beginTransaction();

            if ($escuela) :
                $escuela->update($data);
            else :
                $escuela = self::create($data);
            endif;

            if (!empty($data['file_imagen'])) :
                $path = Media::uploadFile($data['file_imagen']);
                $escuela->imagen = $path;
            endif;

            if (!empty($data['file_plantilla_diploma'])) :
                $path = Media::uploadFile($data['file_plantilla_diploma']);
                $escuela->plantilla_diploma = $path;
            endif;

            // if (!empty($data['nombre_ciclo_0'])) : (new Categoria())->guardarNombreCiclo0($escuela->id, $data['nombre_ciclo_0']);
            // endif;


            $escuela->save();
            DB::commit();
            return $escuela;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }
}
