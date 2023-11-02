<?php

namespace App\Console\Commands;

use App\Models\Taxonomy;
use App\Models\MediaTema;
use App\Models\SummaryTopic;
use Illuminate\Console\Command;

class GenerateStatusMediaProgressCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:mediaprogress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->generateStatusMediaProgress();
    }

    private function generateStatusMediaProgress(){

        $status_revisado = Taxonomy::getFirstData('topic', 'user-status', 'revisado');
        $status_realizado= Taxonomy::getFirstData('topic', 'user-status', 'realizado');
        $status_passed = Taxonomy::getFirstData('topic', 'user-status', 'aprobado');
        $status_failed = Taxonomy::getFirstData('topic', 'user-status', 'desaprobado');
        $status_list = [$status_revisado?->id, $status_realizado?->id, $status_passed?->id, $status_failed?->id];

        SummaryTopic::whereIn('status_id',$status_list)->chunkById(10000, function ($summary_topics) {

            $_bar = $this->output->createProgressBar(count($summary_topics));
            $_bar->start();
            foreach ($summary_topics as $sum_top) {

                $medias = MediaTema::where('topic_id', $sum_top->topic_id)->orderBy('position','ASC')->get();

                $user_progress_media = array();
                foreach($medias as $med)
                {
                    array_push($user_progress_media, (object) array(
                        'media_topic_id' => $med->id,
                        'status'=> 'revisado',
                        'last_media_duration' => null
                    ));
                }
                $sum_top->media_progress = json_encode($user_progress_media);
                $sum_top->last_media_access = null;
                $sum_top->last_media_duration = null;
                $sum_top->save();

                $_bar->advance();
            }

            $_bar->finish();
        });

    }
}
