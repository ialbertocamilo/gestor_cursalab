<?php

namespace App\Models;

use App\Services\FileService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speaker extends BaseModel
{
    protected $table = 'speakers';

    protected $fillable = [
        'workspace_id',
        'name',
        'biography',
        'email',
        'specialty',
        'image',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function benefits()
    {
        return $this->hasMany(Benefit::class, 'speaker_id', 'id');
    }

    public function experiences()
    {
        return $this->hasMany(SpeakerExperience::class, 'speaker_id', 'id');
    }

    protected function storeRequest($data, $speaker = null)
    {
        try {
            $workspace = get_current_workspace();

            DB::beginTransaction();


            if ($speaker) :

                $speaker->update($data);

            else:

                $speaker = self::create($data);
            endif;


            DB::commit();
        } catch (\Exception $e) {
            info($e);
            DB::rollBack();
            // Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }

        cache_clear_model(Speaker::class);

        return $speaker;
    }

    protected function getSpeakersList( $data )
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        // $workspace = get_current_workspace();

        $speakers_query = Speaker::
        where('active',1);
        // where('workspace_id', $workspace->id)

        $field = request()->sortBy ?? 'created_at';
        $sort = request()->sortDesc == 'true' ? 'DESC' : 'ASC';

        $speakers_query->orderBy($field, $sort);

        if (!is_null($filtro) && !empty($filtro)) {
            $speakers_query->where(function ($query) use ($filtro) {
                $query->where('speakers.name', 'like', "%$filtro%");
            });
        }
        $speakers = $speakers_query->paginate(request('paginate', 15));

        $speakers_items = $speakers->items();
        foreach($speakers_items as $item) {
            $item->perfil_speaker = $item->image ? FileService::generateUrl($item->image) : $item->image;
            $route_edit = route('speaker.editSpeaker', [$item->id]);
            $item->edit_route = $route_edit;
        }

        $response['data'] = $speakers->items();
        $response['lastPage'] = $speakers->lastPage();
        $response['current_page'] = $speakers->currentPage();
        $response['first_page_url'] = $speakers->url(1);
        $response['from'] = $speakers->firstItem();
        $response['last_page'] = $speakers->lastPage();
        $response['last_page_url'] = $speakers->url($speakers->lastPage());
        $response['next_page_url'] = $speakers->nextPageUrl();
        $response['path'] = $speakers->getOptions()['path'];
        $response['per_page'] = $speakers->perPage();
        $response['prev_page_url'] = $speakers->previousPageUrl();
        $response['to'] = $speakers->lastItem();
        $response['total'] = $speakers->total();

        return $response;
    }

    protected function getData( $speaker )
    {
        // $workspace = get_current_workspace();

        $speaker = Speaker::with(['experiences'])
                    ->where('active',1)
                    ->where('id', $speaker?->id)
                    ->first();
                    // where('workspace_id', $workspace->id)

        return ['data'=>$speaker];
    }

    protected function getInfo( $data )
    {
        $response['data'] = null;
        $speaker_id = $data['speaker'];

        // $workspace = get_current_workspace();

        $speaker = Speaker::with(
            ['experiences','benefits'
        ])
        ->where('active',1)
        ->where('id', $speaker_id->id)
        ->first();
        // where('workspace_id', $workspace->id)

        return ['data'=>$speaker];
    }

    protected function getSpeakers()
    {
        $workspace = get_current_workspace();
        $speakers_items = Speaker::where('active',1)
                        // ->where('workspace_id', $workspace->id)
                        ->orderBy('name', 'DESC')
                        ->get();
        foreach($speakers_items as $speaker) {
            $speaker->image = $speaker->image ? FileService::generateUrl($speaker->image) : $speaker->image;
        }

        $response['data'] = $speakers_items;

        return $response;
    }
}
