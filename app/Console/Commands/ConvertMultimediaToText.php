<?php

namespace App\Console\Commands;

use GuzzleHttp\Client;
use App\Models\MediaTema;
use App\Services\FileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;

class ConvertMultimediaToText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'convert:multimedia-text';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert video,audio,youtube to text file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->convertMultimediaToText();
    }

    private function convertMultimediaToText(){
        $medias_to_convert = MediaTema::with(['topic:id,course_id','topic.course:id','topic.course.workspaces:id'])
        ->whereNull('path_convert')->where('ia_convert',1)->orderBy('updated_at','DESC')->take(5)->get();
        foreach ($medias_to_convert as $media) {
            $text_result = null;
            $params = [
                // 'url' => $link,
                'workspace_id'=> $media->topic->course->workspaces->first()?->id,
                'topic_id' => $media->topic->id,
                'media_topic_id' => $media->id
            ];
            if($media->type_id == 'youtube'){
                $link = 'https://www.youtube.com/watch?v=' . $media->value;
                $params['url'] =$link;
                $response = Http::withOptions(['verify' => false])->timeout(1500)->post(env('JARVIS_BASE_URL').'/process_youtube', $params);
            }
            if(in_array($media->type_id,['pdf','video','audio'])){
                $params['relative_path'] =$media->value;
                $response = Http::withOptions(['verify' => false])->timeout(1500)->post(env('JARVIS_BASE_URL').'/convert_file', $params);
                dd($response);
            }
            // if ($text_result) {
            //     $fileName = $media->id.'_'.$media->topic_id.'_'.$media->type_id.'_'.rand(1,5000);
            //     $path = 'jarvis/' . $fileName.'.txt';
            //     $media->path_convert = $path;
            //     $text_result = json_decode( $text_result ,JSON_UNESCAPED_UNICODE);
            //     $result = Storage::disk('s3')->put($path, json_encode($text_result,JSON_UNESCAPED_UNICODE));
            //     $media->save();
            // }
        }
    }
}
