<?php

namespace App\Models;

// use Laravel\Sanctum\HasApiTokens;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
use DB;

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
        'document_number', 'ruc',
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
        return $this->getDeviceTokens();
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

    // public function getImageAttribute()
    // {
    //     $image = $this->getFirstMediaToForm('logo');

    //     return $image['media_placeholder'] ?? '';
    // }

    public function getAgeAttribute()
    {
        if ( isset($this->attributes['birthdate']) )
            return Carbon::parse($this->attributes['birthdate'])->age;

        return null;
    }

    public function setPasswordAttribute($value)
    {
        if ( ! empty ($value) )
            $this->attributes['password'] = bcrypt($value);
    }

    public function setActiveAttribute($value)
    {
        $this->attributes['active'] = ($value==='1' OR $value === 1);
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



    protected function storeRequestFull($data = [], $user = null)
    {
        try {

            DB::beginTransaction();

            if ( $user ) :

                $user->update($data);

            else:

                $user = $this->create($data);

            endif;

            if ($user->isNotA('superadmin') AND $user->isNotA('developer'))
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
        $query = self::with('job_position', 'country', 'type', 'gender', 'media');

        if ($request->filters)
        {
            foreach($request->filters AS $key => $filter)
            {
                if ($key == 'q')
                {
                    $query->where(function($q) use ($filter){
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
}
