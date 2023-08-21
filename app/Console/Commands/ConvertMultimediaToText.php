<?php

namespace App\Console\Commands;

use App\Models\MediaTema;
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
        $medias_to_convert = MediaTema::whereNull('path_convert')->where('ia_convert',1)->orderBy('updated_at','DESC')->take(5)->get();
        foreach ($medias_to_convert as $media) {
            switch ($media->type_id) {
                case 'youtube':
                    $url = 'https://www.youtube.com/watch?v=' . $media->value;
                    $params = [
                        'url' => $url, 
                    ];
                    $response = Http::withOptions(['verify' => false])->timeout(800)->post('http://localhost:5000/process_youtube', $params);
                    break;
                default:
                    # code...
                    break;
            }
            if ($response->successful()) {
                $text_result = $response->body();
                $fileName = $media->id.'_'.$media->topic_id.'_'.$media->type_id.'_'.rand(1,5000);
                $path = 'jarvis/' . $fileName.'.txt';
                $media->path_convert = $path;
                $text_result = json_decode( $text_result ,JSON_UNESCAPED_UNICODE);
                $result = Storage::disk('s3')->put($path, json_encode($text_result,JSON_UNESCAPED_UNICODE));
                $media->save();
            } 
        }
    }
}
