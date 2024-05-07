<?php

namespace App\Models;

use App\Models\Course;
use GuzzleHttp\Psr7\stream;
use Illuminate\Support\Facades\DB;
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
        $params['text_grouped'] = self::getTextFromMediaTopics($params['topic_id']);
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
        $files = $request->file('files');
        $number_activities = $request->number_activities;
        $multipart = [];
        foreach ($files as $file) {
            $fileContents = file_get_contents($file);
            $filename = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $multipart[] = [
                'name' => 'attachments[]',
                'contents' => $fileContents,
                'filename' => $filename,
                'headers' => ['Content-Type' => $mimeType]
            ];
        }
        $params = self::getJarvisConfiguration();
        $course_ids = $request->get('course_ids');
        $text_grouped = [];
        foreach ($course_ids as $course_id) {
            $topics_id = Topic::select('id')->where('course_id',$course_id)
            ->whereHas('medias',function($mt){
                $mt->whereNotNull('path_convert');
            })->get()->pluck('id');
            foreach ($topics_id as $topic_id) {
                $text = self::getTextFromMediaTopics($topic_id);
                $text_grouped = array_merge($text_grouped,$text);
            }
        }
        $response = Http::withOptions([
            'verify' => false,
        ])->attach($multipart)
        ->attach('token', $params['token'])
        ->attach('text_grouped', json_encode($text_grouped))
        ->attach('model', $params['model'])
        ->attach('number_activities',$number_activities)->timeout(900)->post(env('JARVIS_BASE_URL').'/generate_checklist',$params);

        // 
        if ($response->successful()) {
            $data = $response->json();
            // JarvisAttempt::increaseAttempt(get_current_workspace()?->id,'descriptions');
            // JarvisResponse::insertResponse([$data['description'][1]],'description');
            return $data['description'];
        }
    }
    protected function searchCoursesTranscribed($name){
        $workspace = get_current_workspace();

        $courses = Course::select('id',DB::raw('CONCAT(id," - ",name) as name'))->FilterByPlatform()->whereHas('workspaces', function ($t) use ($workspace) {
                $t->where('workspace_id', $workspace->id);
            })
            ->where(function ($query) use ($name) {
                $query->where('name', 'like', "%$name%")
                    ->orWhere('id', $name);
            })
            ->whereHas('topics.medias',function($mt){
                $mt->whereNotNull('path_convert');
            })
            ->get()->map(function($course){
                $course->medias_count = Topic::select('id')->where('course_id',$course->id)
                ->whereHas('medias',function($mt){
                    $mt->whereNotNull('path_convert');
                })->withCount([
                    'medias'=> function($mt){
                        $mt->whereNotNull('path_convert');
                    }
                ])->get()->sum('medias_count');
                return $course;
            });
        return $courses;
    }
    /* ---------------------------------------------------------PRIVATE FUNCTIONS-------------------------------------------------------------------------------------*/
    
    private function getTextFromMediaTopics($topic_id){
        $media_topics = MediaTema::where('topic_id',$topic_id)->where('ia_convert',1)->whereNotNull('path_convert')->get();
        $text_grouped = [];
        foreach ($media_topics as $media) {
            $text = Storage::disk('s3')->get($media->path_convert);
            $text_grouped[]=[
                'media_title' => $media->title,
                'media_topic_id' => $media->id,
                'text' => $text
            ];
        }
        return $text_grouped;
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

    /* -----------------------------------------------------END PRIVATE FUNCTIONS-------------------------------------------------------------------------------------*/
}
