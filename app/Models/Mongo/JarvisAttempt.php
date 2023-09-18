<?php

namespace App\Models\Mongo;
use Jenssegers\Mongodb\Eloquent\Model;
class JarvisAttempt extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'jarvis_attempts';
    
    public static function getAttempt($workspace_id){
        $attempt_workspace = self::where('workspace_id',$workspace_id)->select('attempts')->first();
        return $attempt_workspace?->attempts ?? 0;
    }
}
