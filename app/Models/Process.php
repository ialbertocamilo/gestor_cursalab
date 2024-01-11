<?php

namespace App\Models;

use App\Models\BaseModel;
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
        'background_web',
        'color',
        'icon_finished',
    ];

    protected $casts = [
        'active' => 'boolean',
        'count_absences' => 'boolean',
        'limit_absences' => 'boolean',
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


    protected function getProcessesList( $data )
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        $workspace = get_current_workspace();

        $processes_query = Process::with(['instructions'])->where('workspace_id', $workspace->id);

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
            // $item->instructions = [];
            // $item->benefit_speaker = $item->speaker?->name ?? null;
            // $item->benefit_type = $item->type?->name ?? null;
            // $item->benefit_stars = null;

            $stages_route = route('stages.index', [$item->id]);
            $item->stages_route = $stages_route;
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
                $process->update($data);

            else:
                $process = self::create($data);

            endif;

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
