<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateSummariesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'summary:update-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar datos de resumenes de usuarios y cursos';

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
        $users = User::whereNotNull('summary_user_update')->orWhereNotNull('summary_course_update')->get();
    }
}
