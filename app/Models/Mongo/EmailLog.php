<?php

namespace App\Models\Mongo;

use Jenssegers\Mongodb\Eloquent\Model;


class EmailLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'emails_log';
    protected $fillable = ['user_email','email_data','email_subject','template','type_email','status'];
    protected $casts = ['created_at' => 'datetime'];
    protected function insertEmail($data,$type_email,$template,$email,$status='no_sent'){
        EmailLog::create([
            'user_email' => $email,
            'email_data' => $data,
            'email_subject' => $data['subject'],
            'template' => $template,
            'type_email' => $type_email,
            'status' => $status,
        ]);
    }
}
