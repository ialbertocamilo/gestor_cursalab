<?php

namespace App\Console\Commands;

use App\Models\Jarvis;
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
        $medias_to_convert = MediaTema::with(['topic:id,course_id','topic.course:id','topic.course.workspaces:id,jarvis_configuration'])
        ->whereNull('path_convert')->where('ia_convert',1)->orderBy('updated_at','DESC')->take(5)->get();
        foreach ($medias_to_convert as $media) {
            Jarvis::convertMultimediaToText($media);
        }
    }
}
