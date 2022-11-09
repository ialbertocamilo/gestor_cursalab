<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use App\Events\MassiveUploadProgressEvent;
use Illuminate\Validation\ValidationException;

class Massive
{
    function verifyConstraintMassive($type,$count_rows,$message=null){
        $max_upload_rows_size = config('massive.max-uploads')->where('type',$type)->first();
        if(isset($max_upload_rows_size['max']) && $count_rows > $max_upload_rows_size['max']){
            if(!$message){
                $message = 'Estás subiendo '.$count_rows.' filas, la cantidad máxima permitida es de '.$max_upload_rows_size['max'].' por archivo.';
            }
            throw ValidationException::withMessages(compact('message'));
         }
    }
    //Example: 'upload-topic-grades.14.847917
    function formatNameSocket($type,$number_socket){
        if($number_socket){
            return $type.'.'.Auth::user()->id.'.'.$number_socket;
        }
    }
    
    function sendEchoPercentEvent($currente_percent,$name_socket,$percent_sent){
        if(!$name_socket){
            return false;
        }
        if(($currente_percent==0 ||($currente_percent % 5) == 0) && !in_array($currente_percent,$percent_sent)){
            try {
                info($currente_percent);
                event(new MassiveUploadProgressEvent($name_socket,$currente_percent));
                return true;
            } catch (\Throwable $th) {
                return true;
            }
        }
    }
}
