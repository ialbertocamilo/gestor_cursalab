<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stage extends BaseModel
{
    protected $fillable = [
        'process_id',
        'school_id',
        'title',
        'duration',
        'position',
        'active',
        'certificate_template_id',
        'qualification_percentage',
        'qualification_equivalent'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }

    public function process()
    {
        return $this->belongsTo(Process::class, 'process_id');
    }

    public function school()
    {
        return $this->belongsTo(Course::class, 'school_id');
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'stage_id', 'id');
    }

    protected function getStagesList( $data )
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $workspace = get_current_workspace();

        $stages_query = Stage::with(['activities.type'])->where('process_id', $data['process_id']);

        $field = request()->sortBy ?? 'created_at';
        $sort = !is_null(request()->sortDesc) ? (request()->sortDesc == 'true' ? 'DESC' : 'ASC') : 'ASC';

        $stages_query->orderBy($field, $sort);

        if (!is_null($filtro) && !empty($filtro)) {
            $stages_query->where(function ($query) use ($filtro) {
                $query->where('title', 'like', "%$filtro%");
            });
        }
        if(request()->all_data){
            $response['data'] = $stages_query->get();
            return $response;
        }
        $stages = $stages_query->paginate(request('paginate', 15));

        $stages_items = $stages->items();
        foreach($stages_items as $item) {
            $certificate_route = ($item->certificate_template_id) ?
                                        route('stage.diploma.edit', [$item->process_id, $item->id, $item->certificate_template_id]) :
                                        route('stage.diploma.create', [$item->process_id, $item->id]);
            $item->certificate_route = $certificate_route;
        }

        $response['data'] = $stages->items();
        $response['lastPage'] = $stages->lastPage();
        $response['current_page'] = $stages->currentPage();
        $response['first_page_url'] = $stages->url(1);
        $response['from'] = $stages->firstItem();
        $response['last_page'] = $stages->lastPage();
        $response['last_page_url'] = $stages->url($stages->lastPage());
        $response['next_page_url'] = $stages->nextPageUrl();
        $response['path'] = $stages->getOptions()['path'];
        $response['per_page'] = $stages->perPage();
        $response['prev_page_url'] = $stages->previousPageUrl();
        $response['to'] = $stages->lastItem();
        $response['total'] = $stages->total();

        return $response;
    }
}
