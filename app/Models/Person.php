<?php

namespace App\Models;

use App\Models\Error;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;



class Person extends BaseModel
{
    use HasFactory;

    protected $fillable = ['workspace_id','person_attributes','type'];
    //TYPES
    public const INSTRUCTOR = 'dc3-instructor';
    public const LEGAL_REPRESENTATIVE = 'dc3-legal-representative';

    protected $casts = [
        'person_attributes' => 'json'
    ];
    protected function storeDataRequest($data){
        try {
            $workspace = get_current_workspace();
            $person = new Person();
            $person->workspace_id = $workspace->id;
            if($data['type'] == PERSON::INSTRUCTOR || $data['type'] == PERSON::LEGAL_REPRESENTATIVE){
                if(isset($data['file_signature'])){
                    $media = Media::requestUploadFile($data, 'signature',true);
                    $signature_file_id = $media['signature']['id'];
                    $signature_file = $media['signature']['file'];
                }else{
                    $media = Media::where('file',$data['signature'])->select('id')->first();
                    $signature_file_id = $media?->id;
                    $signature_file = $data['signature'];
                }
                $person->person_attributes = [
                    'name' => $data['name'],
                    'signature_file_id' => $signature_file_id,
                    'signature_file' => $signature_file,
                ];
            }
            $person->type = $data['type'];
            $person->save();
            return $person;
        } catch (\Exception $e) {
            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }

}
