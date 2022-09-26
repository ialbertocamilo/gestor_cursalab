<?php

namespace App\Models;

// use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Passport\HasApiTokens;
use Silber\Bouncer\Database\HasRolesAndAbilities;
use NotificationChannels\WebPush\HasPushSubscriptions;
use Altek\Accountant\Contracts\Identifiable;
use Altek\Accountant\Contracts\Recordable;
use Carbon\Carbon;

use Silber\Bouncer\Database\Models;

use Khsing\World\Models\Country;

use App\Traits\CustomAudit;
use App\Traits\CustomCRUD;
use App\Traits\CustomMedia;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Support\Str;

use Spatie\Image\Manipulations;

use Bouncer;

class User extends Authenticatable implements Identifiable, Recordable, HasMedia
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndAbilities, HasPushSubscriptions;

    use \Altek\Accountant\Recordable, \Altek\Eventually\Eventually;

    use CustomCRUD, CustomAudit, CustomMedia;

    use InteractsWithMedia;

    use Cachable;

    use Cachable {
        Cachable::getObservableEvents insteadof \Altek\Eventually\Eventually, CustomAudit;
        Cachable::newBelongsToMany insteadof \Altek\Eventually\Eventually, CustomAudit;
        Cachable::newMorphToMany insteadof \Altek\Eventually\Eventually, CustomAudit;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'lastname', 'surname', 'username','fullname', 'slug', 'alias','person_number','phone_number',
        'email', 'password', 'active', 'phone', 'telephone', 'birthdate',
        'type_id', 'workspace_id', 'job_position_id', 'area_id', 'gender_id', 'document_type_id',
        'document', 'ruc',
        'country_id', 'district_id', 'address', 'description', 'quote',
        'external_id', 'fcm_token', 'token_firebase','secret_key'
    ];

    protected $with = ['roles', 'abilities'];
    protected $appends = ['fullname', 'age'];
    protected $dates = ['birthdate'];

    public $defaultRelationships = [
        'type_id' => 'type',
        // 'job_position_id' => 'job_position', 'area_id' =>  'area', 'gender_id' => 'gender',
        // 'document_type_id' => 'document_type', 'country_id' => 'country', 'district_id' =>  'district'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
        'pivot'
    ];

    protected $ledgerThreshold = 100;


    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function getIdentifier()
    {
        return $this->getKey();
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'users.' . $this->id;
    }

    public function routeNotificationForFcm()
    {
        return $this->fcm_token;
        // return $this->getDeviceTokens();
    }

    /**
     * Route notifications for the Slack channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return $notification->webhookUrl ?? config('slack.routes.support');
    }

    public function criterion_values()
    {
        return $this->belongsToMany(CriterionValue::class);
    }

    public function subworkspace()
    {
        return $this->belongsTo(Workspace::class, 'subworkspace_id');
    }

    public function summary()
    {
        return $this->hasOne(SummaryUser::class);
    }

    public function summary_courses()
    {
        return $this->hasMany(SummaryCourse::class);
    }

    public function summary_topics()
    {
        return $this->hasMany(SummaryTopic::class);
    }

    public function scopeFilterText($q, $filter)
    {
        $q->where(function ($q) use ($filter) {
            $q->whereRaw('document like ?', ["%{$filter}%"]);
            $q->orWhereRaw('name like ?', ["%{$filter}%"]);
            $q->orWhereRaw('email like ?', ["%{$filter}%"]);
            $q->orWhereRaw('lastname like ?', ["%{$filter}%"]);
            $q->orWhereRaw('surname like ?', ["%{$filter}%"]);
        });
    }

    public function getFullnameAttribute()
    {
        // if($this->fullname){
        //     return $this->fullname;
        // }
        $fullname = $this->name;
        if ($this->lastname) $fullname .= ' ' . $this->lastname;

        if ($this->surname) $fullname .= ' ' . $this->surname;

        return $fullname;
    }

    public function getLastnamesAttribute()
    {
        $lastnames = $this->lastname;

        if ($this->surname) $lastnames .= ' ' . $this->surname;

        return $lastnames;
    }

    public function getAgeAttribute()
    {
        if (isset($this->attributes['birthdate']))
            return Carbon::parse($this->attributes['birthdate'])->age;

        return null;
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value))
            $this->attributes['password'] = bcrypt($value);
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value === '1' or $value === 1);
    }

    public function getCriteria()
    {
        $criterion_ids = $this->criterion_values()->get()->pluck('criterion_id')->toArray();

        return Criterion::whereIn('id', $criterion_ids)->get();
    }

    public function getCriterionValue($criterion_code)
    {
        $criterion_ids = $this->criterion_values()->get()->pluck('criterion_id')->toArray();

        return Criterion::whereIn('id', $criterion_ids)->get();
    }

    // public function post()
    // {
    //     return $this->hasMany(Post::class);
    // }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function getAllPermissionsAttribute()
    {
        $permissions = [];
        // foreach (Permission::all() as $permission) {
        //     if (\Auth::user()->can($permission->slug)) {
        //         $permissions[] = $permission->slug;
        //     }
        // }
        return $permissions;
    }

    // /**
    //  * Route notifications for the Slack channel.
    //  *
    //  * @param \Illuminate\Notifications\Notification $notification
    //  * @return string
    //  */
    // public function routeNotificationForSlack($notification)
    // {
    //     return config('slack.routes.support');
    // }

    public function customHasRole($role)
    {
        // return $this->roles()->where('slug', $role)->first();
    }

    public function isMasterOrAdminCursalab()
    {
        // return $this->customHasRole('master') || $this->customHasRole('gestor-lamedia');
        return true;
    }

    public function isSupervisor(): bool
    {
        $supervise_action = Taxonomy::getFirstData('user', 'action', 'supervise');
        $is_supervisor = DB::table('user_relationships')
            ->where('user_id', $this->id)
            ->where('model_type', User::class)
            ->where('relation_type_id', $supervise_action->id)
            ->get();

        return count($is_supervisor) > 0;
    }

    public function getTrainingRole()
    {
        $user = $this;

        //        $training_role =

        return null;
    }

    public function updateStatusUser($active=null,$terminatio_date=null){
        $this->active = $active ? $active : !$this->active;
        $this->save();
        // $criterion = CriterionValue::where('value_text',$terminatio_date)->select('id','value_text')->first();
        $criterion = Criterion::with('values')->where('code','termination_date')->select('id')->first();
        if(!$criterion){
            return $user;
        }
        if($terminatio_date){
            $user_criterion = $this->criterion_values()->where('criterion_id',$criterion->id)->detach();
        }else{

        }
        // $user_criterion = $this->criterion_values()->detach(['criterion_id'=>$criterion->id]);
        // $this->criterion_values()->syncWithoutDetaching([1, 2, 3]);
    }
    protected function storeRequest($data, $user = null)
    {
        try {

            DB::beginTransaction();

            if ($user) :

                $user->update($data);

            else :

                $user = self::create($data);

            endif;

            $user->subworkspace_id = Workspace::query()
                ->where('criterion_value_id', $data['criterion_list']['module'])
                ->first()
                ?->id;

            $user->criterion_values()
                ->sync(array_values($data['criterion_list_final']) ?? []);


            $user->save();
            DB::commit();

        } catch (\Exception $e) {

            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }

    protected function storeRequestFull($data = [], $user = null)
    {
        try {

            DB::beginTransaction();

            if ($user) :

                $user->update($data);

            else :

                $user = $this->create($data);

            endif;

            if ($user->isNotA('superadmin') and $user->isNotA('developer'))
                Bouncer::sync($user)->roles($data['roles'] ?? []);

            // $user->accounts()->sync($data['accounts'] ?? []);

            $user->setMediaCollection($data, 'avatar');

            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();

            return BaseModel::errorResponse(['exception' => $e]);
        }

        return BaseModel::successResponse();
    }

    protected function search($request)
    {
        $query = self::query();
        $query->with('subworkspace');

        if ($request->q) {
            $query->filterText($request->q);
        }
        if ($request->workspace_id){
            $query->whereRelation('subworkspace','parent_id',$request->workspace_id);
        }

        if ($request->subworkspace_id)
            $query->where('subworkspace_id', $request->subworkspace_id);

        if ($request->sub_workspaces_id)
            $query->whereIn('subworkspace_id', $request->sub_workspaces_id);


        $field = $request->sortBy ?? 'created_at';
        $sort = $request->descending == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort)->orderBy('id', $sort);

        return $query->paginate($request->rowsPerPage);
    }

    public function getCurrentCourses($with_programs = true, $with_direct_segmentation = true)
    {
        $user = $this;
        $user->load('criterion_values:id,value_text,criterion_id');
        $programs = collect();
        $all_courses = [];
        // TODO: No considerar criterion_values que se excluyan (ciclo 0)


        if ($with_programs) $this->setProgramCourses($user, $all_courses);

        // TODO: Agregar segmentacion directa
        if ($with_direct_segmentation) $this->setCoursesWithDirectSegmentation($user, $all_courses);

        return collect($all_courses)->unique()->values();
    }

    public function setProgramCourses($user, &$all_courses)
    {
        $user_criterion_values_id = $user->criterion_values->pluck('id');
        $current_active_programs = Block::with([
            'segments.values.criterion_value',
            'block_children.child' => [
                'segments.values.criterion_value',
                'courses' => [
                    'segments.values.criterion_value',
                    'requirements',
                    'schools',
                    'topics' => [
                        'evaluation_type',
                        'requirements',
                        'medias.type'
                    ],
                    'polls.questions',
                    'topics.evaluation_type'
                ],
            ]
        ])
            ->where('parent', 1)
            ->where('active', ACTIVE)
            ->get();

        foreach ($current_active_programs as $program) {

            $program_segment_valid = false;

            if ($program->segments->count() === 0) :

                $program_segment_valid = true;

            else :

                foreach ($program->segments as $segment) {

                    $program_segment_criterion_values_id = $segment->values->pluck('criterion_value_id');
                    $program_segment_valid = $this->validateSegmentByUser($user_criterion_values_id, $program_segment_criterion_values_id);

                    if ($program_segment_valid) break;
                }

            endif;

            if ($program_segment_valid) :

                $blocks = collect();

                foreach ($program->block_children as $block_child) {

                    $block_segment_valid = false;

                    if ($block_child->child->segments->count() === 0) :

                        $block_segment_valid = true;

                    else :

                        foreach ($block_child->child->segments as $segment) {

                            $block_segment_criterion_values_id = $segment->values->pluck('criterion_value_id');
                            $block_segment_valid = $this->validateSegmentByUser($user_criterion_values_id, $block_segment_criterion_values_id);

                            if ($block_segment_valid) break;
                        }

                    endif;

                    if ($block_segment_valid) :

                        $courses = collect();

                        foreach ($block_child->child->courses as $course) {

                            if ($course->segments->count() === 0) :

                                $courses->push([
                                    'id' => $course->id,
                                    'name' => $course->name,
                                    'position' => $course->position,
                                    'schools' => $course->schools->toArray()
                                ]);

                                $all_courses[] = $course;

                            else :

                                foreach ($course->segments as $segment) {

                                    $course_segment_criterion_values_id = $segment->values->pluck('criterion_value_id');
                                    $course_segment_valid = $this->validateSegmentByUser($user_criterion_values_id, $course_segment_criterion_values_id);

                                    if ($course_segment_valid) :

                                        $courses->push([
                                            'id' => $course->id,
                                            'name' => $course->name,
                                            'position' => $course->position,
                                            'schools' => $course->schools->toArray()
                                        ]);

                                        $all_courses[] = $course;
                                        break;

                                    endif;
                                }

                            endif;
                        }
//                        $blocks->push([
//                            'id' => $block_child->child->id,
//                            'name' => $block_child->child->name,
//                            'courses_count' => $courses->count(),
//                            'courses' => $courses->sortBy('position')->values()->all()
//                        ]);
                    endif;
                }
//                $programs->push([
//                    'id' => $program->id,
//                    'name' => $program->name,
//                    'blocks' => $blocks,
//                ]);
            endif;
        }
    }

    public function setCoursesWithDirectSegmentation($user, &$all_courses)
    {
//        dd($user->subworkspace);
        $user->loadMissing('subworkspace.parent');
        $course_segmentations = Course::with([
            'segments.values.criterion_value',
            'requirements',
            'schools',
            'topics' => [
                'evaluation_type',
                'requirements',
                'medias.type'
            ],
            'polls.questions',
            'topics.evaluation_type'
        ])
            ->whereHas('topics', function ($q) {$q->where('active', ACTIVE);})
            ->whereHas('segments', fn($query) => $query->where('active', ACTIVE))
            ->whereRelation('workspaces', 'id', $user->subworkspace->parent->id)
            ->where('active', ACTIVE)
            ->get();

        $user_criteria = $user->criterion_values->groupBy('criterion_id');

        foreach ($course_segmentations as $course) {

            foreach ($course->segments as $segment) {
                $course_segment_criteria = $segment->values->groupBy('criterion_id');
                $valid_segment = $this->validateSegmentByUser($user_criteria, $course_segment_criteria);

                if ($valid_segment) :
                    $all_courses[] = $course;
                    break;
                endif;
            }

        }
    }

    public function validateSegmentByUser(Collection $user_criteria, Collection $segment_criteria): bool
    {
        $segment_valid = false;

        foreach ($segment_criteria as $criterion_id => $segment_values) {
            $user_has_criterion_id = $user_criteria[$criterion_id] ?? false;

            if (!$user_has_criterion_id):
                $segment_valid = false;
                break;
            endif;

            $user_criterion_value_id_by_criterion = $user_criteria[$criterion_id]->pluck('id');
            $segment_valid = $segment_values->whereIn('criterion_value_id', $user_criterion_value_id_by_criterion)->count() > 0;

            if (!$segment_valid):
                $segment_valid = false;
                break;
            endif;
        }

        return $segment_valid;
    }

    public function updateLastUserLogin()
    {
        $user = $this;
        $user->last_login = date('Y-m-d H:i:s');
        $user->save();
    }

    public function updateUserDeviceVersion($request): void
    {
        $user = $this;
        if (!is_null($request['os']) && !is_null($request['version'])) {
            switch ($request['os']) {
                case 'android':
                    $user->android++;
                    $user->v_android = $request['version'];
                    break;
                case 'ios':
                    $user->ios++;
                    $user->v_ios = $request['version'];
                    break;
                case 'huawei':
                    $user->huawei++;
                    $user->v_android = $request['version'];
                    break;
                default:
                    $user->browser++;
                    break;
            }
            $user->save();
        }

    }

    public function getSubworkspaceSetting($field, $value = null)
    {
        // $user = $user ?? auth()->user();

        $settings = $this->subworkspace->$field;

        if (!$settings) return NULL;

        if ($value) return $settings[$value] ?? NULL;

        return $settings;
    }

    protected function calculate_rank($count_approved_courses, $grade_average, $attempts): float|int
    {
        if ($grade_average == 0) return 0;

        $puntos_cursos = $count_approved_courses * 150;
        $puntos_promedio = $grade_average * 100;
        $puntos_intentos = $attempts * 0.5;

        return $puntos_promedio + $puntos_cursos - $puntos_intentos;
    }
}
