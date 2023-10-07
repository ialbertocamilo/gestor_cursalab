<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jarvis extends Model
{
    use HasFactory;
    protected function generateDescriptionJarvis($request){
        
        $data['name']  = $request->get('name');
        $data['type']  = $request->get('type');
        $data = array_merge(self::getJarvisConfiguration(),$data);
        $response = Http::withOptions(['verify' => false])->timeout(900)->post(
            env('JARVIS_BASE_URL').'/generate_description', $data
        );
        if ($response->successful()) {
            $data = $response->json();
            return $data['description'];
        }
        return $response;
    }

    private function getJarvisConfiguration(){
        $workspace = get_current_workspace();
        $jarvis_configuration = $workspace->jarvis_configuration;
        $token = $workspace->jarvis_configuration['openia_token'] ?? '';
        $model = $workspace->jarvis_configuration['openia_model'] ?? 'gpt-3.5-turbo';
        return compact('token','model');
    }
}
