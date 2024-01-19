<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RegistroCapacitacionTrainer extends BaseModel
{
    use SoftDeletes;

    protected $table = 'registro_capacitacion_trainers';

    protected $fillable = ['name', 'workspace_id', 'signature'];

    //
    // Mutators and accesors
    // ========================================

    public function setSignatureAttribute($value)
    {
        $this->attributes['signature'] = json_encode($value);
    }

    public function getSignatureAttribute($value)
    {
        return $value ? json_decode($value) : json_decode('{}');
    }

    protected function storeDataRequest($data) {

        try {

            if (isset($data['file_signature'])) {
                $media = Media::requestUploadFile($data, 'signature',true);
                $signature_file_id = $media['signature']['id'];
                $signature_file = $media['signature']['file'];
            } else {
                $media = Media::where('file',$data['signature'])->select('id')->first();
                $signature_file_id = $media?->id;
                $signature_file = $data['signature'];
            }

            $trainer = new RegistroCapacitacionTrainer();
            $trainer->workspace_id = get_current_workspace()->id;
            $trainer->name = $data['name'];
            $trainer->signature = [
                'media_id' => $signature_file_id,
                'path' => $signature_file
            ];

            $trainer->save();

            return $trainer;

        } catch (\Exception $e) {
            info($e);
            DB::rollBack();
            Error::storeAndNotificateException($e, request());
            abort(errorExceptionServer());
        }
    }
}
