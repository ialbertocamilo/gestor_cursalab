<?php

namespace App\Models;

use App\Services\FileService;
use Carbon\Carbon;
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
        'status_id',
        'poll_id',

        'title',
        'description',
        'image',
        'cupos',
        'inicio_inscripcion',
        'fin_inscripcion',
        'fecha_liberacion',
        'correo',
        'duracion',
        'promotor',
        'promotor_imagen',
        'direccion',
        'referencia',
        'accesible',

        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'accesible' => 'boolean',
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

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_benefits', 'benefit_id', 'user_id');
    }

    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }

    protected function storeRequest($data, $benefit = null)
    {
        $list_silabos = (!is_null($data['list_silabos'])) ? json_decode($data['list_silabos']) : null;
        $speaker = (!is_null($data['speaker'])) ? json_decode($data['speaker']) : null;

        $property_silabo = Taxonomy::getFirstData('benefit', 'benefit_property', 'silabo');
        $property_links = Taxonomy::getFirstData('benefit', 'benefit_property', 'links');
        $property_polls = Taxonomy::getFirstData('benefit', 'benefit_property', 'polls');
        $property_implements = Taxonomy::getFirstData('benefit', 'benefit_property', 'implements');

        $benefit_type = Taxonomy::getFirstData('benefit', 'benefit_type', $data['type']);

        $data['type_id'] = !is_null($benefit_type) ? $benefit_type->id : null;
        $data['speaker_id'] = !is_null($speaker) ? $speaker->id : null;

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

            if(!is_null($list_silabos)) {
                foreach ($list_silabos as $key => $silabo) {
                    BenefitProperty::updateOrCreate(
                        ['id' => str_contains($silabo->id, 'n-') ? null : $silabo->id],
                        [
                            'name' => $silabo->name,
                            'value' => $silabo->value,
                            'value_date' => $silabo->value_date ? Carbon::parse($silabo->value_date)->format('Y-m-d') : null,
                            'value_time' => $silabo->value_time ? Carbon::parse($silabo->value_time)->format('H:m:i') : null,
                            'value' => $silabo->value,
                            'active' => $silabo->active,
                            'benefit_id' => $benefit->id,
                            'type_id' => $property_silabo->id,
                            'position' => $key + 1,
                        ]
                    );
                }
            }


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
            DB::rollBack();
            // Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }

        cache_clear_model(Benefit::class);
        cache_clear_model(BenefitProperty::class);

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
                ]);
        // ->where('active',1);
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

            $route_edit = route('benefit.editBenefit', [$item->id]);
            $item->edit_route = $route_edit;
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

    protected function getData( $benefit_id )
    {
        $response['data'] = null;

        // $workspace = get_current_workspace();

        $benefit = Benefit::with(
            ['implements','silabo','polls','links','speaker',
            'type'=> function ($query) {
                        $query->select('id', 'name', 'code');
                    }
        ])
        ->where('id', $benefit_id->id)
        ->first();
        // where('workspace_id', $workspace->id)

        if( !is_null($benefit) ) {
            $benefit->inicio_inscripcion = Carbon::parse($benefit->inicio_inscripcion)->format('Y-m-d');
            $benefit->fin_inscripcion = Carbon::parse($benefit->fin_inscripcion)->format('Y-m-d');
            $benefit->fecha_liberacion = Carbon::parse($benefit->fecha_liberacion)->format('Y-m-d');
            if($benefit->speaker) {
                $benefit->speaker->image = $benefit->speaker->image ? FileService::generateUrl($benefit->speaker->image) : $benefit->speaker->image;
            }
        }

        return ['data'=> $benefit];
    }
    // Apis
    protected function registerUserForBenefit( $data )
    {
        $limit_benefits_x_user = 3;
        $response['error'] = false;
        $response['data'] = [];

        $user_id = $data['user'] ?? null;
        $benefit_id = $data['benefit'] ?? null;

        $is_registered = UserBenefit::where('user_id', $user_id)
                        ->where('benefit_id', $benefit_id)
                        ->first();

        if($is_registered) {
            $response['error'] = true;
            $response['msg'] = [
                'title' => 'Ya cuentas con este beneficio',
                'description' => ['Ya te encuentras registrado en este beneficio.']
            ];
        }
        else {

            $benefits_user_registered = UserBenefit::where('user_id',$user_id)->count();
            if($benefits_user_registered < $limit_benefits_x_user) {
                try {
                    DB::beginTransaction();

                    $is_created = UserBenefit::create(['user_id'=>$user_id, 'benefit_id'=>$benefit_id]);
                    cache_clear_model(UserBenefit::class);

                    if($is_created) {
                        $benefit = Benefit::with([
                        'status'=> function ($query) {
                                $query->select('id', 'name', 'code');
                            }
                        ])
                        ->where('id', $benefit_id)
                        ->first();
                        $response['msg'] = [
                            'title' => 'Se ha registrado al beneficio',
                            'description' => ['Te haz inscrito satisfactoriamente al beneficio de <b>'.$benefit->title.'</b>.<br>Recuerda revisar el detalle.']
                        ];
                        $response['data'] = [
                            'benefit_id' => $benefit_id,
                            'user_status' => ['name' => 'Suscrito', 'code' => 'subscribed'],
                            'subscribed' => true,
                            'status' => $benefit->status
                        ];
                    }

                    DB::commit();

                } catch (\Exception $e) {
                    info($e);
                    DB::rollBack();
                    // Error::storeAndNotificateException($e, request());
                    $response['error'] = true;
                    $response['msg'] = [
                        'title' => 'No se pudo registrar a este beneficio',
                        'description' => ['No se pudo registrar a este beneficio. Inténtelo nuevamente.']
                    ];
                    // abort(errorExceptionServer());
                }

            }
            else {
                $response['error'] = true;
                $response['msg'] = [
                    'title' => 'Límite de inscripciones alcanzadas',
                    'description' => [
                        'Has alcanzando el máximo de beneficios inscritos a la vez ('.$limit_benefits_x_user.'), si deseas registrarte debes retirarte de otro beneficio o comunicarte con el coordinador del beneficio.'
                    ]
                ];
            }
        }

        return $response;
    }
    protected function unsubscribeUserForBenefit( $data )
    {
        $response['error'] = false;
        $response['data'] = [];

        $user_id = $data['user'] ?? null;
        $benefit_id = $data['benefit'] ?? null;

        $is_registered = UserBenefit::where('user_id', $user_id)
                        ->where('benefit_id', $benefit_id)
                        ->first();

        if(!$is_registered) {
            $response['error'] = true;
            $response['msg'] = [
                'title' => 'No cuentas con este beneficio',
                'description' => ['No estás registrado en este beneficio.']
            ];
        }
        else {

            try {
                DB::beginTransaction();

                    $is_registered->delete();
                    cache_clear_model(UserBenefit::class);

                    $benefit = Benefit::with([
                    'status'=> function ($query) {
                            $query->select('id', 'name', 'code');
                        }
                    ])
                    ->where('id', $benefit_id)
                    ->first();

                    $response['msg'] = [
                        'title' => 'Te has retirado del beneficio',
                        'description' => ['Ya no te encuentras registrado al beneficio: '. $benefit->title]
                    ];
                    $response['data'] = [
                        'benefit_id' => $benefit_id,
                        'user_status' => ['name' => 'Retirado', 'code' => 'unsubscribe'],
                        'subscribed' => false,
                        'status' => $benefit->status
                    ];

                DB::commit();

            } catch (\Exception $e) {
                info($e);
                DB::rollBack();
                // Error::storeAndNotificateException($e, request());
                $response['error'] = true;
                $response['msg'] = [
                    'title' => 'No se pudo dar de baja a este beneficio',
                    'description' => ['No se pudo dar de baja a este beneficio. Inténtelo nuevamente.']
                ];
                // abort(errorExceptionServer());
            }

        }

        return $response;
    }

    protected function getBenefits( $data )
    {
        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';
        $user_id = $data['user'];
        $status_benefit = ($data['status'] && is_array($data['status']) && count($data['status']) > 0) ? $data['status'] : null;

        // $workspace = get_current_workspace();

        $benefits_user_registered = UserBenefit::where('user_id',$user_id)->pluck('benefit_id')->toArray();

        $benefits_query = Benefit::with([
            'type'=> function ($query) {
                    $query->select('id', 'name', 'code');
                },
            'status'=> function ($query) {
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
        if($status_benefit) {
            if(in_array('subscribed', $status_benefit) && count($status_benefit) == 1) {
                $benefits_query->whereIn('id', $benefits_user_registered);
            }
            else if(in_array('subscribed', $status_benefit) && count($status_benefit) > 1) {
                $benefits_query->whereIn('id', $benefits_user_registered);
                $benefits_query->orWhereHas('status', function ($query) use ($status_benefit) {
                    $query->whereIn('code', $status_benefit);
                });
            }
            else {
                $benefits_query->whereHas('status', function ($query) use ($status_benefit) {
                    $query->whereIn('code', $status_benefit);
                });
            }
        }

        $benefits = $benefits_query->paginate(request('paginate', 15));

        $benefits_items = $benefits->items();

        foreach($benefits_items as $item)
        {
            $item->user_status = null;
            $item->subscribed = false;

            if(in_array($item->id, $benefits_user_registered)) {
                $user_benefit = UserBenefit::where(['user_id' => $user_id, 'benefit_id' => $item->id])->first();
                if($user_benefit) {
                    $item->user_status = ['name' => 'Suscrito', 'code' => 'subscribed'];
                    $item->subscribed = true;
                }
            }

            // if ($item->inicio_inscripcion && $item->fin_inscripcion) {
            //     $inicio_inscripcion = new Carbon($item->inicio_inscripcion);
            //     $fin_inscripcion = new Carbon($item->fin_inscripcion);
            //     $now = Carbon::now();
            //     if ($now->gt($inicio_inscripcion) && $now->lt($fin_inscripcion))
            //         $item->status = ['name' => 'Activo', 'code' => 'activo'];
            // }


            $item->ubicacion = "Lima";
            $item->inicio_inscripcion = Carbon::parse($item->inicio_inscripcion)->format('d/m/Y');
            $item->fin_inscripcion = Carbon::parse($item->fin_inscripcion)->format('d/m/Y');
            $item->fecha_liberacion = Carbon::parse($item->fecha_liberacion)->format('d/m/Y');
            unset(
                $item->promotor,
                $item->promotor_imagen,
                $item->direccion,
                $item->referencia,
                $item->duracion,
                $item->workspace_id,
                $item->type_id,
                $item->speaker_id,
                $item->status_id,
                $item->active
            );
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

    protected function getInfo( $data )
    {
        $response['data'] = null;
        $benefit_id = $data['benefit'];
        $user_id = $data['user'];

        $benefits_user_registered = UserBenefit::where('user_id',$user_id)->pluck('benefit_id')->toArray();

        // $workspace = get_current_workspace();

        $benefit = Benefit::with(
            ['implements','silabo','polls','links','speaker',
            'type'=> function ($query) {
                        $query->select('id', 'name', 'code');
                    },
            'status'=> function ($query) {
                $query->select('id', 'name', 'code');
            }
        ])
        ->where('active',1)
        ->where('id', $benefit_id->id)
        ->first();
        // where('workspace_id', $workspace->id)

        if($benefit) {

            $benefit->user_status = null;
            $benefit->subscribed = false;

            if(in_array($benefit->id, $benefits_user_registered)) {
                $user_benefit = UserBenefit::where(['user_id' => $user_id, 'benefit_id' => $benefit->id])->first();
                if($user_benefit) {
                    $benefit->user_status = ['name' => 'Suscrito', 'code' => 'subscribed'];
                    $benefit->subscribed = true;
                }
            }

            $benefit->direccion = [
                'lugar' => 'Av. Nicolas de Pierola 645 - Surquillo - Lima',
                'link' => 'https://maps.google.com/?q=Av.+Vista+Alegre+3400,+Carabayllo+15121,+Per%C3%BA&ftid=0x9105d6daa1e0bb81:0x35aced63bf7fc7e2',
                'image' => null,
                'referencia' => $benefit->referencia
            ];
            $benefit->inicio_inscripcion = Carbon::parse($benefit->inicio_inscripcion)->format('d/m/Y');
            $benefit->fin_inscripcion = Carbon::parse($benefit->fin_inscripcion)->format('d/m/Y');
            $benefit->fecha_liberacion = Carbon::parse($benefit->fecha_liberacion)->format('d/m/Y');
            unset(
                $benefit->referencia,
                $benefit->workspace_id,
                $benefit->type_id,
                $benefit->speaker_id,
                $benefit->status_id,
                $benefit->poll_id,
                $benefit->active
            );
        }

        return ['data'=>$benefit];
    }
}
