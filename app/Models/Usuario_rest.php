<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario_rest extends Authenticatable implements JWTSubject, Auditable
{
    use Notifiable;
    
    protected $table = 'usuarios';

    protected $fillable = ['email', 'password'];
    // Rest omitted for brevity

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Route notifications for the Slack channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return config('slack.routes.support');
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function matriculas()
    {
        return $this->hasMany(Matricula::class, 'usuario_id');
    }

    /* AUDIT TAGS */
    public function generateTags(): array
    {
        return [
            'modelo_independiente',
        ];
    }
    /* AUDIT TAGS */
}
