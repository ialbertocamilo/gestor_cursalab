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
        $topics = Topic::with(['course' => ['schools', 'workspaces'], 'evaluation_type'])
                    ->withCount('questions')
                    ->whereHas('course')
                    ->whereHas('questions')
                    ->where('evaluation_verified','<>', 1)
                    ->where('assessable', 1)
                    ->get();

        $total = $topics->count();

        $this->info("\n ------- Cantidad de temas: {$total} ------- \n");

        $verified = $not_verified = 0;

        $bar = $this->output->createProgressBar($total);

        $bar->start();

        foreach ($topics as $key => $topic) {

            $result = Question::verifyEvaluation($topic);

            if ($result['status'] == false) {
                
                $not_verified++;

                $workspace = $topic->course->workspaces->first();

                $school = $topic->course->schools->first();

                info('Workspace: ' . $workspace->name ?? 'UNDEFINED');
                info('Escuela: ' . $school->name ?? 'UNDEFINED');
                // info('Escuela estado: ' . ($school->active ? 'Activo' : 'Inactivo'));
                info('Curso: ' . $topic->course->name ?? 'UNDEFINED');
                // info('Curso estado: ' . ($topic->course->active ? 'Activo' : 'Inactivo'));
                info('Tema: ' . $topic->name ?? 'UNDEFINED');
                info('# Preguntas: ' . $topic->questions_count ?? 'UNDEFINED');



                $url = url("/escuelas/{$school->id}/cursos/{$topic->course->id}/temas/{$topic->id}/preguntas");

                info($url);
                info('-----------------------------------------------------');

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
