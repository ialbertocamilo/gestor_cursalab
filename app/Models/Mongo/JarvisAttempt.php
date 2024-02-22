<?php

namespace App\Models\Mongo;
use Jenssegers\Mongodb\Eloquent\Model;
class JarvisAttempt extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'jarvis_attempts';
    protected $fillable = [
        'workspace_id', 
        'type',
        'attempts',
        'historics'
    ];
    public static function getAttempt($workspace_id,$type='evaluations'){
        $attempt_workspace = self::where('workspace_id',$workspace_id)->where('type',$type)->select('attempts')->first();
        return $attempt_workspace?->attempts ?? 0;
    }

    protected function increaseAttempt($workspace_id,$type = 'evaluations') {
        $filtro = [
            "workspace_id" => $workspace_id,
            "type" => $type
        ];
    
        $operacion_actualizacion = [
            '$inc' => ["attempts" => 1]
        ];
    
        $resultado = JarvisAttempt::where($filtro)->first();
    
        if ($resultado) {
            // Si el documento existe, actualiza el número de intentos
            JarvisAttempt::where($filtro)->increment('attempts');
        } else {
            // Si el documento no existe, crea uno nuevo y establece el número de intentos a 1
            $nuevoIntento = new JarvisAttempt([
                'workspace_id' => $workspace_id,
                'type' => $type,
                'attempts' => 1
            ]);
            $nuevoIntento->save();
        }
    }
}
