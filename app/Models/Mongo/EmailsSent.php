<?php

namespace App\Models\Mongo;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

class EmailsSent extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'emails_sent';

    protected function storeEmailSent($data_sent,$type){
        $emails_sent = new EmailsSent();
        $emails_sent->data_sent = $data_sent;
        $emails_sent->type = $type;
        $emails_sent->save();
    }

    //function to validate code in guest_code_verification type email sent
    protected function verifyCode($request){
        $email_sent = EmailsSent::where($request->email)->where('type','guest_code_verification')->orderby('created_at','desc')->first();
        if($email_sent['data_sent']['code'] != $request->code){
            return [
                'verified'=>false,
                'message' => 'El código que has indicado es incorrecto  si deseas puedes volver a ingresarlo  o reenviar el código.'
            ];
        }
        if(!($email_sent['data_sent']['expires_code'] > now())){
            return [
                'verified'=>false,
                'message' => 'El código esta expirado.'
            ];
        }
        return [
            'verified'=>true,
            'message' => 'El código es correcto.'
        ];
    }
}
?>