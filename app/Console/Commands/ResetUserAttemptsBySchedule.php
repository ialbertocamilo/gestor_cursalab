<?php

namespace App\Console\Commands;

use App\Models\SummaryTopic;
use App\Models\Course;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ResetUserAttemptsBySchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:reset-user-attempts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reiniciar los intentos de usuarios en evaluaciones desaprobadas o abandonadas.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->line("Inicio: " . now());

        $courses = Course::disableCache()
            ->with([
                'topics' => function($q) {
                    $q->where('active', ACTIVE);
                    $q->select('id', 'name', 'course_id');
                },
                'schools' => function($q) {
                    $q->where('active', ACTIVE);
                    $q->select('id', 'name', 'scheduled_restarts');
                }
            ])
            ->select('id', 'name', 'scheduled_restarts', 'mod_evaluaciones')
            ->where('active', ACTIVE)->get();

        $bar = $this->output->createProgressBar($courses->count());

        $records = 0;

        foreach ($courses as $course) {

            $config = $course->mod_evaluaciones;
            $minutes = $course->getScheduleRestartMinutes();
            $topic_ids = $course->topics->pluck('id');

            if (!$minutes || !$topic_ids) {

                $bar->advance();
                continue;
            }

            $date = Carbon::now()->subMinutes($minutes);
            $date->second = 59;

            $query = SummaryTopic::disableCache()
                        ->select('id', 'restarts', 'attempts', 'updated_at')
                        ->whereIn('topic_id', $topic_ids)
                        ->whereRelationIn('status', 'code', ['desaprobado', 'por-iniciar'])
                        ->where('attempts', '>=', $config['nro_intentos'])
                        ->where('last_time_evaluated_at', '<=', $date);

            $total = $query->count();

            if (!$total) {

                $bar->advance();
                continue;
            }

            $query->chunkById(500, function ($rows) {
                            
                $chunkData = [];

                foreach ($rows as $row) {

                    $chunkData[] = [
                        'id' => $row->id,
                        'restarts' => $row->restarts ? $row->restarts + 1 : 1 ,
                        // 'restarts' => ['+', 1],
                        'attempts' => 0,
                        'updated_at' => $row->updated_at->format('Y-m-d H:i:s'),
                    ];
                }

                batch()->update(new SummaryTopic, $chunkData, 'id');
            });

            $records += $total;

            $bar->advance();
        }

        $this->line("");

        $bar->finish();

        $this->line("Records updated => " . $records);

        $this->line("Fin: " . now());
    }
}
