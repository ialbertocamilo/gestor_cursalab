<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Course extends Model
{
    protected $fillable = [
        'name', 'description', 'imagen', 'plantilla_diploma', 'external_code', 'slug',
        'assessable', 'freely_eligible',
        'position', 'scheduled_restarts', 'active'
    ];


    public function topics()
    {
        return $this->hasMany(Topic::class, 'course_id');
    }

    public function poll()
    {
        return $this->belongsToMany(Poll::class);
    }

    public function requirement()
    {
        return $this->belongsToMany(Course::class);
    }

    public function checklists()
    {
        return $this->belongsToMany(Checklist::class, 'checklist_relationship', 'course_id', 'checklist_id');
    }

    public function update_usuarios()
    {
        return $this->hasMany(Update_usuarios::class, 'curso_id');
    }

    protected function search($request, $paginate = 15)
    {
        $q = self::with([
            'update_usuarios' => function ($q_update) {
                $q_update->whereIn('estado', [0, 1]);
            }
        ])
            ->withCount('topics', 'poll');

        if ($request->schoold_id)
            $q->where('school_id', $request->school_id);

        if ($request->q)
            $q->where('nombre', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'orden';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);
    }

    protected static function storeRequest($data, $curso = null)
    {
        try {

            DB::beginTransaction();

            if ($curso) :
                $curso->update($data);
            else :
                $data['libre'] = $data['categoria_modalidad'] === 'libre' ? 1 : 0;
                $curso = self::create($data);
            endif;

            $curso->save();
            DB::commit();
            return $curso;
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
        }
    }
}
