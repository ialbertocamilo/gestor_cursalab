<?php

namespace App\Models;

// use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\UCMigrationData\Migration_1;
use App\Notifications\UserResetPasswordNotification;
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

    use Notifiable;

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
        'name', 'lastname', 'surname', 'username', 'fullname', 'slug', 'alias', 'person_number', 'phone_number',
        'email', 'password', 'active', 'phone', 'telephone', 'birthdate',
        'type_id', 'subworkspace_id', 'job_position_id', 'area_id', 'gender_id', 'document_type_id',
        'document', 'ruc',
        'country_id', 'district_id', 'address', 'description', 'quote',
        'external_id', 'fcm_token', 'token_firebase', 'secret_key',
        'user_relations',
        'summary_user_update', 'summary_course_update', 'summary_course_data', 'required_update_at', 'last_summary_updated_at', 'is_updating'
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
        'user_relations' => 'array'
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

    public function students()
    {
        return $this->belongsToMany(
            User::class,
            'trainer_user',
            'trainer_id',
            'user_id');
    }

    public function criterion_values()
    {
        return $this->belongsToMany(CriterionValue::class);
    }

    public function criterion_user()
    {
        return $this->hasMany(CriterionValueUser::class);
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

    public function failed_topics()
    {
        return $this->hasMany(SummaryTopic::class, 'user_id')
            ->where('passed', 0)
            ->whereNotNull('attempts')
            ->where('attempts', '<>', 0);
    }

    public function relationships()
    {
        return $this->hasMany(UserRelationship::class, 'user_id');
    }

    public function segments()
    {
        return $this->morphMany(Segment::class, 'model');
    }

    public function scopeOnlyClientUsers($q)
    {
        $q
            ->whereHas('type', fn($q) => $q->whereNotIn('code', ['cursalab']));
//            ->whereNotNull('subworkspace_id');
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
        $this->attributes['active'] = ($value === 'true' or $value === true or $value === 1 or $value === '1') ? 1 : 0;
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
        $userWithSupervisorSegment = User::loadSupervisorWithSegment($this->id);
        return $userWithSupervisorSegment['segment'] !== null;
    }

    public function getActiveCycle()
    {
        $user = $this;
        $user_cycles = $user->criterion_values()
            ->whereRelation('criterion', 'code', 'cycle')
            ->orderBy('position')
            ->get();

        //        info("CICLOS DEL USUARIO {$user->fullname}");
        //        info($user_cycles->pluck('value_text'));
        //        info($user->criterion_values()->pluck('value_text'));

        return $user_cycles->last();
    }

    /**
     * Load supervisor user and its segment
     * @param $userId
     * @return array
     */
    public static function loadSupervisorWithSegment($userId): array
    {
        $supervisorTaxonomy = Taxonomy::where('type', 'code')
            ->where('group', 'segment')
            ->where('code', 'user-supervise')
            ->where('active', 1)
            ->first();

        $supervisorSegment = Segment::where('model_type', 'App\Models\User')
            ->where('model_id', $userId)
            ->where('code_id', $supervisorTaxonomy->id)
            ->where('active', 1)
            ->first();

        return [
            'user' => User::find($userId),
            'segment' => $supervisorSegment
        ];
    }

    public function getTrainingRole()
    {
        $user = $this;

        $is_student = EntrenadorUsuario::alumno($user->id)->first();
        $is_trainer = EntrenadorUsuario::entrenador($user->id)->first();
        if (!is_null($is_student)) {
            return 'student';
        } else if (!is_null($is_trainer)) {
            return 'trainer';
        } else {
            return null;
        }
    }

    public function updateStatusUser($active = null, $termination_date = null)
    {
        $user = $this;
        $user->active = $active ? $active : !$user->active;
        $user->save();
        $criterion = Criterion::with('field_type:id,code')->where('code', 'termination_date')->select('id', 'field_id')->first();
        if (!$criterion) {
            return true;
        }
        $user_criterion = $user->criterion_values()->where('criterion_id', $criterion->id)->detach();
        if ($termination_date && !$user->active) {
            $criterion_value = CriterionValue::where('criterion_id', $criterion->id)->where('value_text', trim($termination_date))->select('id', 'value_text')->first();
            $colum_name = CriterionValue::getCriterionValueColumnNameByCriterion($criterion);
            if (!$criterion_value) {
                $data_criterion_value[$colum_name] = $termination_date;
                $data_criterion_value['value_text'] = $termination_date;
                $data_criterion_value['criterion_id'] = $criterion->id;
                $data_criterion_value['active'] = 1;
                $data_criterion_value['workspace_id'] = $user->subworkspace?->parent?->id;
                $criterion_value = CriterionValue::storeRequest($data_criterion_value);
            }
            $user_criterion = $user->criterion_values()->where('criterion_id', $criterion->id)->detach();
            $user->criterion_values()->syncWithoutDetaching([$criterion_value->id]);
        }
    }

    protected function storeRequest($data, $user = null, $update_password = true,$from_massive=false)
    {
        try {

            DB::beginTransaction();
            $old_document = $user ? $user->document : null;
            if ($user) :
                if (!$update_password && isset($data['password'])) {
                    unset($data['password']);
                }
                $user->update($data);
                if(!$from_massive){
                    SummaryUser::updateUserData($user);
                }
                if ($user->wasChanged('document') && ($data['document'] ?? false)):
                    $user_document = $this->syncDocumentCriterionValue(old_document: $old_document, new_document: $data['document']);
                else:
                    $user_document = CriterionValue::whereRelation('criterion', 'code', 'document')
                        ->where('value_text', $old_document)->first();
                    if(!$user_document){
                        $user_document = $this->syncDocumentCriterionValue(old_document: null, new_document: $old_document);
                    }
                endif;
            else :
                $data['type_id'] = $data['type_id'] ?? Taxonomy::getFirstData('user', 'type', 'employee')->id;

                $user = self::create($data);
                $user_document = $this->syncDocumentCriterionValue(old_document: null, new_document: $data['document']);
            endif;

            $user->subworkspace_id = Workspace::query()
                ->where('criterion_value_id', $data['criterion_list']['module'])
                ->first()
                ?->id;

            $user->criterion_values()
                ->sync(array_values($data['criterion_list_final']) ?? []);

            $data['criterion_list_final'][] = $user_document->id;

            $user->criterion_values()
                ->sync($data['criterion_list_final']);

            $user->save();
            DB::commit();
        } catch (\Exception $e) {

            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }

    public function syncDocumentCriterionValue($old_document, $new_document)
    {
        $document_criterion = Criterion::where('code', 'document')->first();

        $document_value = CriterionValue::whereRelation('criterion', 'code', 'document')
            ->where('value_text', $old_document)->first();

        $criterion_value_data = [
            'value_text' => $new_document,
            'criterion_id' => $document_criterion?->id,
            'active' => ACTIVE
        ];

        $document_value = CriterionValue::storeRequest($criterion_value_data, $document_value);

        return $document_value;
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

    protected function search($request, $withAdvancedFilters = false)
    {
        $query = self::onlyClientUsers();
        if (auth()->user()->isA('super-user')){
            $query = self::query();
        }

        $with = ['subworkspace'];

        if (get_current_workspace()->id == 25) {

            $with = ['subworkspace', 'criterion_values' => function ($q) {
                $q->whereIn('criterion_id', [40, 41]);
            }];
        }

        $query->with($with)->withCount('failed_topics');

        if ($request->q)
            $query->filterText($request->q);

        if ($request->active == 1)
            $query->where('active', ACTIVE);

        if ($request->active == 2)
            $query->where('active', '<>', ACTIVE);

        if ($request->workspace_id)
            $query->whereRelation('subworkspace', 'parent_id', $request->workspace_id);

        if ($request->subworkspace_id)
            $query->where('subworkspace_id', $request->subworkspace_id);

        if ($request->sub_workspaces_id)
            $query->whereIn('subworkspace_id', $request->sub_workspaces_id);

        if ($withAdvancedFilters):
            $workspace = get_current_workspace();

            $criteria_template = Criterion::select('id', 'name', 'field_id', 'code', 'multiple')
                ->with('field_type:id,name,code')
                ->whereRelation('workspaces', 'id', $workspace->id)
                ->where('is_default', INACTIVE)
                ->orderBy('name')
                ->get();

            foreach ($criteria_template as $i => $criterion) {
                $idx = $i;

                if ($request->has($criterion->code)) {
                    $code = $criterion->code;
                    $request_data = $request->$code;

                    $query->join("criterion_value_user as cvu{$idx}", function ($join) use ($request_data, $idx) {

                        $request_data = is_array($request_data) ? $request_data : [$request_data];

                        $join->on('users.id', '=', "cvu{$idx}" . '.user_id')
                            ->whereIn("cvu{$idx}" . '.criterion_value_id', $request_data);
                    });

                }
            }
        endif;

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->descending == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort)->orderBy('id', $sort);

        return $query->paginate($request->paginate);
        // return $query->paginate($request->rowsPerPage);
    }

//    public function getCurrentCourses($with_programs = true, $with_direct_segmentation = true, $withFreeCourses = true, $withRelations = 'default', $only_ids = false)
//    {
//        $user = $this;
//        $user->load('criterion_values:id,value_text,criterion_id');
////        $programs = collect();
//        $all_courses = [];
//
////        if ($with_programs) $this->setProgramCourses($user, $all_courses);
//
//        // TODO: Agregar segmentacion directa
//        if ($with_direct_segmentation) $this->setCoursesWithDirectSegmentation($user, $all_courses, $withFreeCourses, $withRelations);
//
//        return $only_ids
//            ? array_unique(array_column($all_courses, 'id'))
//            : collect($all_courses)->unique()->values();
//    }

    public function getCurrentCourses(
        $with_programs = true,
        $with_direct_segmentation = true,
        $withFreeCourses = true,
        $withRelations = 'default',
        $only_ids = false)
    {
        $user = $this;
        $user->load('criterion_values:id,value_text,criterion_id');

        $all_courses = [];

//        if ($with_programs) $this->setProgramCourses($user, $all_courses);

        if ($with_direct_segmentation)
            $this->setCoursesWithDirectSegmentation($user, $all_courses, $withFreeCourses);

        if ($only_ids)
            return array_unique(array_column($all_courses, 'id'));

        $query = $this->getUserCourseSegmentationQuery($withRelations);

        return $query->whereIn('id', array_column($all_courses, 'id'))->get();
    }

    private function getUserCourseSegmentationQuery($withRelations)
    {
        // $relations = config("courses.user-courses-query.$withRelations");
        $user_id =  auth()->user() ?  auth()->user()->id : $this->id;
        $relations = config("courses.user-courses-query")($withRelations,$user_id);
        return Course::with($relations);
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

    public function setCoursesWithDirectSegmentation($user, &$all_courses, $withFreeCourses)
    {
        $user->loadMissing('subworkspace.parent');

        $workspace = $user->subworkspace->parent;

        $query = $this->getUserCourseSegmentationQuery('soft');

        $course_segmentations = $query->whereRelation('schools', 'active', ACTIVE)
            ->whereRelation('segments', 'active', ACTIVE)
            ->whereRelation('topics', 'active', ACTIVE)
            ->whereRelation('workspaces', 'id', $workspace->id)
            ->when(!$withFreeCourses, function ($q) {
                $q->whereRelation('type', 'code', '<>', 'free');
            })
            ->where('active', ACTIVE)
            ->get();

        $user_criteria = $user->criterion_values()->with('criterion.field_type')->get()->groupBy('criterion_id');
//        $user->active_cycle = $user->getActiveCycle();

//        $UC_rules_data = [
//            'criterion_cycle' => Criterion::where('code', 'cycle')->first(),
//            'cycle_0_value' => CriterionValue::whereRelation('criterion', 'code', 'cycle')
//                ->where('value_text', 'Ciclo 0')->first()
//        ];

        foreach ($course_segmentations as $course) {

            foreach ($course->segments as $segment) {
//                dd($segment->values->first());

//                $valid_rule = $this->validateUCCyclesRule($segment, $user, $UC_rules_data);
//
//                if (!$valid_rule) continue;

                $course_segment_criteria = $segment->values->groupBy('criterion_id');

                $valid_segment = Segment::validateSegmentByUserCriteria($user_criteria, $course_segment_criteria);
                //                $valid_segment = Segment::validateSegmentByUserCriteria($user_criteria, $course_segment_criteria, $workspace_criteria);

                if ($valid_segment) :

                    // COMPATIBLE VALIDATION

                    if ($user->subworkspace->parent_id === 25)
                        $course = $course->getCourseCompatibilityByUser($user);
                    
                    $all_courses[] = $course;

                    break;
                endif;
            }
        }
        unset($user->active_cycle);
    }
//    public function setCoursesWithDirectSegmentation($user, &$all_courses, $withFreeCourses, $withRelations)
//    {
//        $user->loadMissing('subworkspace.parent');
//
//        $workspace = $user->subworkspace->parent;
//
//        $query = $this->getUserCourseSegmentationQuery($withRelations, $user);
//
//        $course_segmentations = $query->whereRelation('schools', 'active', ACTIVE)
//            ->whereRelation('segments', 'active', ACTIVE)
//            ->whereRelation('topics', 'active', ACTIVE)
//            ->whereRelation('workspaces', 'id', $workspace->id)
//            ->when(!$withFreeCourses, function ($q) {
//                $q->whereRelation('type', 'code', '<>', 'free');
//            })
//            ->where('active', ACTIVE)
//            ->get();
//
//        //        info("COUNT COURSES :: {$course_segmentations->count()}");
//
//        //        $user_criteria = $user->criterion_values->groupBy('criterion_id');
//        $user_criteria = $user->criterion_values()->with('criterion.field_type')->get()->groupBy('criterion_id');
//        $user->active_cycle = $user->getActiveCycle();
//
//        //        $workspace_criteria = Criterion::whereRelation('workspaces', 'id', $workspace_id)->get();
//
//        foreach ($course_segmentations as $course) {
//
//            foreach ($course->segments as $segment) {
//
//                //                $valid_rule = $this->validateUCCyclesRule($segment, $user);
//                //
//                //                if (!$valid_rule) continue;
//
//                $course_segment_criteria = $segment->values->groupBy('criterion_id');
//
//                $valid_segment = Segment::validateSegmentByUserCriteria($user_criteria, $course_segment_criteria);
//                //                $valid_segment = Segment::validateSegmentByUserCriteria($user_criteria, $course_segment_criteria, $workspace_criteria);
//
//                if ($valid_segment) :
//
//                    // $course = $course->getCourseCompatibilityByUser($user);
//
//                    $all_courses[] = $course;
//
//                    break;
//                endif;
//            }
//        }
//        unset($user->active_cycle);
//    }

    public function validateUCCyclesRule(Segment $segment, $user, $UC_rules_data): bool
    {
        if ($user->subworkspace->parent_id != 25) return true;

        $cycle_criterion = $UC_rules_data['criterion_cycle'];
        $cycle_0 = $UC_rules_data['cycle_0_value'];

        $has_criterion_cycle = $segment->values->where('criterion_id', $cycle_criterion->id)->count() > 0;

        if (!$has_criterion_cycle) return true;

        $user_active_cycle = $user->active_cycle;
        if (!$user_active_cycle) return false;

        $user_active_cycle_Ciclo_0 = $user_active_cycle->value_text === 'Ciclo 0';
        $segment_has_Ciclo_0 = $segment->values->where('criterion_value_id', $cycle_0->id);

        if (
            (!$user_active_cycle_Ciclo_0 && $segment_has_Ciclo_0->count() === 1)
            || ($user_active_cycle_Ciclo_0 && !($segment_has_Ciclo_0->count() > 0))
        )
            return false;

        return true;
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

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
    }

    public static function countActiveUsersInWorkspace($workspaceId)
    {

        return self::join('workspaces', 'users.subworkspace_id', '=', 'workspaces.id')
            ->where('workspaces.parent_id', $workspaceId)
            ->where('users.active', 1)
            ->count();
    }

    public function belongsToSegmentation($model)
    {
        if (!$model) return false;

        $model->load('segments.values');

        $user_criteria = $this->criterion_values()->with('criterion.field_type')->get()->groupBy('criterion_id');

        $valid_segment = false;

        foreach ($model->segments as $key => $segment) {

            $model_segment_criteria = $segment->values->groupBy('criterion_id');

            $valid_segment = Segment::validateSegmentByUserCriteria($user_criteria, $model_segment_criteria);

            if ($valid_segment) break;
        }

        return $valid_segment;
    }

    public function load_ranking_data($criterion_code = null)
    {
        $user = $this;

        $summary_user = SummaryUser::getCurrentRow($user);

        if (!$summary_user) return null;

        $query = SummaryUser::query()
            ->whereRelation('user', 'subworkspace_id', $user->subworkspace_id);

        if ($criterion_code):
            $user_criterion_value = $user->criterion_values()
                ->whereRelation('criterion', 'code', $criterion_code)
                ->first();

            $query->whereHas(
                'user.criterion_values',
                fn($q) => $q
                    ->where('id', $user_criterion_value->id)
            );
        endif;

        $ranks_before_user = $query->whereRelation('user', 'active', ACTIVE)
            ->whereNotNull('last_time_evaluated_at')
            ->where('score', '>=', $summary_user->score ?? 0)
            ->orderBy('score', 'desc')
            ->orderBy('last_time_evaluated_at')
            ->get();

        $row = $ranks_before_user->where('user_id', $user->id)->first();

        return [
            'position' => $ranks_before_user->count(),
            'last_time_evaluated_at' => $row?->last_time_evaluated_at,
            'score' => $row?->score
        ];
    }
}
