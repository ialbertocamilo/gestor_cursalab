<?php

namespace App\Models;

use App\Models\Error;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Person extends BaseModel
{
    use HasFactory;

    protected $fillable = ['workspace_id','attributes','type','active'];

    public function storeRequest($data){
        try {
            $workspace = get_current_workspace();
            $data['workspace_id'] = $workspace->id;
            Person::insert($person);
        } catch (\Throwable $th) {
            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }
}
