<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends BaseModel
{
    // protected $rememberFor = WEEK_MINUTES;
    protected $table = 'notifications_recipients';

    // protected $fillable = ['parent_id', 'name', 'slug', 'description', 'type', 'percentage', 'quantity'];
    protected $guarded = [];
    protected $forceDeleting  = true;

    public static function bootSoftDeletes() {}

    // public $defaultRelationships = ['type_id' => 'type', 'model_id' => 'model', 'platform_id' => 'platform'];

    // protected $hidden=[
    //   'pivot'
    // ];

   protected $with = ['role', 'channel', 'platform', 'notification_type'];

    public function role()
    {
       return $this->belongsTo(Role::class);
    }

    public function channel()
    {
       return $this->belongsTo(Taxonomy::class, 'channel_id');
    }

    public function platform()
    {
       return $this->belongsTo(Taxonomy::class, 'platform_id');
    }

    public function notification_type()
    {
       return $this->belongsTo(NotificationType::class, 'notification_type_id');
    }

    // public function children()
    // {
    //     return $this->hasMany(Taxonomy::class, 'parent_id')->orderBy('position')->with('children');
    // }
}
