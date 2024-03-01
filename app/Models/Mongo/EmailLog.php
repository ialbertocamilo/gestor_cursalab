<?php

namespace App\Models\Mongo;

use Carbon\Carbon;
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
    //save reminders emails
    protected function formatToSaveEmail($users,$subject,$template,$type_email,$reminders_configuration,$status='programmed'){
        
        //programar emails
        $init_time = Carbon::today()->startOfDay()->addHours(6); // 6am
        // $total_minutes = 720; // 6am to 6pm in minutes
        $count_users = count($users); 
        // $bloks_to_sent = (int) round($count_users /match (true) {
        //     $count_users < 20 =>  $count_users,
        //     $count_users > 20 && $count_users <= 100 => 10,
        //     $count_users > 100 && $count_users <= 500 => 20,
        //     $count_users > 500 => 40,
        //     default => 20,
        // });
        $bloks_to_sent = $reminders_configuration['chunk'];
        // $range_in_minutes = (int) round($total_minutes/$bloks_to_sent);
        $range_in_minutes = $reminders_configuration['interval'];
        $chunk_users = array_chunk($users,$bloks_to_sent);
        
        $_users=[];
        $firstElement = true;
        $web_url = config('app.web_url');
        $_users=[];
        foreach ($chunk_users as $chunk_user) {
            //first block send at 6:00 am
            $add_minutes = $firstElement ? 0 : $range_in_minutes;
            $init_time = $init_time->addMinutes($add_minutes);
            //Evitar la sobrecarga de envio de correos por minuto de mailersend
            $second_level_range_in_minutes = (int) round($add_minutes/count($chunk_user));
            $second_level_range_in_minutes = $second_level_range_in_minutes == 0 ? 1 : $second_level_range_in_minutes;
            $chunk_users_by_minuts = array_chunk($chunk_user,$range_in_minutes);
            foreach ($chunk_users_by_minuts as $users_by_minuts) {
                $init_time->addMinutes($second_level_range_in_minutes);
            
                foreach ($users_by_minuts as $user) {
                    $_users[]=[
                        'user_email' => $user['email'],
                        'email_data' => [
                            'subject' => $subject,
                            'courses' => $user['courses'],
                            'user_id' => $user['user_id'],
                            'workspace_id' => $user['workspace_id'],
                            'subworkspace_id' => $user['subworkspace_id'],
                            'web_url'=> $web_url,
                            'image_logo' => $user['image_logo']
                        ],
                        'time' => $init_time->format('Y-m-d H:i'),
                        'email_subject' =>  $subject,
                        'template' => $template,
                        'type_email'=> $type_email,
                        'status' => $status
                    ];
                }
            }
            $firstElement = false;
        }
        return $_users;
    }
}
