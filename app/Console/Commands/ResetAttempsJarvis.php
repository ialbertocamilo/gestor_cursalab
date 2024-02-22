<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Mongo\JarvisAttempt;

class ResetAttempsJarvis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:attempts-jarvis';

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
        $this->resetJarvisAttempts();
    }

    private function resetJarvisAttempts(){
        $jarvis_attempts = JarvisAttempt::all();
        $now = Carbon::now();
        $last_month = $now->subMonth()->format('F');
        foreach ($jarvis_attempts as $jarvis_attempt) {
            $historics = is_null($jarvis_attempt->historics) ? [] : $jarvis_attempt->historics; 
            $historics[] = [
                'month'=> $last_month,
                'attempts'=>$jarvis_attempt->attempts
            ];
            $jarvis_attempt->historics = $historics;
            $jarvis_attempt->attempts = 0;
            $jarvis_attempt->save();
        }
    }
}
