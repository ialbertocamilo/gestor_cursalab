<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Benefit extends BaseModel
{
    protected $table = 'benefits';

    protected $fillable = [
        'workspace_id',
        'type_id',
        'speaker_id',
        'name',
        'description',
        'image',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];


    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function properties()
    {
        return $this->hasMany(BenefitProperty::class, 'benefit_id', 'id');
    }

    public function speaker()
    {
        return $this->belongsTo(Speaker::class, 'speaker_id', 'id');
    }

    public function implements()
    {
        $implements_id = Taxonomy::where('group', 'benefit')
                            ->where('type', 'benefit_property')
                            ->where('code', 'implements')
                            ->first();
        return $this->properties()->where('type_id', $implements_id->id);
    }

    public function silabo()
    {
        $silabo_id = Taxonomy::where('group', 'benefit')
                            ->where('type', 'benefit_property')
                            ->where('code', 'silabo')
                            ->first();
        return $this->properties()->where('type_id', $silabo_id->id);
    }

    public function polls()
    {
        $polls_id = Taxonomy::where('group', 'benefit')
                            ->where('type', 'benefit_property')
                            ->where('code', 'polls')
                            ->first();
        return $this->properties()->where('type_id', $polls_id->id);
    }

    public function links()
    {
        $links_id = Taxonomy::where('group', 'benefit')
                            ->where('type', 'benefit_property')
                            ->where('code', 'links')
                            ->first();
        return $this->properties()->where('type_id', $links_id->id);
    }

    protected function getBenefits( $data )
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        // $workspace = get_current_workspace();

        $benefits_query = Benefit::with(
            ['implements','silabo','polls','links','speaker',
            'type'=> function ($query) {
                        $query->select('id', 'name', 'code');
                    }
        ])
        ->where('active',1);
        // where('workspace_id', $workspace->id)

        $field = request()->sortBy ?? 'created_at';
        $sort = request()->sortDesc == 'true' ? 'DESC' : 'ASC';

        $benefits_query->orderBy($field, $sort);

        if (!is_null($filtro) && !empty($filtro)) {
            $benefits_query->where(function ($query) use ($filtro) {
                $query->where('benefits.title', 'like', "%$filtro%");
                $query->orWhere('benefits.description', 'like', "%$filtro%");
            });
        }
        $benefits = $benefits_query->paginate(request('paginate', 15));





        // $speaker = $benefits->speaker()->get();
        // $implements = $benefits->implements()->get();
        // dd($benefits, $speaker, $implements);

        $response['data'] = $benefits->items();
        $response['lastPage'] = $benefits->lastPage();
        $response['current_page'] = $benefits->currentPage();
        $response['first_page_url'] = $benefits->url(1);
        $response['from'] = $benefits->firstItem();
        $response['last_page'] = $benefits->lastPage();
        $response['last_page_url'] = $benefits->url($benefits->lastPage());
        $response['next_page_url'] = $benefits->nextPageUrl();
        $response['path'] = $benefits->getOptions()['path'];
        $response['per_page'] = $benefits->perPage();
        $response['prev_page_url'] = $benefits->previousPageUrl();
        $response['to'] = $benefits->lastItem();
        $response['total'] = $benefits->total();

        return $response;
    }

    protected function getInfo( $data )
    {
        $response['data'] = null;
        $benefit_id = $data['benefit'];

        // $workspace = get_current_workspace();

        $benefit = Benefit::with(
            ['implements','silabo','polls','links','speaker',
            'type'=> function ($query) {
                        $query->select('id', 'name', 'code');
                    }
        ])
        ->where('active',1)
        ->where('id', $benefit_id->id)
        ->first();
        // where('workspace_id', $workspace->id)

        return ['data'=>$benefit];
    }
}
