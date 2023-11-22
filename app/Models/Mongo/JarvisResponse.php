<?php

namespace App\Models\Mongo;
use Jenssegers\Mongodb\Eloquent\Model;
class JarvisResponse extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'jarvis_responses';

    public static function insertResponse($responses,$type){
        foreach ($responses as $response) {
            $response['workspace_id'] = get_current_workspace()?->id;
            $response['user_id'] = auth()->user()->id;
            $response['type'] = $type;
            JarvisResponse::insert($response);
        }
    }
}
