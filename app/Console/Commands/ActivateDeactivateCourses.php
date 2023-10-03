<?php

namespace App\Console\Commands;

use App\Models\Course;
use Illuminate\Console\Command;

class ActivateDeactivateCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:activate-deactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate or deactivate courses according to schedule';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Load inactive courses to activate when current datetime
        // has passed their schedule datetime

        $coursesToActivate = Course::query()
            ->where('activate_at', '<=', now())
            ->where('active', 0)
            ->get();

        foreach ($coursesToActivate as $course) {
            $course->active = 1;
            $course->save();

            info('Course has been activated: [' . $course->id . '] ' . $course->name);
        }

        // Load active courses to deactivate when cu

        $coursesToDeactivate = Course::query()
            ->where('deactivate_at', '<=', now())
            ->where('active', 1)
            ->get();

        foreach ($coursesToDeactivate as $course) {
            $course->active = 0;
            $course->save();

            info('Course has been deactivated: [' . $course->id . '] ' . $course->name);
        }


        return Command::SUCCESS;
    }
}
