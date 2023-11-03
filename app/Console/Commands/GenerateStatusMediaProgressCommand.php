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

    private function generateStatusMediaProgress()
    {
        $this->line("Inicio: " . now());

        $status_revisado = Taxonomy::getFirstData('topic', 'user-status', 'revisado');
        $status_realizado= Taxonomy::getFirstData('topic', 'user-status', 'realizado');
        $status_passed = Taxonomy::getFirstData('topic', 'user-status', 'aprobado');
        $status_failed = Taxonomy::getFirstData('topic', 'user-status', 'desaprobado');
        $status_list = [$status_revisado?->id, $status_realizado?->id, $status_passed?->id, $status_failed?->id];

        $query = SummaryTopic::with('topic.medias')->whereIn('status_id', $status_list);

        $total_records = $query->count();
        $chunk_total = 500;

        $this->line("Total records " . $total_records);

        $bar = $this->output->createProgressBar($total_records);
        $bar->start();

        $query->chunkById($chunk_total, function ($summary_topics) use ($bar) {

            $chunkData = [];

            foreach ($summary_topics as $sum_top) {

                // $medias = MediaTema::where('topic_id', $sum_top->topic_id)->orderBy('position','ASC')->get();
                $medias = $sum_top->topic->medias->sortBy('position');
                $user_progress_media = [];

                foreach($medias as $med)
                {
                    array_push($user_progress_media, (object) array(
                        'media_topic_id' => $med->id,
                        'status'=> 'revisado',
                        'last_media_duration' => null
                    ));
                }

                $chunkData[] = [
                    'id' => $sum_top->id,
                    'media_progress' => json_encode($user_progress_media),
                    'last_media_access' => null,
                    'last_media_duration' => null,
                ];
            }

            batch()->update(new SummaryTopic, $chunkData, 'id');
        
            $bar->advance($summary_topics->count());
        });
        
        $bar->finish();

        $this->line("Fin: " . now());
    }
}
