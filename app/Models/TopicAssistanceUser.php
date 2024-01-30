<?php

namespace App\Models;

use App\Models\BaseModel;
// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TopicAssistanceUser extends BaseModel
{
    use HasFactory;

    protected $table = 'topic_assistance_user';

    protected $fillable = [
        'id','topic_id','user_id','status_id','date_assistance','historic_assistance','updated_at','created_at','deleted_at'
    ];

    protected $casts = [
        'historic_assistance' => 'array',
    ];

    protected function insertUpdateMassive($topic_assistance_user,$type){
        $users_chunk = array_chunk($topic_assistance_user,250);
        foreach ($users_chunk as $users) {
            if($type == 'insert'){
                self::insert($users);
            }
            if($type == 'update'){
                batch()->update(new TopicAssistanceUser, $topic_assistance_user, 'id');
            }
        }
    }

    protected function assistance($topic_id,$user_ids){
        return self::
            select('id','topic_id','user_id','status_id','date_assistance','historic_assistance')
            ->where('topic_id',$topic_id)
            ->whereIn('user_id',$user_ids)
            ->get();
    }

    protected function listUserWithAssistance($users,$topic_id,$codes_taxonomy){
        $assistance_users = self::assistance($topic_id,$users->pluck('id'));
        return $users->map(function($user) use ($codes_taxonomy,$assistance_users){
            $user_has_assistance = $assistance_users->where('user_id',$user->id)->first();
            $status = null;
            if($user_has_assistance){
                $status = $codes_taxonomy->where('id',$user_has_assistance->status_id)->first()?->code;
            }
            return [
                'id' => $user->id,
                'name' => $user->name,
                'document' => $user->document,
                'lastname' => $user->lastname,
                'surname' => $user->surname,
                'status' => $status
            ];
        });
    }
}
