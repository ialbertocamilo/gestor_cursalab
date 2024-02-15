<?php

namespace App\Models;

use Carbon\Carbon;
// use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TopicAssistanceUser extends BaseModel
{
    use HasFactory;

    protected $table = 'topic_assistance_user';

    protected $fillable = [
        'id','topic_id','user_id','status_id','date_assistance','historic_assistance','signature','updated_at','created_at','deleted_at'
    ];

    protected $casts = [
        'historic_assistance' => 'array',
    ];
    public function status()
    {
        return $this->belongsTo(Taxonomy::class, 'status_id');
    }
    protected function generatePDFDownload($course_id,$topic_id){
        $required_signature = Course::where('id',$course_id)->select('modality_in_person_properties')->first()
                                ?->modality_in_person_properties?->required_signature;
        $assigned = CourseInPerson::listUsersBySession($course_id, $topic_id, 'all',null,false,$required_signature);

        $topic =  Topic::where('id',$topic_id)->select('name','modality_in_person_properties')->first();
        $modality_in_person_properties = $topic->modality_in_person_properties;
        $host = User::select('name','lastname','surname')->where('id',$modality_in_person_properties->host_id)->first();
        $start_datetime = Carbon::createFromFormat('Y-m-d H:i',$modality_in_person_properties->start_date.' '.$modality_in_person_properties->start_time);
        $finish_datetime = Carbon::createFromFormat('Y-m-d H:i',$modality_in_person_properties->start_date.' '.$modality_in_person_properties->finish_time);
        $diff = $finish_datetime->diff($start_datetime);
        $duration = sprintf('%02d:%02d', $diff->h, $diff->i);
        $data = [
            'users' => $assigned['users'],
            'required_signature' => $required_signature,
            'colspan' => $required_signature ? '4' : '3',
            'name_session'=>$topic->name,
            'datetime'=>$modality_in_person_properties->start_date.' '.$modality_in_person_properties->start_time,
            'host' => $host->name.' '.$host->lastname.' '.$host->surname,
            'duration'=>$duration
        ];
        $name_pdf = Str::slug('listado-de-asistencia-'.$topic->name);
        $pdf = PDF::loadView('pdf.report-assistance', $data);
        return $pdf->download($name_pdf);
    }
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
            select('id','topic_id','user_id','status_id','date_assistance','signature','historic_assistance')
            ->where('topic_id',$topic_id)
            ->whereIn('user_id',$user_ids)
            ->get();
    }
    protected function userIsPresent($user_id,$topic_id){
        return self::where('user_id',$user_id)
                ->where('topic_id',$topic_id)
                ->whereHas('status', fn($q) => $q->whereIn('code',['attended','late']))
                ->first();
    }
    protected function listUserWithAssistance($users,$topic_id,$codes_taxonomy,$maskDocument=true,$signature=false){
        $assistance_users = self::assistance($topic_id,$users->pluck('id'));
        return $users->map(function($user) use ($codes_taxonomy,$assistance_users,$maskDocument,$signature){
            $user_has_assistance = $assistance_users->where('user_id',$user->id)->first();
            $status_code = null;
            $status_name = null;
            if($user_has_assistance){
                $status = $codes_taxonomy->where('id',$user_has_assistance->status_id)->first();
                $status_code = $status?->code;
                $status_name = $status?->name;
            }
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'lastname' => $user->lastname,
                'surname' => $user->surname,
                'fullname' => $user->name.' '.$user->lastname.' '.$user->surname,
                'document' => $maskDocument ? $this->maskDocument($user->document) : $user->document,
                'status' => $status_code,
                'status_name' => $status_name,
            ];
            if($signature){
                $data['signature'] = ($user_has_assistance?->signature) ? reportsSignedUrl($user_has_assistance?->signature) : '';
            }
            return $data;
        });
    }

    private function maskDocument($document){
        $document = $document;
        $len_document = strlen($document);
        $asterisks = str_repeat('*', $len_document - 3); 
        $last_3_digits = substr($document, -3);
        return $asterisks . $last_3_digits;
    }
    
}
