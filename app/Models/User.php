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
        'name', 'lastname', 'surname', 'username', 'slug', 'alias',
        'email', 'password', 'active', 'phone', 'telephone', 'birthdate',
        'type_id', 'job_position_id', 'area_id', 'gender_id', 'document_type_id',
        'document', 'ruc',
        'country_id', 'district_id', 'address', 'description', 'quote',
        'external_id', 'fcm_token',
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
        return $notification->webhookUrl ?? config('slack.routes.general');
    }

    public function criterion_values()
    {
        return $this->belongsToMany(CriterionValue::class);
    }

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function getFullnameAttribute()
    {
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
        if (!empty ($value))
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

    public function user_relations()
    {
        return $this->belongsToMany(User::class);
    }

    // public function post()
    // {
    //     return $this->hasMany(Post::class);
    // }

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    // public function job_position()
    // {
    //    return $this->belongsTo(Taxonomy::class, 'job_position_id');
    // }

    // public function area()
    // {
    //    return $this->belongsTo(Taxonomy::class, 'area_id');
    // }

    // public function gender()
    // {
    //    return $this->belongsTo(Taxonomy::class, 'gender_id');
    // }

    // public function document_type()
    // {
    //    return $this->belongsTo(Taxonomy::class, 'document_type_id');
    // }

    // public function country()
    // {
    //    return $this->belongsTo(Country::class);
    // }

    // public function district()
    // {
    //    return $this->belongsTo(Taxonomy::class, 'district_id');
    // }

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

    public function isSupervisor() : bool
    {
        return !!$this->user_relations()->where('relation_type_id', 'entrenador')->first();
    }

    protected function storeRequest($data, $user = null)
    {
        try {

            DB::beginTransaction();

            if ($user) :

                $user->update($data);

            else:

                $user = self::create($data);

            endif;

            $user->criterion_values()->sync(array_values($data['criterion_list']) ?? []);

            DB::commit();

        } catch (\Exception $e) {
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

            else:

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

        if ($request->q) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%$request->q%");
                $q->orWhere('lastname', 'like', "%$request->q%");
                $q->orWhere('surname', 'like', "%$request->q%");
                $q->orWhere('email', 'like', "%$request->q%");
            });
        }

        if ($request->filters) {
            foreach ($request->filters as $key => $filter) {
                if ($key == 'q') {
                    $query->where(function ($q) use ($filter) {
                        $q->where('name', 'like', "%$filter%");
                        $q->orWhere('lastname', 'like', "%$filter%");
                        $q->orWhere('surname', 'like', "%$filter%");
                        $q->orWhere('email', 'like', "%$filter%");
                    });
                }

                if ($key == 'type')
                    $query->where('type_id', $filter);

                if ($key == 'country')
                    $query->where('country_id', $filter);

                if ($key == 'job_position')
                    $query->where('job_position_id', $filter);

                if ($key == 'gender')
                    $query->where('gender_id', $filter);

                if ($key == 'area')
                    $query->where('area_id', $filter);

                if ($key == 'starts_at')
                    $query->whereDate('created_at', '>=', $filter);

                if ($key == 'ends_at')
                    $query->whereDate('created_at', '<=', $filter);
            }
        }

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->descending == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort)->orderBy('id', $sort);

        return $query->paginate($request->rowsPerPage);
    }

    public function setCurrentCourses($return_courses_id = false)
    {
        $user = $this;
        $user->load('criterion_values:id,value_text');
        // TODO: No considerar criterion_values que se excluyan (ciclo 0)

        $user_criterion_values_id = $user->criterion_values->pluck('id');
        $all_programs = Block::with('segments.values.criterion_value',
            'block_children.child.segments.values.criterion_value',
            'block_children.child.courses.segments.values.criterion_value'
        )
            ->where('parent', 1)
            ->where('active', ACTIVE)
            ->get();

        $courses_id = [];

        $programs = collect();
        foreach ($all_programs as $program) {

            $program_segment_valid = false;

            if ($program->segments->count() === 0) :

                $program_segment_valid = true;

            else:

                foreach ($program->segments as $segment) {

                    $program_segment_criterion_values_id = $segment->values->pluck('criterion_value_id');
                    $program_segment_valid = $this->validateSegmentationForUser($user_criterion_values_id, $program_segment_criterion_values_id);

                    if ($program_segment_valid) break;
                }

            endif;

            if ($program_segment_valid):

                $blocks = collect();

                foreach ($program->block_children as $block_child) {

                    $block_segment_valid = false;

                    if ($block_child->child->segments->count() === 0) :

                        $block_segment_valid = true;

                    else:

                        foreach ($block_child->child->segments as $segment) {

                            $block_segment_criterion_values_id = $segment->values->pluck('criterion_value_id');
                            $block_segment_valid = $this->validateSegmentationForUser($user_criterion_values_id, $block_segment_criterion_values_id);

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
                                    'position' => $course->position
                                ]);

                                $courses_id[] = $course->id;

                            else:

                                foreach ($course->segments as $segment) {

                                    $course_segment_criterion_values_id = $segment->values->pluck('criterion_value_id');
                                    $course_segment_valid = $this->validateSegmentationForUser($user_criterion_values_id, $course_segment_criterion_values_id);

                                    if ($course_segment_valid) :

                                        $courses->push([
                                            'id' => $course->id,
                                            'name' => $course->name,
                                            'position' => $course->position
                                        ]);

                                        $courses_id[] = $course->id;
                                        break;

                                    endif;
                                }

                            endif;
                        }

                        $blocks->push([
                            'id' => $block_child->child->id,
                            'name' => $block_child->child->name,
                            'courses_count' => $courses->count(),
                            'courses' => $courses->sortBy('position')->values()->all()
                        ]);
                    endif;

                }
                $programs->push([
                    'id' => $program->id,
                    'name' => $program->name,
                    'blocks' => $blocks,
                ]);

            endif;
        }

        if($return_courses_id) return $courses_id;

        $user->courses = $programs;
//        $user->courses = $courses;
    }

    public function validateSegmentationForUser(Collection $user_criterion_values, Collection $segment_values): bool
    {
        $intersection = $user_criterion_values->intersect($segment_values);

        return $intersection->count() === $segment_values->count();
    }

    public function updateUserDeviceVersion()
    {

    }

}
