<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Benefit extends BaseModel
{
    protected $table = 'benefits';

    protected $fillable = [
        'workspace_id',
        'type_id',
        'speaker_id',
        'title',
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

    protected function storeRequest($data, $benefit = null)
    {
        try {
            $workspace = get_current_workspace();

            DB::beginTransaction();


            if ($benefit) :

                $benefit->update($data);

            else:

                $benefit = self::create($data);
                // $benefit->workspaces()->sync([$workspace->id]);
                // foreach ($data['escuelas'] as  $escuela) {
                //     SortingModel::setLastPositionInPivotTable(CourseSchool::class,Course::class,[
                //         'school_id' => $escuela,
                //         'course_id'=>$course->id,
                //     ],[
                //         'school_id'=>$escuela,
                //     ]);
                // }
            endif;

            // if ($data['requisito_id']) :
            //     Requirement::updateOrCreate(
            //         ['model_type' => Course::class, 'model_id' => $course->id,],
            //         ['requirement_type' => Course::class, 'requirement_id' => $data['requisito_id']]
            //     );

            // else :

            //     $course->requirements()->delete();

            // endif;


            DB::commit();
        } catch (\Exception $e) {
            info($e);
            dd($e);
            DB::rollBack();
            // Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }

        cache_clear_model(Benefit::class);

        return $benefit;
    }

    protected function getBenefitsList( $data )
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        // $workspace = get_current_workspace();

        $benefits_query = Benefit::with(
            ['speaker',
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

        $benefits_items = $benefits->items();
        foreach($benefits_items as $item) {
            $item->benefit_speaker = $item->speaker?->name ?? null;
            $item->benefit_type = $item->type?->name ?? null;
            $item->benefit_stars = null;
        }

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

    // Apis
    protected function getBenefits( $data )
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';

        // $workspace = get_current_workspace();

        $benefits_query = Benefit::with(
            ['type'=> function ($query) {
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
        $benefits = $benefits_query->select('id','workspace_id','type_id','title','description','image','cupos','inicio_inscripcion','fin_inscripcion','fecha_liberacion','dificultad','active')->paginate(request('paginate', 15));


        $benefits_items = $benefits->items();
        foreach($benefits_items as $item) {
            $item->status = ['name' => 'Suscrito', 'code' => 'suscrito'];
            $item->ubicacion = "Lima";
            $item->accesible = true;
        }



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

        if($benefit) {
            $benefit->direccion = [
                'lugar' => 'Av. Nicolas de Pierola 645 - Surquillo - Lima',
                'link' => 'https://maps.google.com/?q=Av.+Vista+Alegre+3400,+Carabayllo+15121,+Per%C3%BA&ftid=0x9105d6daa1e0bb81:0x35aced63bf7fc7e2',
                'image' => null,
                'referencia' => $benefit->referencia
            ];
            $benefit->status = ['name' => 'Activo', 'code' => 'activo'];
            $benefit->accesible = true;
            unset($benefit->referencia);
        }

        return ['data'=>$benefit];
    }
}
