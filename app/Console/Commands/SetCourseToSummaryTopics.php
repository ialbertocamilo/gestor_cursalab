<?php

namespace App\Console\Commands;

use App\Models\Topic;
use App\Models\Workspace;
use Illuminate\Console\Command;

// ALTER TABLE `summary_topics` ADD COLUMN `course_id` bigint UNSIGNED NULL AFTER `user_id`, 
// ADD CONSTRAINT `summary_topics_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

// UPDATE `summary_topics` SET `last_time_evaluated_at` = NULL WHERE `last_time_evaluated_at` < '2000-01-01 00:00:00';

class SetCourseToSummaryTopics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:set-course-to-summary-topics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $topics = Topic::whereHas('summaries')->get();

        $bar = $this->output->createProgressBar($topics->count());

        foreach ($topics as $topic) {

            \DB::table('summary_topics')->whereNull('course_id')->where('topic_id', $topic->id)->update(['course_id' => $topic->course_id]);
           
            $bar->advance();
        }

        $bar->finish();
    }
}
