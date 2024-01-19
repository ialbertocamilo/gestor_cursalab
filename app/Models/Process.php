<?php

namespace App\Models;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Process extends BaseModel
{
    protected $table = 'processes';

    protected $fillable = [
        'workspace_id',
        'title',
        'description',
        'count_absences',
        'limit_absences',
        'absences',
        'active',
        'logo',
        'background_web',
        'background_mobile',
        'color',
        'icon_finished',
        'config_completed',
        'certificate_template_id',
        'starts_at',
        'finishes_at'
    ];

    protected $casts = [
        'active' => 'boolean',
        'count_absences' => 'boolean',
        'limit_absences' => 'boolean',
        'config_completed' => 'boolean',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function instructions()
    {
        return $this->hasMany(Instruction::class, 'process_id', 'id');
    }

    public function stages()
    {
        return $this->hasMany(Stage::class, 'process_id', 'id');
    }

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }


    protected function getProcessesList( $data )
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $workspace = get_current_workspace();

        $processes_query = Process::with(['instructions', 'segments', 'stages'])->where('workspace_id', $workspace->id);

        $field = request()->sortBy ?? 'created_at';
        $sort = !is_null(request()->sortDesc) ? (request()->sortDesc == 'true' ? 'DESC' : 'ASC') : 'DESC';

        $processes_query->orderBy($field, $sort);

        if (!is_null($filtro) && !empty($filtro)) {
            $processes_query->where(function ($query) use ($filtro) {
                $query->where('title', 'like', "%$filtro%");
                $query->orWhere('description', 'like', "%$filtro%");
            });
        }
        if(request()->all_data){
            $response['data'] = $processes_query->get();
            return $response;
        }
        $processes = $processes_query->paginate(request('paginate', 15));

        $processes_items = $processes->items();
        foreach($processes_items as $item) {
            $item->title_process = $item->title;

            $stages_route = route('stages.index', [$item->id]);
            $certificate_route = ($item->certificate_template_id) ?
                                        route('process.diploma.edit', [$item->id, $item->certificate_template_id]) :
                                        route('process.diploma.create', [$item->id]);

            $item->stages_route = $stages_route;
            $item->certificate_route = $certificate_route;
            $item->assigned_users = $item->segments->count();
            if($item->segments->count())
                $item->progress_process = 0;
            else
                $item->progress_process = null;
            $item->stages_count = $item->stages->count();

            $item->starts_at = date('Y-m-d', strtotime($item->starts_at));
            $item->finishes_at = date('Y-m-d', strtotime($item->finishes_at));
        }

        $response['data'] = $processes->items();
        $response['lastPage'] = $processes->lastPage();
        $response['current_page'] = $processes->currentPage();
        $response['first_page_url'] = $processes->url(1);
        $response['from'] = $processes->firstItem();
        $response['last_page'] = $processes->lastPage();
        $response['last_page_url'] = $processes->url($processes->lastPage());
        $response['next_page_url'] = $processes->nextPageUrl();
        $response['path'] = $processes->getOptions()['path'];
        $response['per_page'] = $processes->perPage();
        $response['prev_page_url'] = $processes->previousPageUrl();
        $response['to'] = $processes->lastItem();
        $response['total'] = $processes->total();

        return $response;
    }
    protected function storeRequest($data, $process = null)
    {
        try {
            $workspace = get_current_workspace();
            $data['workspace_id'] = $workspace?->id;

            DB::beginTransaction();


            if ($process) :
                $data['config_completed'] = true;
                $process->update($data);
            else:
                $process = self::create($data);
            endif;


            //instructions
            if($data['instructions'])
            {
                $instructions = json_decode($data['instructions']);
                if(is_array($instructions))
                {
                    foreach ($instructions as $key => $instruction) {
                        Instruction::updateOrCreate(
                            ['id' => str_contains($instruction->id, 'n-') ? null : $instruction->id],
                            [
                                'description' => $instruction->description,
                                'process_id' => $process->id,
                                'position' => $key + 1,
                            ]
                        );
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            info($e);
            DB::rollBack();
            // Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }

        cache_clear_model(Process::class);

        return $process;
    }

    // Api
    protected function getProcessesApi( $data )
    {
        $user = auth()->user();
        $workspace_id = $user?->subworkspace?->parent?->id;

        $response['data'] = null;
        $user_id = $data['user'];

        $processes_query = Process::with(['instructions','stages.activities.type'])->where('workspace_id', $workspace_id);

        $field = 'created_at';
        $sort = 'DESC';

        $processes_query->orderBy($field, $sort);

        $processes = $processes_query->paginate(10);

        $processes_items = $processes->items();
        foreach($processes_items as $item) {
            if($item->stages){
                $i = 0;
                foreach ($item->stages as $stage) {
                    if($i == 0)
                        $stage->status = 'pendiente';
                    else
                        $stage->status = 'bloqueado';
                    if($stage->activities) {
                        foreach ($stage->activities as $activity) {
                            $activity->status = 'pendiente';
                        }
                    }
                    $i++;
                }
            }
        }

        $response['data'] = $processes->items();
        $response['lastPage'] = $processes->lastPage();
        $response['current_page'] = $processes->currentPage();
        $response['first_page_url'] = $processes->url(1);
        $response['from'] = $processes->firstItem();
        $response['last_page'] = $processes->lastPage();
        $response['last_page_url'] = $processes->url($processes->lastPage());
        $response['next_page_url'] = $processes->nextPageUrl();
        $response['path'] = $processes->getOptions()['path'];
        $response['per_page'] = $processes->perPage();
        $response['prev_page_url'] = $processes->previousPageUrl();
        $response['to'] = $processes->lastItem();
        $response['total'] = $processes->total();

        return $response;
    }

    protected function getProcessApi( $data )
    {
        $response['data'] = null;
        $user_id = $data['user'];
        $process_id = $data['process'];

        $process = Process::with(['instructions','stages.activities.type','stages.activities.requirement'])
                    ->where('id', $process_id)
                    ->first();
        if($process)
        {
            foreach ($process->stages as $stage) {
                $stage->status = 'pendiente';
                foreach ($stage->activities as $activity) {
                    $activity->status = 'pendiente';
                }
            }
        }

        return ['data'=> $process];
    }

}
