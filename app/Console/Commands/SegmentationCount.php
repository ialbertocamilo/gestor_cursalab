<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

class SegmentationCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'segmentation:count';

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
        $courses = Course::query()
            ->where('active', 1)
            ->get();

        dd(
            $courses[0]->segments()->get()->toArray()
        );
        $this->info($courses->count());

        return Command::SUCCESS;
    }
}
