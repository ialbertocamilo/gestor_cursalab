<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\DatabaseNotification;

use Illuminate\Support\Facades\Notification AS LaravelNotification;
use App\Notifications\SystemNotification;

/**
 * App\Models\Notification
 */
class Notification extends DatabaseNotification
{
    // protected $rememberFor = WEEK_MINUTES;

    // protected $fillable = ['parent_id', 'name', 'slug', 'description', 'type', 'percentage', 'quantity'];
    // protected $fillable = [];
    protected $guarded = [];

    protected $dates = ['read_at'];

    // protected $hidden=[
    //   'pivot'
    // ];

    // protected $with = ['children', 'parent'];
    // protected $cast = ['data' =>  'object'];


    // public function type()
    // {
    //    return $this->belongsTo(Taxonomy::class, 'type_id');
    // }


    protected function sendMessage($code, $data = null)
    {
        $notification = NotificationType::with('recipients', 'type')->where('code', $code)->where('active', ACTIVE)->first();

        if (!$notification)
            return false;

        foreach ($notification->recipients as $key => $recipient)
        {
            $users = User::whereHas('roles', function ($q) use ($recipient){
                                $q->where('roles.id', $recipient->role_id);
                            })
                            ->when($data['user_id'] ?? null, function($q) use ($data){
                                $q->where('id', $data['user_id']);
                            })
                            ->get();

            LaravelNotification::send($users, new SystemNotification($recipient, $data));
        }

        return true;
    }
}
