<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class School extends BaseModel
{
    protected $fillable = [
        'name', 'description', 'imagen', 'plantilla_diploma',
         'scheduled_restarts', 'active'
    ];

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1');
    }

    public function workspaces()
    {
        return $this->belongsToMany(Workspace::class);
    }

    public function subworkspaces()
    {
        return $this->belongsToMany(Workspace::class, 'school_subworkspace', 'school_id', 'subworkspace_id');
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

        $modules_id = $request->modules ?? $workspace->subworkspaces->pluck('id')->toArray();

        $escuelas = School::
        // addSelect('DISTINCT(ss.school_id)')
            // whereRelation('workspaces', 'workspace_id', $workspace->id)
            
            // ->whereIn('ss.subworkspace_id',$modules_id)
            // with(['subworkspaces',function($q){
            //     $q->select('subworkspace_id','school_id','orden');
            // }])
            when($request->canChangePosition ?? null, function ($q) use ($modules_id) {
                $q->join('school_subworkspace as ss','ss.school_id','schools.id')->where('ss.subworkspace_id',$modules_id[0]);
            })
            ->whereHas('subworkspaces', function ($j) use ($modules_id) {
                $j->whereIn('subworkspace_id', $modules_id);
            })
            ->withCount(['courses']);
            // ->withCount(['courses' => function ($c) use ($workspace) {
            //     $c->whereRelation('workspaces', 'id', $workspace->id);
            // }]);

        if ($request->q)
            $escuelas->where('name', 'like', "%$request->q%");

        if ($request->active == 1)
            $escuelas->where('active', ACTIVE);

        if ($request->active == 2)
            $escuelas->where('active', '<>', ACTIVE);

        if ($request->dates) {

            if (isset($request->dates[0]))
                $escuelas->whereDate('created_at', '>=', $request->dates[0]);

            if (isset($request->dates[1]))
                $escuelas->whereDate('created_at', '<=', $request->dates[1]);
        }
        if(!$request->canChangePosition){
            if (!is_null($request->sortBy)) {
                $field = $request->sortBy ?? 'created_at';
                $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
                $escuelas->orderBy($field, $sort);
            } else {
                $escuelas->orderBy('created_at', 'DESC');
            }
        }else{
            $escuelas->addSelect('ss.position as school_position')->orderBy('school_position', 'ASC')->groupBy('schools.id');
        }


        // $field = $request->sortBy == 'orden' ? 'position' : $request->sortBy;
        // $field = $field ?? 'position';
        // $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        // dd($escuelas->paginate($request->paginate)->pluck('id'));
        // $escuelas->orderBy($field, $sort);
        return $escuelas->paginate($request->paginate);
    }

    protected function storeRequest($data, $school = null)
    {
        try {

            DB::beginTransaction();

            if ($school) :
                $school->update($data);
            else :
                $school = self::create($data);
                foreach ($data['subworkspaces'] as  $subworkspace) {
                    SortingModel::setLastPositionInPivotTable(SchoolSubworkspace::class,School::class,[
                        'subworkspace_id'=>$subworkspace,
                        'school_id' => $school->id
                    ],[
                        'subworkspace_id'=>$subworkspace,
                    ]);
                }
            endif;
            
            $school->subworkspaces()->sync($data['subworkspaces'] ?? []);

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

    public function setSelectSuffixes($active_suffix = true, $subworkspaces_suffix = true)
    {
        if ($subworkspaces_suffix) {

            $subworkspaces = implode(', ', $this->subworkspaces->pluck('codigo_matricula')->toArray());
            
            $this->name .= " [{$subworkspaces}]";
        }

        if ($active_suffix) {

            $active = !$this->active ? " [Inactivo]" : "";
            
            $this->name .= "{$active}";
        }
    }
}
