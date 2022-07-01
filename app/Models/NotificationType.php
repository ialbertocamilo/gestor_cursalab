<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Support\HasManySyncable;


class NotificationType extends BaseModel
{
    // protected $rememberFor = WEEK_MINUTES;
    protected $table = 'notifications_types';

    protected $fillable = ['code', 'name', 'active', 'description', 'title', 'message', 'type_id', 'model_id', 'platform_id'];

    public $defaultRelationships = ['type_id' => 'type', 'model_id' => 'model', 'platform_id' => 'platform'];

    // protected $hidden=[
    //   'pivot'
    // ];

    // protected $with = ['children', 'parent'];

    public function sluggable(): array
    {
       return [
           'code' => [ 'source' => 'name', 'onUpdate' => false, 'unique' => true]
       ];
    }

    public function type()
    {
       return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function model()
    {
       return $this->belongsTo(Taxonomy::class, 'model_id');
    }

    public function platform()
    {
       return $this->belongsTo(Taxonomy::class, 'platform_id');
    }

    public function recipients()
    {
        return $this->hasManySync(NotificationRecipient::class, 'notification_type_id');
       // return $this->belongsToMany(Taxonomy::class, 'notifications_recipients', 'notification_type_id', 'platform_id')
                    // ->withPivot('role_id', 'channel_id', 'title', 'message');
    }

    public function setCodeAttribute($value = '')
    {
        if ( ! empty ($value) )
        {
            $this->attributes['code'] = $value;
        }
    }

    protected function prepareRecipientsData($recipients = [])
    {
        $data = [];

        $platforms = Role::whereNotIn('name', ['superadmin', 'developer'])->get()->groupBy('platform.code');
        $channels = Taxonomy::where('group', 'system')->where('type', 'channel')->get();

        foreach ($platforms as $key => $platform)
        {
            foreach ($platform as $role)
            {
                $row = [
                    'platform_id' => $role->platform->id,
                    'role_id' => $role->id,
                    'name' => $role->title,
                    'value' => $this->checkIfRoleIsChecked($role, $recipients),
                ];

                foreach ($channels as $channel)
                {
                    $ch = $this->checkIfChannelIsChecked($role, $channel, $recipients);

                    $row['channels'][] = [
                        'channel_id' => $channel->id,
                        'name' => $channel->name,
                        'value' => $ch ? true : false,
                        'id' => $ch->id ?? null,
                    ];
                }

                $data[$key][] = $row;
            }
        } 

        return $data;
    }

    public function checkIfRoleIsChecked($role, $recipients = [])
    {
        if ( ! $recipients )
            return false;

        $role = $recipients->where('role_id', $role->id)->first();

        return $role ? true : false;
    }

    public function checkIfChannelIsChecked($role, $channel, $recipients = [])
    {
        if ( ! $recipients )
            return false;

        $channel = $recipients->where('role_id', $role->id)->where('channel_id', $channel->id)->first();

        return $channel;
    }

    // public function children()
    // {
    //     return $this->hasMany(Taxonomy::class, 'parent_id')->orderBy('position')->with('children');
    // }
}
