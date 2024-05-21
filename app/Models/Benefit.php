<?php

namespace App\Models;

use App\Mail\EmailTemplate;
use App\Services\FileService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class Benefit extends BaseModel
{
    protected $table = 'benefits';

    protected $fillable = [
        'workspace_id',
        'type_id',
        'speaker_id',
        'status_id',
        'poll_id',
        'group_id',

        'title',
        'description',
        'image',
        'cupos',
        'inicio_inscripcion',
        'fin_inscripcion',
        'fecha_liberacion',
        'fecha_encuesta',
        'correo',
        'duracion',
        'promotor',
        'promotor_imagen',
        'direccion',
        'referencia',
        'accesible',
        'dificultad',

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

    public function group()
    {
        return $this->belongsTo(Taxonomy::class, 'group_id');
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

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    protected function storeRequest($data, $benefit = null)
    {
        $promotor_imagen_multimedia = (isset($data['promotor_imagen_multimedia']) && !is_null($data['promotor_imagen_multimedia'])) ? $data['promotor_imagen_multimedia'] : null;
        if(!is_null($promotor_imagen_multimedia)){
            $data['promotor_imagen'] = $promotor_imagen_multimedia ?? null;
        }

        $data_maps = (isset($data['ubicacion_mapa']) && !is_null($data['ubicacion_mapa']))
            ? json_decode($data['ubicacion_mapa'])
            : null;

        if($data_maps) {
            $geometry = $data_maps->geometry ?? null;
            $json_maps['location'] = $geometry->location ?? null;
            $json_maps['address'] = $data_maps->formatted_address ?? null;
            $json_maps['url'] = $data_maps->url ?? null;
            $json_maps['image'] = $data_maps->image_map ?? null;
            $json_maps['ubicacion'] = $data_maps->ubicacion ?? null;

            if ($benefit->direccion) {
                if (json_decode($benefit->direccion)->address != $json_maps['address']) {
                    if ($json_maps['address'] != null) {
                        $data['direccion'] = json_encode($json_maps);
                    }
                }

            } else {
                $data['direccion'] = json_encode($json_maps);
            }

        } else {
            $data['direccion'] = null;
        }

        $list_links = (isset($data['list_links']) && !is_null($data['list_links'])) ? json_decode($data['list_links']) : null;
        $list_silabos = (isset($data['list_silabos']) && !is_null($data['list_silabos'])) ? json_decode($data['list_silabos']) : null;
        $lista_implementos = (isset($data['lista_implementos']) && !is_null($data['lista_implementos'])) ? json_decode($data['lista_implementos']) : null;
        $speaker = (!is_null($data['speaker'])) ? json_decode($data['speaker']) : null;

        $property_silabo = Taxonomy::getFirstData('benefit', 'benefit_property', 'silabo');
        $property_links = Taxonomy::getFirstData('benefit', 'benefit_property', 'links');
        $property_polls = Taxonomy::getFirstData('benefit', 'benefit_property', 'polls');
        $property_implements = Taxonomy::getFirstData('benefit', 'benefit_property', 'implements');

        $benefit_type = Taxonomy::getFirstData('benefit', 'benefit_type', $data['type']);

        $group = $data['group'] ?? 'free';
        $benefit_group = Taxonomy::getFirstData('benefit', 'group', $group);

        $data['type_id'] = !is_null($benefit_type) ? $benefit_type->id : null;
        $data['group_id'] = !is_null($benefit_group) ? $benefit_group->id : null;
        $data['speaker_id'] = !is_null($speaker) ? $speaker->id : null;

        $data['cupos'] = ( $data['cupos'] == 'ilimitado') ? null : $data['cupos'];

        $data['poll_id'] = isset($data['poll_id']) ? (int)$data['poll_id'] : null;
        $data['fecha_encuesta'] = $data['fecha_encuesta'] ?? null;

        try {
            $workspace = get_current_workspace();
            $data['workspace_id'] = $workspace?->id;

            DB::beginTransaction();


            if ($benefit) :

                $benefit->update($data);

            else:

                $benefit = self::create($data);

            endif;

            if(!is_null($list_silabos)) {

                if (count($list_silabos) > 0) {

                    $currentSilabos =  BenefitProperty::query()
                        ->where('benefit_id', $benefit->id)
                        ->where('type_id', $property_silabo->id)
                        ->get();

                    foreach ($list_silabos as $key => $silabo) {
                        BenefitProperty::updateOrCreate(
                            ['id' => str_contains($silabo->id, 'n-') ? null : $silabo->id],
                            [
                                'name' => $silabo->name,
                                'value' => $silabo->value,
                                'value_date' => $silabo->value_date ? Carbon::parse($silabo->value_date)->format('Y-m-d') : null,
                                'value_time' => $silabo->value_time ? Carbon::parse($silabo->value_time)->format('H:i:s') : null,
                                'active' => $silabo->active,
                                'benefit_id' => $benefit->id,
                                'type_id' => $property_silabo->id,
                                'position' => $key + 1,
                            ]
                        );

                        // Delete silabos which has been removed
                        // from the list
                        $silabosToDelete = $currentSilabos
                            ->whereNotIn('id', collect($list_silabos)
                            ->pluck('id'));

                        if ($silabosToDelete->pluck('id')->count() > 0 ) {
                            BenefitProperty::query()
                                ->where('benefit_id', $benefit->id)
                                ->where('type_id', $property_silabo->id)
                                ->whereIn('id', $silabosToDelete->pluck('id'))
                                ->delete();
                        }
                    }
                } else {
                    BenefitProperty::query()
                        ->where('benefit_id', $benefit->id)
                        ->where('type_id', $property_silabo->id)
                        ->delete();
                }
            }

            if(!is_null($list_links)) {
                if (count($list_links) > 0) {
                    foreach ($list_links as $key => $link) {
                        BenefitProperty::updateOrCreate(
                            ['id' => str_contains($link->id, 'n-') ? null : $link->id],
                            [
                                'name' => $link->name,
                                'value' => $link->value,
                                'active' => $link->active,
                                'benefit_id' => $benefit->id,
                                'type_id' => $property_links->id,
                                'position' => $key + 1,
                            ]
                        );
                    }
                } else {
                    BenefitProperty::query()
                        ->where('benefit_id', $benefit->id)
                        ->where('type_id', $property_links->id)
                        ->delete();
                }
            }

            if(!is_null($lista_implementos)) {
                if (count($lista_implementos) > 0) {
                    foreach ($lista_implementos as $key => $implemento) {
                        BenefitProperty::updateOrCreate(
                            ['id' => str_contains($implemento->id, 'n-') ? null : $implemento->id],
                            [
                                'name' => $implemento->name,
                                'active' => $implemento->active,
                                'benefit_id' => $benefit->id,
                                'type_id' => $property_implements->id,
                                'position' => $key + 1,
                            ]
                        );
                    }
                } else {
                    BenefitProperty::query()
                        ->where('benefit_id', $benefit->id)
                        ->where('type_id', $property_implements->id)
                        ->delete();
                }
            }

            $this->setStatus($benefit);


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

        $workspace = get_current_workspace();

        $benefits_query = Benefit::with(
            ['speaker',
            'type'=> function ($query) {
                        $query->select('id', 'name', 'code');
                    }
                ])
        ->where('workspace_id', $workspace->id);

        $field = request()->sortBy ?? 'created_at';
        $sort = !is_null(request()->sortDesc) ? (request()->sortDesc == 'true' ? 'DESC' : 'ASC') : 'DESC';

        $benefits_query->orderBy($field, $sort);

        if (!is_null($filtro) && !empty($filtro)) {
            $benefits_query->where(function ($query) use ($filtro) {
                $query->where('benefits.title', 'like', "%$filtro%");
                $query->orWhere('benefits.description', 'like', "%$filtro%");
            });
        }
        if(request()->types){
            $benefits_query->whereHas('type', fn($q) => $q->whereIn('code',request()->types));
        }
        if(request()->all_data){
            $response['data'] = $benefits_query->get();
            return $response;
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
                    },
            'group'=> function ($query) {
                        $query->select('id', 'name', 'code');
                    }
        ])
        ->where('id', $benefit_id->id)
        ->first();
        // where('workspace_id', $workspace->id)

        if( !is_null($benefit) ) {
            $benefit->inicio_inscripcion = Carbon::parse($benefit->inicio_inscripcion)->format('Y-m-d');
            $benefit->fecha_encuesta = Carbon::parse($benefit->fecha_encuesta)->format('Y-m-d');
            $benefit->fin_inscripcion = Carbon::parse($benefit->fin_inscripcion)->format('Y-m-d');
            $benefit->fecha_liberacion = Carbon::parse($benefit->fecha_liberacion)->format('Y-m-d');
            $benefit->direccion = (!is_null($benefit->direccion)) ? json_decode($benefit->direccion) : null;
            if($benefit->speaker) {
                $benefit->speaker->image = $benefit->speaker->image ? FileService::generateUrl($benefit->speaker->image) : $benefit->speaker->image;
            }
        }

        return ['data'=> $benefit];
    }

    protected function getSegments( $benefit_id )
    {
        $benefit = null;

        $workspace = get_current_workspace();

        if(!is_null($benefit_id)) {

            $criteria = Segment::getCriteriaByWorkspace(get_current_workspace());
            $segments = Segment::getSegmentsByModel($criteria, Benefit::class, $benefit_id->id);

            $benefit['segments'] = $segments;

            if (is_array($segments)) {
                $segments = collect($segments);
            }

            $segmentation_by_document_list = [];
            $segmentation_by_document = $segments->map(function ($item) {
                return ['segmentation_by_document'=> $item->segmentation_by_document];
            });

            foreach ($segmentation_by_document as $seg) {
                foreach ($seg['segmentation_by_document'] as $value) {
                    array_push($segmentation_by_document_list, $value);
                }
            }
            $benefit['segmentation_by_document'] = ['segmentation_by_document'=> $segmentation_by_document_list];

        }

        return ['benefit' => $benefit];
    }

    protected function setStatus( $benefit )
    {
        if($benefit) {

            $inicio_inscripcion = new Carbon($benefit->inicio_inscripcion);
            $fin_inscripcion = new Carbon($benefit->fin_inscripcion);
            $fin_inscripcion->setTime(23, 59, 59);
            $fecha_liberacion = new Carbon($benefit->fecha_liberacion);
            $now = Carbon::now();


            $status_active = Taxonomy::getFirstData('benefit', 'status', 'active');
            $status_locked = Taxonomy::getFirstData('benefit', 'status', 'locked');
            $status_finished = Taxonomy::getFirstData('benefit', 'status', 'finished');
            $status_released = Taxonomy::getFirstData('benefit', 'status', 'released');

            if($now->lt($inicio_inscripcion)) {
                $benefit->status_id = $status_locked->id;
            }
            else if ($now->gt($inicio_inscripcion) && $now->lt($fin_inscripcion)) {
                $benefit->status_id = $status_active->id;
            }
            else if($now->gt($fin_inscripcion) && $now->lt($fecha_liberacion)) {
                $benefit->status_id = $status_finished->id;
            }
            else if($now->gt($fecha_liberacion)) {
                $benefit->status_id = $status_released->id;
            }

            $benefit->save();
        }

        cache_clear_model(Benefit::class);

        return $benefit;
    }

    protected function maxBenefitsxUsers()
    {
        $workspace_id = get_current_workspace()?->id;
        $workspace = Workspace::where('id', $workspace_id)->first();

        $response['max_benefits_x_users'] = $workspace?->max_benefits ?? 0;
        return $response;
    }

    protected function updateMaxBenefitsxUsers( $value )
    {
        $response = null;

        if(!is_null($value)) {

            try {
                $workspace_id = get_current_workspace()?->id;

                DB::beginTransaction();

                $workspace = Workspace::where('id', $workspace_id)->first();

                if ($workspace) :

                    $workspace->max_benefits = ($value < 0) ? 0 : $value;
                    $workspace->save();

                    $response = $workspace->max_benefits;

                    cache_clear_model(Workspace::class);

                endif;

                DB::commit();
            } catch (\Exception $e) {
                info($e);
                DB::rollBack();
                // Error::storeAndNotificateException($e, request());
                abort(errorExceptionServer());
            }

            return $response;
        }

        return $response;
    }

    protected function assignedSpeaker( $benefit_id, $speaker_id )
    {
        $response = null;

        if(!is_null($benefit_id) && !is_null($speaker_id)) {

            try {
                DB::beginTransaction();

                $benefit = Benefit::where('id', $benefit_id)->first();

                if ($benefit)
                {
                    $speaker = Speaker::where('id', $speaker_id)->first();

                    if($speaker) {
                        $benefit->speaker_id = $speaker?->id;
                        $benefit->save();
                    }

                    cache_clear_model(Benefit::class);
                }

                DB::commit();
            } catch (\Exception $e) {
                info($e);
                DB::rollBack();
                // Error::storeAndNotificateException($e, request());
                abort(errorExceptionServer());
            }
        }

        return $response;
    }

    protected function getSuscritos($benefit_id = null)
    {
        $segmentados = collect();

        $course = new Course();
        $benefit = Benefit::with(['segments'])->where('id', $benefit_id)->first();

        $users_query = UserBenefit::leftJoin('users', 'users.id','user_benefits.user_id')
                    ->with(
                        ['status' => function($q){
                            $q->select('id', 'name', 'code');
                        }],
                        ['type' => function($q){
                            $q->select('id', 'name', 'code');
                        }]
                    )
                    ->whereHas('status', function($q) {
                        $q->where('type', 'user_status');
                        $q->where(function($t){
                            $t->where('code', 'approved');
                            $t->orWhere('code', 'subscribed');
                        });
                    })
                    ->where('user_benefits.benefit_id', $benefit_id)
                    ->select('users.id','users.surname','users.lastname','users.document','users.name', 'users.fullname','user_benefits.status_id','user_benefits.type_id','user_benefits.id as user_benefits_id');

        $users_ids = $users_query->pluck('id')->toArray();
        $users = $users_query->get();

        if($benefit)
        {
            $segmentados_id = $course->usersSegmented($benefit?->segments, $type = 'users_id');
            $segmentados_id = array_unique($segmentados_id);
            // $segmentados = User::whereIn('id',$segmentados_id)->get();
            $segmentados = User::whereIn('id',$segmentados_id)
                                ->whereNotIn('id', $users_ids)
                                ->select('id','name','surname','lastname','document')
                                ->get();
        }

        // $users_id = UserBenefit::whereHas('status', function($q) {
        //                 // $q->where('id', $user_status_subscribed?->id);
        //                 $q->where('type', 'user_status');
        //                 $q->where('code', 'subscribed');
        //             })
        //             ->where('benefit_id', $benefit_id)
        //             ->pluck('user_id')
        //             ->toArray();

        // $users = User::whereIn('users.id',$users_id)
        //                 ->select('users.id','users.surname','users.lastname','users.document')
        //                 ->get();

        $response['users'] = $users;
        $response['segmentados'] = $segmentados;

        return $response;
    }

    protected function updateSuscritos( $benefit_id = null, $seleccionados = null )
    {
        $response = null;

        if(!is_null($benefit_id) && is_array($seleccionados))
        {
            $user_status_subscribed = Taxonomy::getFirstData('benefit', 'user_status', 'subscribed');
            $user_status_approved = Taxonomy::getFirstData('benefit', 'user_status', 'approved');
            $user_status_removed = Taxonomy::getFirstData('benefit', 'user_status', 'removed');
            $type_register_extraordinario = Taxonomy::getFirstData('benefit', 'type_register', 'extraordinario');

            $seleccionados_ids = array_column($seleccionados, 'user_benefits_id');
            $users_subscribed_in_benefit = UserBenefit::where('benefit_id', $benefit_id)
                                                ->whereNotIn('id', $seleccionados_ids)
                                                ->pluck('id');

            $removed_users = UserBenefit::whereIn('id', $users_subscribed_in_benefit)->get();
            $removed_users->each(function($item) use ($user_status_removed) {
                $item->status_id = $user_status_removed?->id;
                $item->save();
            });

            try {
                DB::beginTransaction();

                foreach($seleccionados as $sel)
                {
                    if(isset($sel['ev_type_register']) && $sel['ev_type_register'] == 'extraordinario'){
                        if(isset($sel['user_benefits_id']) && !is_null($sel['user_benefits_id']))
                        {
                            $ub_id = UserBenefit::where('id', $sel['user_benefits_id'])->first();
                            if(!is_null($ub_id))
                            {
                                $ub_id->type_id = $type_register_extraordinario?->id;
                                $ub_id->status_id = $user_status_subscribed?->id;
                                if(!is_null($ub_id->fecha_registro))
                                    $ub_id->fecha_registro = new Carbon();
                                $ub_id->save();
                            }
                        }
                        else
                        {
                            $ub_id = UserBenefit::where('user_id', $sel['id'])
                                                ->where('benefit_id', $benefit_id)
                                                ->first();
                            if(!is_null($ub_id))
                            {
                                $ub_id->type_id = $type_register_extraordinario?->id;
                                $ub_id->status_id = $user_status_subscribed?->id;
                                if(!is_null($ub_id->fecha_registro))
                                    $ub_id->fecha_registro = new Carbon();
                                $ub_id->save();
                            }
                            else
                            {
                                if(!is_null($sel['id']))
                                {
                                    $created_user = UserBenefit::updateOrCreate([
                                            'user_id' => $sel['id'],
                                            'benefit_id' => $benefit_id
                                        ],[
                                        'user_id' => $sel['id'],
                                        'benefit_id' => $benefit_id,
                                        'status_id' => $user_status_subscribed?->id,
                                        'type_id' => $type_register_extraordinario?->id,
                                        'fecha_registro' => new Carbon()
                                    ]);

                                    if(isset($sel['ev_user_status']) && $sel['ev_user_status'] == 'approved'){
                                        if(isset($sel['user_benefits_id']) && !is_null($sel['user_benefits_id']))
                                        {
                                            if(!is_null($created_user))
                                            {
                                                if($created_user->status_id != $user_status_approved?->id)
                                                {
                                                    $created_user->status_id = $user_status_approved?->id;
                                                    $created_user->fecha_confirmado = new Carbon();
                                                    $created_user->save();

                                                    $mail_user = User::where('id', $created_user?->user_id)->select('id','email')->first();
                                                    $mail_benefit = Benefit::where('id', $created_user?->benefit_id)->select('id','title')->first();
                                                    if(!is_null($mail_user) && !is_null($mail_benefit)) {
                                                        Benefit::sendEmail( 'confirm', $mail_user, $mail_benefit );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if(isset($sel['ev_user_status']) && $sel['ev_user_status'] == 'approved'){
                        if(isset($sel['user_benefits_id']) && !is_null($sel['user_benefits_id']))
                        {
                            $ub_id = UserBenefit::where('id', $sel['user_benefits_id'])->first();
                            if(!is_null($ub_id))
                            {
                                if($ub_id->status_id != $user_status_approved?->id)
                                {
                                    $ub_id->status_id = $user_status_approved?->id;
                                    $ub_id->fecha_confirmado = new Carbon();
                                    $ub_id->save();

                                    $mail_user = User::where('id', $ub_id?->user_id)->select('id','email')->first();
                                    $mail_benefit = Benefit::where('id', $ub_id?->benefit_id)->select('id','title')->first();
                                    if(!is_null($mail_user) && !is_null($mail_benefit)) {
                                        Benefit::sendEmail( 'confirm', $mail_user, $mail_benefit );
                                    }
                                }
                            }
                        }
                    }

                }

                cache_clear_model(UserBenefit::class);
                $users_approved = collect($seleccionados)->where('ev_user_status','approved')->all();
                if(count($users_approved)>0){
                    $benefit = Benefit::where('id',$benefit_id)->first();
                    $benefit->syncUsersInBenefitsMeeting($users_approved);
                }
                // dd($users_approved);
                DB::commit();
            } catch (\Exception $e) {
                info($e);
                DB::rollBack();
                // Error::storeAndNotificateException($e, request());
                abort(errorExceptionServer());
            }

            return $response;
        }

        return $response;
    }

    protected function tagsDefault()
    {
        return [
            ['code' => 'adaptacion-y-flexibilidad', 'name' => 'Adaptación y flexibilidad', 'edit' => false ],
            ['code' => 'comunicacion', 'name' => 'Comunicación', 'edit' => false ],
            ['code' => 'curiosidad', 'name' => 'Curiosidad', 'edit' => false ],
            ['code' => 'determinacion-en-la-ejecucion', 'name' => 'Determinación en la ejecución', 'edit' => false ],
            ['code' => 'energizacion-de-personas', 'name' => 'Energización de personas', 'edit' => false ],
            ['code' => 'foco-en-data', 'name' => 'Foco en Data', 'edit' => false ],
            ['code' => 'mentalidad-digital', 'name' => 'Mentalidad Digital', 'edit' => false ],
            ['code' => 'obsesion-por-el-cliente', 'name' => 'Obsesión por el cliente', 'edit' => false ],
            ['code' => 'orientacion-a-resultados', 'name' => 'Orientación a resultados', 'edit' => false ],
            ['code' => 'orientacion-al-cliente', 'name' => 'Orientación al cliente', 'edit' => false ],
            ['code' => 'proactividad', 'name' => 'Proactividad', 'edit' => false ],
            ['code' => 'trabajo-en-equipo', 'name' => 'Trabajo en Equipo', 'edit' => false ],
            ['code'=> 'basico', 'name'=> "Básico", 'edit' => false ],
            ['code'=> 'intermedio', 'name'=> "Intermedio", 'edit' => false ],
            ['code'=> 'avanzado', 'name'=> "Avanzado", 'edit' => false ]
        ];
    }

    // Apis

    protected function registerPollOfUserForBenefit( $data )
    {
        $response['error'] = false;
        $response['data'] = [];

        $user_id = $data['user'] ?? null;
        $benefit_id = $data['benefit'] ?? null;

        $is_registered = UserBenefit::where('user_id', $user_id)
                        ->where('benefit_id', $benefit_id)
                        ->first();

        if( $is_registered?->fecha_encuesta ) {
            $response['error'] = true;
            $response['msg'] = [
                'title' => 'Ya respondiste esta encuesta',
                'description' => ['Ya respondiste la encuesta asignada a este beneficio.']
            ];
        }
        else {
            if($is_registered)
            {
                $is_registered->fecha_encuesta = new Carbon();
                $is_registered->save();
                cache_clear_model(UserBenefit::class);

                $benefit = Benefit::where('id', $benefit_id)->first();

                $users_subscribed_in_benefit = UserBenefit::whereHas('status', function($q){
                                    $q->where('code', 'subscribed');
                                    $q->orWhere('code', 'approved');
                                    $q->orWhere('code', 'exchanged');
                                })
                                ->where('benefit_id', $benefit->id)->count();

                if(!is_null($benefit->cupos) && is_numeric($benefit->cupos)) {
                    $registrados = $users_subscribed_in_benefit;
                    $benefit->cupos -= $registrados;
                    $benefit->cupos = $benefit->cupos < 0 ? 0 : $benefit->cupos;
                }

                $response['msg'] = [
                    'title' => 'Encuesta completa',
                    'description' => ['¡Gracias!<br>Tus comentarios son importantes para nosotros']
                ];
                $response['data'] = [
                    'benefit_id' => $benefit_id,
                    'user_status' => ['name' => 'Confirmado', 'code' => 'exchanged'],
                    'subscribed' => true,
                    'status' => ['name' => 'Confirmado', 'code' => 'exchanged'],
                    'cupos' => $benefit->cupos ?? null
                ];
            }
            else
            {
                $response['error'] = true;
                $response['msg'] = [
                    'title' => 'No cuentas con este beneficio',
                    'description' => ['No estás registrado en este beneficio.']
                ];
            }
        }

        return $response;
    }

    protected function registerUserForBenefit( $data )
    {
        $response['error'] = false;
        $response['data'] = [];

        $user_id = $data['user'] ?? null;
        $benefit_id = $data['benefit'] ?? null;

        $max_benefits_workspace = Benefit::join('workspaces','benefits.workspace_id','=','workspaces.id')
                                ->where('benefits.id', $benefit_id)
                                ->select('workspaces.max_benefits')
                                ->first();

        $limit_benefits_x_user = $max_benefits_workspace ? $max_benefits_workspace->max_benefits : 0;
        $limit_benefits_x_user = $limit_benefits_x_user ?? 0;

        $is_registered = UserBenefit::where('user_id', $user_id)
                        ->where('benefit_id', $benefit_id)
                        ->whereHas('status', function($q){
                            $q->where('code','subscribed');
                            $q->orWhere('code', 'approved');
                            $q->orWhere('code', 'exchanged');
                        })
                        ->first();

        if($is_registered) {
            $response['error'] = true;
            $response['msg'] = [
                'title' => 'Ya cuentas con este beneficio',
                'description' => ['Ya te encuentras registrado en este beneficio.']
            ];
        }
        else {
            $currentYear = Carbon::now()->year;
            $benefits_user_registered = UserBenefit::whereHas('status', function($q){
                                            $q->where('code', 'subscribed');
                                            $q->orWhere('code', 'approved');
                                            $q->orWhere('code', 'exchanged');
                                        })
                                        ->whereYear('fecha_registro', $currentYear)
                                        ->where('user_id',$user_id)->count();
            if($benefits_user_registered < $limit_benefits_x_user) {
                try {
                    DB::beginTransaction();

                    $user_status_subscribed = Taxonomy::getFirstData('benefit', 'user_status', 'subscribed');
                    $type_register_regular = Taxonomy::getFirstData('benefit', 'type_register', 'regular');

                    $is_created =  UserBenefit::updateOrCreate([
                            'user_id' => $user_id,
                            'benefit_id' => $benefit_id
                        ],[
                        'user_id' => $user_id,
                        'benefit_id' => $benefit_id,
                        'status_id' => $user_status_subscribed?->id,
                        'type_id' => $type_register_regular?->id,
                        'fecha_registro' => new Carbon()
                    ]);
                    cache_clear_model(UserBenefit::class);

                    if($is_created) {

                        $benefit = Benefit::where('id', $benefit_id)->first();

                        $user = User::where('id', $user_id)->first();
                        if($user){
                            $users = [];
                            array_push($users, $user);
                            // $benefit->syncUsersInBenefitsMeeting($users);
                        }

                        $users_subscribed_in_benefit = UserBenefit::whereHas('status', function($q){
                                            $q->where('code', 'subscribed');
                                            $q->orWhere('code', 'approved');
                                            $q->orWhere('code', 'exchanged');
                                        })
                                        ->where('benefit_id', $benefit->id)->count();

                        if(!is_null($benefit->cupos) && is_numeric($benefit->cupos)) {
                            $registrados = $users_subscribed_in_benefit;
                            $benefit->cupos -= $registrados;
                            $benefit->cupos = $benefit->cupos < 0 ? 0 : $benefit->cupos;
                        }

                        $response['msg'] = [
                            'title' => 'Inscripción confirmada',
                            'description' => ['Te has inscrito satisfactoriamente al beneficio de <b>'.$benefit->title.'</b>.<br>Recuerda revisar el detalle.']
                        ];
                        $response['data'] = [
                            'benefit_id' => $benefit_id,
                            'user_status' => ['name' => 'Retirarme', 'code' => 'subscribed'],
                            'subscribed' => true,
                            'status' => ['name' => 'Retirarme', 'code' => 'subscribed'],
                            'cupos' => $benefit->cupos ?? null
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
                        'Has alcanzando <b>el máximo de beneficios inscritos a la vez ('.$limit_benefits_x_user.')</b>, si deseas registrarte debes retirarte de otro beneficio o comunicarte con tu coordinador del beneficio.'
                    ]
                ];
            }
        }

        return $response;
    }

    protected function notifyUserForBenefit( $data )
    {
        $response['error'] = false;
        $response['data'] = [];

        $user_id = $data['user'] ?? null;
        $benefit_id = $data['benefit'] ?? null;

        try {
            DB::beginTransaction();

            $user_status_notify = Taxonomy::getFirstData('benefit', 'user_status', 'notify');

            $benefit = UserBenefit::where('user_id', $user_id)
                                ->where('benefit_id', $benefit_id)
                                ->first();

            if($benefit) {
                $benefit->status_id = $user_status_notify?->id;
                $benefit->save();
            }
            else {
                $benefit = UserBenefit::updateOrCreate([
                    'user_id' => $user_id,
                    'benefit_id' => $benefit_id
                ]
                    ,[
                    'user_id' => $user_id,
                    'benefit_id' => $benefit_id,
                    'status_id' => $user_status_notify?->id,
                ]);
            }
            cache_clear_model(UserBenefit::class);

            if($benefit) {

                $users_subscribed_in_benefit = UserBenefit::whereHas('status', function($q){
                                    $q->where('code', 'subscribed');
                                    $q->orWhere('code', 'approved');
                                    $q->orWhere('code', 'exchanged');
                                })
                                ->where('benefit_id', $benefit->id)->count();

                if(!is_null($benefit->cupos) && is_numeric($benefit->cupos)) {
                    $registrados = $users_subscribed_in_benefit;
                    $benefit->cupos -= $registrados;
                    $benefit->cupos = $benefit->cupos < 0 ? 0 : $benefit->cupos;
                }

                $response['msg'] = [
                    'title' => 'Alerta de beneficio activada',
                    'description' => ['Se notificará en tu correo asignado cuando este beneficio se encuentre disponible.']
                ];
                $response['data'] = [
                    'benefit_id' => $benefit_id,
                    'user_status' => ['name' => 'Notificarme', 'code' => 'notify'],
                    'subscribed' => false,
                    'status' => ['name' => 'Notificarme', 'code' => 'notify'],
                    'cupos' => $benefit->cupos ?? null
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
                        ->whereHas('status', function($q){
                            $q->where('code', 'subscribed');
                            $q->orWhere('code', 'approved');
                        })
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

                    $user_status_unsubscribe = Taxonomy::getFirstData('benefit', 'user_status', 'unsubscribe');

                    $is_registered->status_id = $user_status_unsubscribe?->id;
                    $is_registered->save();
                    // $is_registered->delete();
                    cache_clear_model(UserBenefit::class);

                    $benefit = Benefit::where('id', $benefit_id)->first();

                    if($benefit){

                        $user = User::where('id', $user_id)->first();
                        if($user){
                            $users = [];
                            array_push($users, $user);
                            // $benefit->syncUsersInBenefitsMeeting($users, 'remove');
                        }

                        $users_subscribed_in_benefit = UserBenefit::whereHas('status', function($q){
                                            $q->where('code', 'subscribed');
                                            $q->orWhere('code', 'approved');
                                            $q->orWhere('code', 'exchanged');
                                        })
                                        ->where('benefit_id', $benefit->id)->count();

                        if(!is_null($benefit->cupos) && is_numeric($benefit->cupos)) {
                            $registrados = $users_subscribed_in_benefit;
                            $benefit->cupos -= $registrados;
                            $benefit->cupos = $benefit->cupos < 0 ? 0 : $benefit->cupos;
                        }

                        $response['msg'] = [
                            'title' => 'Te has retirado del beneficio',
                            'description' => ['Ya no te encuentras registrado al beneficio: '. $benefit->title]
                        ];
                        $response['data'] = [
                            'benefit_id' => $benefit_id,
                            'user_status' => ['name' => 'Registrarme', 'code' => 'active'],
                            'subscribed' => false,
                            'status' => ['name' => 'Registrarme', 'code' => 'active'],
                            'cupos' => $benefit->cupos ?? null
                        ];
                    }

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
        $user = auth()->user();
        $workspace_id = $user?->subworkspace?->parent?->id;

        $response['data'] = null;
        $filtro = $data['filtro'] ?? $data['q'] ?? '';
        $user_id = $data['user'];
        $status_benefit = ($data['status'] && is_array($data['status']) && count($data['status']) > 0) ? $data['status'] : null;
        $group_benefit = ($data['type'] && is_array($data['type']) && count($data['type']) > 0) ? $data['type'] : null;

        if( is_array($status_benefit) && in_array('locked', $status_benefit) )
            array_push($status_benefit,'finished');

        $benefits_asigned = array_column($user->getSegmentedByModelType(Benefit::class),'id');

        $benefits_user_registered = UserBenefit::whereHas('status', function($q){
                                        $q->where('code', 'subscribed');
                                        $q->orWhere('code', 'approved');
                                    })
                                    ->where('user_id',$user_id)->pluck('benefit_id')->toArray();
        $benefits_user_notified = UserBenefit::whereHas('status', function($q){
                                        $q->where('code', 'notify');
                                        $q->orWhere('code', 'notified');
                                    })
                                    ->where('user_id',$user_id)->pluck('benefit_id')->toArray();

        $benefits_query = Benefit::with([
            'polls',
            'group'=> function ($query) {
                    $query->select('id', 'name', 'code');
                },
            'type'=> function ($query) {
                    $query->select('id', 'name', 'code');
                },
            'status'=> function ($query) {
                    $query->select('id', 'name', 'code');
                }
        ])
        ->where('active',1)
        ->whereIn('id', $benefits_asigned);

        $field = request()->sortBy ?? 'created_at';
        $sort = request()->sortDesc == 'true' ? 'DESC' : 'ASC';

        $benefits_query->orderBy($field, $sort);

        // $benefits_query->whereHas('status', function ($query) use ($status_benefit) {
        //     $query->where('code', '<>', 'released');
        // });

        if (!is_null($filtro) && !empty($filtro)) {
            $benefits_query->where(function ($query) use ($filtro) {
                $query->where('title', 'like', "%$filtro%");
                $query->orWhere('description', 'like', "%$filtro%");
            });
        }
        if($status_benefit) {
            if(in_array('subscribed', $status_benefit) && count($status_benefit) == 1) {
                $benefits_query->whereIn('id', $benefits_user_registered);
            }
            else if(in_array('subscribed', $status_benefit) && count($status_benefit) > 1) {
                $benefits_query->where(function($t) use ($status_benefit, $benefits_user_registered){
                    $t->whereHas('status', function ($query) use ($status_benefit) {
                        if(in_array('exchanged', $status_benefit)) {
                            $query->whereIn('code', $status_benefit);
                            $query->orWhereIn('code', ['approved']);
                        }
                        else{
                            $query->whereIn('code', $status_benefit);
                        }
                    });
                    $t->orWhereIn('id', $benefits_user_registered);
                });
            }
            else {
                $benefits_query->whereHas('status', function ($query) use ($status_benefit) {
                    $query->whereIn('code', $status_benefit);
                });
            }
        }
        if($group_benefit) {
            $benefits_query->whereHas('group', function ($query) use ($group_benefit) {
                $query->whereIn('code', $group_benefit);
            });
        }

        $benefits = $benefits_query->paginate(request('paginate', 15));

        $benefits_items = $benefits->items();

        foreach($benefits_items as $key => $item)
        {
            $item->user_status = null;
            $item->subscribed = false;
            $users_subscribed_in_benefit = UserBenefit::whereHas('status', function($q){
                                                $q->where('code', 'subscribed');
                                                $q->orWhere('code', 'approved');
                                                $q->orWhere('code', 'exchanged');
                                            })
                                            ->where('benefit_id', $item->id)->count();
            if(!is_null($item->cupos) && is_numeric($item->cupos)) {
                $registrados = $users_subscribed_in_benefit;
                $item->cupos -= $registrados;
                $item->cupos = $item->cupos < 0 ? 0 : $item->cupos;
            }

            if(in_array($item->id, $benefits_user_registered)) {
                $item->user_status = ['name' => 'Retirarme', 'code' => 'subscribed'];
                $item->subscribed = true;
                $is_approved = UserBenefit::whereHas('status', function($q){
                                                    $q->where('code', 'approved');
                                                })
                                                ->where('benefit_id', $item->id)
                                                ->where('user_id', $user_id)
                                                ->first();

                if($is_approved || $item->status?->code == 'released') {
                    $item->user_status = ['name' => 'Confirmado', 'code' => 'exchanged'];

                    $now = new Carbon();
                    $fecha_encuesta = new Carbon($item->fecha_encuesta);
                    $user_poll = UserBenefit::where('benefit_id', $item->id)->where('user_id', $user_id)->first();
                    if ($now->gt($fecha_encuesta) && is_null($user_poll?->fecha_encuesta)) {
                        $item->user_status = ['name' => 'Encuesta', 'code' => 'poll'];
                    }
                }
            }
            else if (in_array($item->id, $benefits_user_notified)) {
                $item->user_status = ['name' => 'Notificarme', 'code' => 'notify'];
                if($item->status?->code == 'active') {
                    $item->user_status = ['name' => 'Registrarme', 'code' => 'active'];
                }
            }
            else {
                if($item->status?->code == 'active') {
                    $item->user_status = ['name' => 'Registrarme', 'code' => 'active'];
                    if(!is_null($item->cupos) && $item->cupos == 0)
                        $item->user_status = ['name' => 'Contactarme', 'code' => 'contact-me'];
                }
                else if($item->status?->code == 'locked') {
                    $item->user_status = ['name' => 'Notificarme', 'code' => 'disabled'];
                }
                else if($item->status?->code == 'finished') {
                    $item->user_status = ['name' => 'Contactarme', 'code' => 'contact-me'];
                }
                else if($item->status?->code == 'released') {
                    $item->user_status = $item->status;
                }
            }

            unset($item->status);
            $item->status = $item->user_status;


            $direccion = ($item->direccion) ? json_decode($item->direccion) : null;
            $item->ubicacion = ($direccion) ? $direccion->ubicacion ?? null : null;

            $item->inicio_inscripcion = $item->inicio_inscripcion ? Carbon::parse($item->inicio_inscripcion)->format('d/m/Y') : null;
            $item->fin_inscripcion = $item->fin_inscripcion ? Carbon::parse($item->fin_inscripcion)->format('d/m/Y') : null;
            $item->fecha_liberacion = $item->fecha_liberacion ? Carbon::parse($item->fecha_liberacion)->format('d/m/Y') : null;
            $item->fecha_encuesta = $item->fecha_encuesta ? Carbon::parse($item->fecha_encuesta)->format('d/m/Y') : null;

            if(!is_null($item->poll_id)) {
                $item->type_poll = 'interno';
                $poll = Poll::select('id','titulo','imagen','anonima')->where('id', $item->poll_id)->first();
                $item->poll = $poll;
            }
            else {
                $item->poll = null;
                $item->type_poll = null;
                if(count($item->polls) > 0) {
                    $item->type_poll = 'externo';
                    $item->poll = [
                        'id' => $item->polls[0]?->id ?? null,
                        'name' => $item->polls[0]?->name ?? null,
                        'value' => $item->polls[0]?->value ?? null
                    ];
                }
            }
            if($item->dificultad)
            {
                $name_tag = $this->searchNameForTags($item->dificultad,$this->tagsDefault());
                $item->dificultad = $name_tag ?? $item->dificultad;
            }
            $item->hasMeeting = false;
            if( count($item->silabo) > 0){
                $item->hasMeeting = boolval(Meeting::select('id')->where('model_type','App\\Models\\BenefitProperty')->whereIn('model_id',$item->silabo->pluck('id'))->first());
            }
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
                $item->active,
                $item->user_status,
                $item->polls,
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

        $benefits_user_registered = UserBenefit::whereHas('status', function($q){
                                        $q->where('code', 'subscribed');
                                        $q->orWhere('code', 'approved');
                                        $q->orWhere('code', 'exchanged');
                                    })
                                    ->where('user_id',$user_id)->pluck('benefit_id')->toArray();

        $benefits_user_notified = UserBenefit::whereHas('status', function($q){
                                        $q->where('code', 'notify');
                                        $q->orWhere('code', 'notified');
                                    })
                                    ->where('user_id',$user_id)->pluck('benefit_id')->toArray();

        $users_subscribed_in_benefit = UserBenefit::whereHas('status', function($q){
                                            $q->where('code', 'subscribed');
                                            $q->orWhere('code', 'approved');
                                            $q->orWhere('code', 'exchanged');
                                        })
                                        ->where('benefit_id', $benefit_id?->id)->count();

        $benefit = Benefit::with(
            ['implements','silabo','polls','links','speaker',
            'group'=> function ($query) {
                    $query->select('id', 'name', 'code');
                },
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

        if($benefit) {

            $benefit->user_status = null;
            $benefit->subscribed = false;
            if(!is_null($benefit->cupos) && is_numeric($benefit->cupos)) {
                $registrados = $users_subscribed_in_benefit;
                $benefit->cupos -= $registrados;
                $benefit->cupos = $benefit->cupos < 0 ? 0 : $benefit->cupos;
            }

            if(in_array($benefit->id, $benefits_user_registered)) {

                $benefit->user_status = ['name' => 'Retirarme', 'code' => 'subscribed'];
                $benefit->subscribed = true;
                $is_approved = UserBenefit::whereHas('status', function($q){
                                                    $q->where('code', 'approved');
                                                })
                                                ->where('benefit_id', $benefit->id)
                                                ->where('user_id', $user_id)
                                                ->first();

                if($is_approved || $benefit->status?->code == 'released') {
                    $benefit->user_status = ['name' => 'Confirmado', 'code' => 'exchanged'];

                    $now = new Carbon();
                    $fecha_encuesta = new Carbon($benefit->fecha_encuesta);
                    $user_poll = UserBenefit::where('benefit_id', $benefit->id)->where('user_id', $user_id)->first();
                    if ($now->gt($fecha_encuesta) && is_null($user_poll?->fecha_encuesta)) {
                        $benefit->user_status = ['name' => 'Encuesta', 'code' => 'poll'];
                    }
                }

            }
            else if (in_array($benefit->id, $benefits_user_notified)) {

                $benefit->user_status = ['name' => 'Notificarme', 'code' => 'notify'];
                if($benefit->status?->code == 'active') {
                    $benefit->user_status = ['name' => 'Registrarme', 'code' => 'active'];
                }
            }
            else {
                if($benefit->status?->code == 'active') {
                    $benefit->user_status = ['name' => 'Registrarme', 'code' => 'active'];
                    if(!is_null($benefit->cupos) && $benefit->cupos == 0)
                        $benefit->user_status = ['name' => 'Contactarme', 'code' => 'contact-me'];
                }
                else if($benefit->status?->code == 'locked') {
                    $benefit->user_status = ['name' => 'Notificarme', 'code' => 'disabled'];
                }
                else if($benefit->status?->code == 'finished') {
                    $benefit->user_status = ['name' => 'Contactarme', 'code' => 'contact-me'];
                }
                else if($benefit->status?->code == 'released') {
                    $benefit->user_status = $benefit->status;
                }
            }

            unset($benefit->status);
            $benefit->status = $benefit->user_status;

            $direccion = ($benefit->direccion) ? json_decode($benefit->direccion) : null;
            if ($direccion) {
                $benefit->direccion = (object)[
                    'lugar' => $direccion->address ?? null,
                    'link' => $direccion->url ?? null,
                    'image' => $direccion->image ?? null,
                    'ubicacion' => $direccion->ubicacion ?? null,
                    'referencia' => $benefit->referencia ?? null,
                ];
                $benefit->ubicacion = $direccion->ubicacion ?? null;
            }
            else{
                $benefit->direccion = null;
                $benefit->ubicacion = null;
            }
            $benefit->inicio_inscripcion = $benefit->inicio_inscripcion ? Carbon::parse($benefit->inicio_inscripcion)->format('d/m/Y') : null;
            $benefit->fin_inscripcion = $benefit->fin_inscripcion ? Carbon::parse($benefit->fin_inscripcion)->format('d/m/Y') : null;
            $benefit->fecha_liberacion = $benefit->fecha_liberacion ? Carbon::parse($benefit->fecha_liberacion)->format('d/m/Y') : null;
            $benefit->fecha_encuesta = $benefit->fecha_encuesta ? Carbon::parse($benefit->fecha_encuesta)->format('d/m/Y') : null;

            if(!is_null($benefit->poll_id)) {
                $benefit->type_poll = 'interno';
                $poll = Poll::select('id','titulo','imagen','anonima')->where('id', $benefit->poll_id)->first();
                $benefit->poll = $poll;
            }
            else {
                $benefit->poll = null;
                $benefit->type_poll = null;
                if(count($benefit->polls) > 0) {
                    $benefit->type_poll = 'externo';
                    $benefit->poll = [
                        'id' => $benefit->polls[0]?->id ?? null,
                        'name' => $benefit->polls[0]?->name ?? null,
                        'value' => $benefit->polls[0]?->value ?? null
                    ];
                }
            }

            $benefit->silabo->each(function($item){
                $item->value_date = Carbon::parse($item->value_date)->format('d/m/Y');
                $item->value_time = Carbon::parse($item->value_time)->format('H:i');
            });
            if($benefit->dificultad)
            {
                $name_tag = $this->searchNameForTags($benefit->dificultad,$this->tagsDefault());
                $benefit->dificultad = $name_tag ?? $benefit->dificultad;
            }
            $benefit->hasMeeting = false;
            if( count($benefit->silabo) > 0){
                $benefit->hasMeeting = boolval(Meeting::where('model_type','App\\Models\\BenefitProperty')->whereIn('model_id',$benefit->silabo->pluck('id'))->first());
            }

            unset(
                $benefit->referencia,
                $benefit->workspace_id,
                $benefit->type_id,
                $benefit->speaker_id,
                $benefit->status_id,
                $benefit->poll_id,
                $benefit->active,
                $benefit->polls
            );
        }

        return ['data'=>$benefit];
    }

    private function searchNameForTags($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['code'] === $id) {
                return $val['name'];
            }
        }
        return null;
     }

    protected function config($data)
    {

        // Get benefits configuration from user's workspace

        $user = auth()->user();
        $benefitsConfiguration = $user->subworkspace?->benefits_configuration;

        // If default benefit group does not have a name,
        // get default name from taxonomies

        $tab_name = null;
        if (isset($benefitsConfiguration->default_group_name)) {
            $tab_name = $benefitsConfiguration->default_group_name;
        }

        if (!$tab_name) {
            $tab = Taxonomy::query()
                ->where('group','benefit')
                ->where('type','group')
                ->where('code','ir-academy')
                ->select('name')->first();
            $tab_name = $tab?->name ?? 'IR Academy';
        }

        $response = [
            "buscador" => [
                "filtros_status" => [
                    ["name" => "Activos", "code"=> "active", "checked" => true],
                    ["name" => "Bloqueados", "code"=> "locked", "checked" => true],
                    ["name" => "Registrados", "code"=> "subscribed", "checked" => true],
                    ["name" => "Canjeados", "code"=> "exchanged", "checked" => true]
                ],
                "filtros_tipo" => [
                    ["name" => "Todos", "code"=> "free", "show"=> false, "checked" => true],
                    ["name" =>  $tab_name, "code"=> "ir-academy", "show"=> true, "checked" => true]
                ]
            ],
            "tabs"=> [
                [
                    "name" => "Todos",
                    "code"=> "free",
                    "filtros_status" => [
                        ["name" => "Activos", "code"=> "active", "checked" => true],
                        ["name" => "Bloqueados", "code"=> "locked", "checked" => true]
                    ],
                    "filtros_tipo" => [
                        ["name" => "Todos", "code"=> "free", "show"=> false, "checked" => true],
                        ["name" => $tab_name, "code"=> "ir-academy", "show"=> false, "checked" => true]
                    ]
                ],
                [
                    "name" =>  $tab_name,
                    "code" => "ir-academy",
                    "filtros_status" => [
                        ["name" => "Activos", "code"=> "active", "checked" => true],
                        ["name" => "Bloqueados", "code"=> "locked", "checked" => true]
                    ],
                    "filtros_tipo" => [
                        ["name" => $tab_name, "code"=> "ir-academy", "show"=> false, "checked" => true]
                    ]
                ],
                [
                    "name" => "Mis Beneficios",
                    "code"=> "benefits",
                    "filtros_status" => [
                        ["name" => "Canjeados", "code"=> "exchanged", "checked" => true],
                        ["name" => "Registrados", "code"=> "subscribed", "checked" => true]
                    ],
                    "filtros_tipo" => [
                        ["name" => "Todos", "code"=> "free", "show"=> false, "checked" => true],
                        ["name" => $tab_name, "code"=> "ir-academy", "show"=> true, "checked" => true]
                    ]
                ]
            ]
        ];
        return ['data' => $response];
    }

    protected function sendEmail( $type = null, $user = null, $benefit = null )
    {
        if($type && $user && $benefit){
            $base_url = config('app.web_url') ?? null;
            $email = $user?->email ?? null;

            if($base_url) {
                if($type == 'notify') {
                    $imagen = URL::asset('img/benefits/icon_mail_notify.png');
                    $subject = 'Inscripción abierta';
                }
                else if($type == 'confirm') {
                    $imagen = URL::asset('img/benefits/icon_mail_confirm.png');
                    $subject = 'Felicitaciones, has sido confirmado para acceder a:';
                }
                else {
                    $imagen = URL::asset('img/benefits/icon_mail_new.png');
                    $subject = 'Tenemos un nuevo beneficio para ti';
                }
                $mail_data = [ 'subject' => $subject,
                               'benefit_name' => $benefit?->title,
                               'benefit_link' => $base_url.'/beneficio?beneficio='.$benefit?->id,
                               'icon' => $imagen ];

                // enviar email
                if($email) {
                    Mail::to($email)
                        ->send(new EmailTemplate('emails.nuevo_beneficio', $mail_data));
                }
            }
        }
    }
    function syncUsersInBenefitsMeeting(array $users,$type='add'){
        //$benefit->syncUsersInBenefitsMeeting(User $users);
        $benefit = $this;
        $benefit->loadMissing('silabo');
        foreach ($benefit->silabo as $silabo) {
            $meeting = Meeting::where('model_type','App\\Models\\BenefitProperty')
                        ->whereHas('status',function($q){
                            $q->whereIn('code',['reserved','scheduled','in-progress']);
                        })
                        // ->whereRelation('status', 'code', 'in',['reserved','scheduled','in-progress'])
                        ->where('model_id',$silabo->id)->first();
            if($meeting){
                switch ($type) {
                    case 'add':
                        Meeting::addAttendantFromUser($meeting,$users);
                        Attendant::createOrUpdatePersonalLinkMeeting(
                            $meeting, false
                        );
                        break;
                    case 'remove':
                        Meeting::deleteAttendantFromUser($meeting,$users);
                        break;
                }
            }
        }
    }

    protected function duplicateBenefitsFromWorkspace($current_workspace,$new_workspace){
        $benefits = Benefit::select('id','type_id','speaker_id','status_id','poll_id','group_id','title','description','image','cupos',
        'inicio_inscripcion','fin_inscripcion','fecha_liberacion','fecha_encuesta','correo','duracion',
        'promotor','promotor_imagen','direccion','referencia','accesible','dificultad','active'
    )->with([
        'speaker:id,name,biography,email,specialty,image,active',
        'speaker.experiences:speaker_id,company,occupation,active',
        'properties:id,benefit_id,type_id,name,value,value_date,value_time,position,active'
    ])->where('workspace_id',$current_workspace->id)->get();
        foreach ($benefits as $benefit) {
            $_properties = $benefit->properties->map(function ($p) {
                unset($p['benefit_id']);
                unset($p['id']);
                return $p;
            })->toArray();

            if($benefit->speaker){
                $speaker = $benefit->speaker->toArray();
                unset($speaker['id']);
                $speaker['workspace_id'] = $new_workspace->id;
                $_speaker = new Speaker();
                $_speaker->fill($speaker);
                $_speaker->save();
                $benefit['speaker_id'] = $_speaker->id;
                $experiencies = $benefit->speaker->experiences->map(function($experiencie){
                    unset($experiencie->speaker_id);
                    return $experiencie;
                })->toArray();
                if(count($experiencies)>0){
                    $_speaker->experiences()->createMany($experiencies);
                }
            }
            $benefit = $benefit->toArray();
            unset($benefit['id']);
            unset($benefit['speaker']);
            unset($benefit['properties']);
            $benefit['workspace_id'] = $new_workspace->id;
            $_benefit = new Benefit();
            $_benefit->fill($benefit);
            $_benefit->save();
            $_benefit->properties()->createMany($_properties);
        }
    }
}
