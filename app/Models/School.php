<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class School extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'imagen', 'plantilla_diploma',
        'position', 'scheduled_restarts', 'active'
    ];

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    protected static function search($request)
    {
        // $courses = Course::whereHas('workspace', function ($t) use ($request) {
        //     $t->where('workspace_id', $request->workspace_id);
        // })->get('id');

        $workspace = get_current_workspace();

        $escuelas = School::whereRelation('workspaces', 'workspace_id', $workspace->id)
            // whereHas('courses', function ($j) use ($courses) {
            //     $j->whereIn('course_id', $courses->pluck('id'));
            // })->
            ->withCount(['courses' => function ($c) use ($workspace) {
                $c->whereRelation('workspaces', 'id', $workspace->id);
            }]);

        if ($request->q)
            $escuelas->where('name', 'like', "%$request->q%");

        if ($request->active == 1)
            $escuelas->where('active', ACTIVE);

        if ($request->active == 2)
            $escuelas->where('active', '<>', ACTIVE);

        // if (!is_null($request->sortBy)) {
        //     $field = $request->sortBy ?? 'created_at';
        //     $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        //     $escuelas->orderBy($field, $sort);
        // } else {
        //     $escuelas->orderBy('created_at', 'DESC');
        // }

        $field = $request->sortBy == 'orden' ? 'position' : $request->sortBy;

        $field = $field ?? 'position';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $escuelas->orderBy($field, $sort);

        return $escuelas->paginate($request->paginate);
    }

    protected function storeRequest($data, $school = null)
    {
        try {

            $workspace = get_current_workspace();

            DB::beginTransaction();

            if ($school) :
                $school->update($data);
            else :
                $school = self::create($data);

                $workspace->schools()->attach($school);
            endif;

            // Generate code when is not defined

            if (!$school->code) {
                $school->code = 'S' . str_pad($school->id, 2, '0', STR_PAD_LEFT);
                $school->save();
            }

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            info($e);

            return $e;
        }

        cache_clear_model(Course::class);

        return $school;
    }
}
