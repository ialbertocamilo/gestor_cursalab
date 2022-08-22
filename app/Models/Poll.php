<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Poll extends BaseModel
{
    protected $table = 'polls';

    protected $fillable = [
        'type_id', 'anonima', 'titulo', 'imagen', 'active', 'workspace_id'
    ];

    /*

        Relationships

    --------------------------------------------------------------------------*/


    public function questions()
    {
        return $this->hasMany(PollQuestion::class, 'poll_id');
    }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function course()
    {
        return $this->belongsToMany(Course::class);
    }

    /*

        Methods

    --------------------------------------------------------------------------*/

    /**
     * Searches a record according a criteria
     *
     * @param $request
     * @return mixed
     */
    protected function search($request)
    {
        $session = $request->session()->all();
        $workspace = $session['workspace'];

        $query = self::withCount('questions')
                     ->where('workspace_id', $workspace->id);

        if ($request->q)
            $query->where('titulo', 'like', "%$request->q%");

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

    /**
     * Calculates the count of related courses
     *
     * @return int
     */
    public function countCoursesRelated()
    {
        return DB::table('course_poll')
            ->where('poll_id', $this->id)
            ->count();
    }

    /**
     * Load active course polls
     *
     * @return void
     */
    protected function loadCoursePolls()
    {

        $taxonomy = Taxonomy::getFirstData(
            'poll',
            'tipo',
            'xcurso'
        );
        if ($taxonomy) {

            $polls = Poll::where('active', 1)
                ->where('type_id', $taxonomy->id)
                ->get();

            return $polls;
        } else {
            return [];
        }
    }
}
