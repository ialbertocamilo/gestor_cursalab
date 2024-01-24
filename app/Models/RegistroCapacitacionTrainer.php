<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

            $url = '';
            if (isset($data['file_signature'])) {

                $file = $data['file_signature'];
                $filename = Str::random(20);
                $ext = $file->getClientOriginalExtension();
                $path = "/signatures/$filename.$ext";
                $url = Course::generateRegistroCapacitacionURL($path);
                $result = Storage::disk('s3')->put($path, file_get_contents($file), 'public');
                info($result);
            }

            $trainer = new RegistroCapacitacionTrainer();
            $trainer->workspace_id = get_current_workspace()->id;
            $trainer->name = $data['name'];
            $trainer->signature = [
                'path' => $url
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
