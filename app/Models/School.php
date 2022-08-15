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
    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

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
        $courses = Course::whereHas('workspace', function ($t) use ($request) {
            $t->where('workspace_id', $request->workspace_id);
        })->get('id');

        $escuelas = School::whereHas('courses', function ($j) use ($courses) {
            $j->whereIn('course_id', $courses->pluck('id'));
        })->withCount(['courses' => function ($c) use ($request) {
            $c->whereHas('workspace', function ($r) use ($request) {
                $r->where('workspace_id', $request->workspace_id);
            });
        }]);

        if ($request->q)
            $escuelas->where('schools.name', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'schools.position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $escuelas->orderBy($field, $sort);

        return $escuelas->paginate($request->paginate);
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
