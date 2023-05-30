<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailUser extends Model
{
    use HasFactory;
    protected $table = 'emails_user';
    protected $fillable = ['user_id','workspace_id','type_id'];
    public $timestamps = false;

    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public static function storeUpdate($data,$user){
        $user->emails_user()->delete();
        $users_email_to_create = [];
        foreach ($data['emailtowk'] as $workspace_id => $email_types) {
            foreach ($email_types as $email_type) {
                if ($email_type !== null) {
                    $email_typeArray = explode(",", $email_type);
                    foreach ($email_typeArray as $email_type) {
                        if($email_type){
                            $users_email_to_create[] = [
                                            'user_id' => $user->id,
                                            'workspace_id' => $workspace_id,
                                            'type_id'=>$email_type
                                        ];
                        }
                    }
                }
            }
        }
        EmailUser::insert($users_email_to_create);
    }
}
