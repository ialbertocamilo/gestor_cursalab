<?php

namespace App\Models;

use GuzzleHttp\Psr7\stream;
use App\Models\Mongo\JarvisAttempt;
use App\Models\Mongo\JarvisResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jarvis extends Model
{
    use HasFactory;
    
    private $client_folder ;
    private $jarvis_base_url;
    private $jarvis_folder ;
    private $bucket;

    public function __construct()
    {
        $this->client_folder = env('AWS_CURSALAB_CLIENT_NAME_FOLDER');
        $this->bucket = env('AWS_BUCKET');

        $this->jarvis_base_url = env('JARVIS_BASE_URL');
        $this->jarvis_folder = 'jarvis';
    }
    protected function generateDescriptionJarvis($request){
        
        $data['name']  = $request->get('name');
        $data['type']  = $request->get('type');
        $data = array_merge(self::getJarvisConfiguration(),$data);
        $response = Http::withOptions(['verify' => false])->timeout(900)->post(
            env('JARVIS_BASE_URL').'/generate_description', $data
        );
        if ($response->successful()) {
            $data = $response->json();
            JarvisAttempt::increaseAttempt(get_current_workspace()?->id,'descriptions');
            JarvisResponse::insertResponse([$data['description'][1]],'description');
            return $data['description'][0];
        }
        
        return $response;
    }
    protected function convertMultimediaToText($multimedia){
        info('init convertMultimediaToText');
        info($multimedia);
        $text_result = null;
        $workspace = $multimedia->topic->course->workspaces->first();
        $params = self::getJarvisConfiguration($workspace);
        info($params);
        $params['bucket'] = $this->bucket;
        if($multimedia->type_id == 'youtube'){
            $link = 'https://www.youtube.com/watch?v=' . $multimedia->value;
            $params['url'] =$link;
            $response = Http::withOptions(['verify' => false])->timeout(1500)->post($this->jarvis_base_url.'/process_youtube', $params);
        }
        if(in_array($multimedia->type_id,['pdf','video','audio'])){
            $params['relative_path'] = $this->client_folder.'/'.$multimedia->value;
            $response = Http::withOptions(['verify' => false])->timeout(1500)->post($this->jarvis_base_url.'/convert_file', $params);
        }
        info($params);
        info($response);
        if ($response->successful()) {
            $data = $response->json();
            $text = $data['message'];
            $workspace_id =  $workspace->id;
            $topic_id = $multimedia->topic->id;
            $media_topic_id =  $multimedia->id;
            $path_name = $this->generateRandomName($workspace_id,$topic_id,$media_topic_id);
            $multimedia->path_convert=$path_name;
            $this->saveInS3($path_name,$text);
            $multimedia->save();
            return 'Created';
        }
        info('final convertMultimediaToText');
    }
    protected function generateQuestionsJarvis($request){
        $params = $request->all();
        $params = array_merge(self::getJarvisConfiguration(),$params);
        $media_topics = MediaTema::where('topic_id',$params['topic_id'])->where('ia_convert',1)->whereNotNull('path_convert')->get();
        $text_grouped = [];
        foreach ($media_topics as $media) {
            $text = Storage::disk('s3')->get($media->path_convert);
            $text_grouped[]=[
                'media_title' => $media->title,
                'media_topic_id' => $media->id,
                'text' => $text
            ];
        }
        $params['text_grouped'] = $text_grouped;
        $response = Http::withOptions(['verify' => false])->timeout(900)->post($this->jarvis_base_url.'/generate-questions', $params);
        if ($response->successful()) {
            $data = $response->json();
            JarvisAttempt::increaseAttempt(get_current_workspace()?->id,'evaluations');
            JarvisResponse::insertResponse($data['message'][1],'evaluation');
            info($data['message'][0]);
            return $data['message'][0];
        }
    }
    
    protected function generateChecklistJarvis($request){
        // $data['files']  = $request->file('files');
        // dd($request->file('files'));
        // $data = array_merge(self::getJarvisConfiguration(),$data);
        $files = $request->file('files');
        $multipart = [];
        foreach ($files as $file) {
            $multipart[] = [
                'name' => 'attachments[]',
                'contents' => file_get_contents($file),
                'filename' => basename($file),
                'headers' => ['Content-Type' => 'text/plain'] // Establecer el tipo MIME como texto plano
            ];
        }
        $response = Http::withOptions([
            'verify' => false,
        ])->attach($multipart)->timeout(900)->post(env('JARVIS_BASE_URL').'/generate_checklist');
        // $multipart = [];
        // foreach ($files as $key => $file) {
        //     $multipart[] = [
        //         'name'     => $key,
        //         'contents' => stream_for(fopen($file->path(), 'r'))
        //     ];
        // }

        // 
        if ($response->successful()) {
            $data = $response->json();
            dd($data);
            // JarvisAttempt::increaseAttempt(get_current_workspace()?->id,'descriptions');
            // JarvisResponse::insertResponse([$data['description'][1]],'description');
            return $data['description'][0];
        }
    }

    private function getJarvisConfiguration($_workspace=null){
        $workspace = $_workspace ?? get_current_workspace();
        $jarvis_configuration = is_array($workspace->jarvis_configuration) ? $workspace->jarvis_configuration : json_decode($workspace->jarvis_configuration,true);
        $token = $jarvis_configuration['openia_token'] ?? '';
        $model = $jarvis_configuration['openia_model'] ?? 'gpt-3.5-turbo';
        $context = $jarvis_configuration['context_jarvis'] ?? '';
        return compact('token','model','context');
    }
    private function saveInS3($path_name,$text)
    {
        $texto = $text;
        // Guardar el texto en el archivo .txt en S3
        Storage::disk('s3')->put($path_name, $texto, 'public');
        return "Texto guardado en S3 correctamente.";
    }
    private function generateRandomName($workspace_id,$topic_id,$media_topic_id){
        // Generar un número aleatorio entre 1000 y 9999
        $post_number = str(mt_rand(1000, 9999));
        return $this->jarvis_folder.'/'.'wk_id_'.$workspace_id . '_tp_id_' . $topic_id . '_mt_id_' . $media_topic_id . '_rnd_' . $post_number.'.txt';
    }
}
