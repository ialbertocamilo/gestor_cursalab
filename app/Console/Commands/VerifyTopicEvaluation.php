<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Topic;
use App\Models\Question;

class VerifyTopicEvaluation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'topic:verify-evaluation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verificar evaluaciones';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $topics = Topic::with('course')->whereHas('questions')->get();

        $total = $topics->count();

        $this->info("\n ------- Cantidad de temas: {$total} ------- \n");

        $verified = $not_verified = 0;

        $bar = $this->output->createProgressBar($total);

        $bar->start();

        foreach ($topics as $key => $topic) {

            $result = Question::verifyEvaluation($topic);

            if ($result['status'] == false) {
                
                $not_verified++;

                info('Curso: ' . $topic->course->name ?? 'UNDEFINED');
                info('Tema: ' . $topic->name ?? 'UNDEFINED');
                info('----------------------');

            } else {

                $verified++;
            }

            $bar->advance();
        }

        $bar->finish();

        $this->info("\n ------- Verificados: {$verified} ------- \n");
        $this->info("\n ------- No Verificados: {$not_verified} ------- \n");
    }
}
