<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\SummaryCourse;
use App\Models\SummaryTopic;
use App\Models\SummaryUser;
use App\Models\Taxonomy;
use App\Models\Question;

class CleanOverdueQuizzes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quizzes:finish-summary-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finalizar evaluaciones fuera de tiempo';

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
        $rows = SummaryTopic::with('topic.course', 'user.subworkspace')
                    ->where('taking_quiz', ACTIVE)
                    ->where('current_quiz_finishes_at', '<=', now())
                    ->get();

        foreach ($rows as $key => $row) {

            try {

                if ($row->hasNoAttemptsLeft(null, $row->user)) continue;

                $total_questions = Question::where('topic_id', $row->topic_id)->count();

                $data_ev = [
                    'attempts' => $row->attempts + 1,
                    'last_time_evaluated_at' => now(),
                    'current_quiz_started_at' => NULL,
                    'current_quiz_finishes_at' => NULL,
                    'taking_quiz' => NULL,
                ];

                if ($row->status->code == 'desarrollo') {

                    $status_failed = Taxonomy::getFirstData('topic', 'user-status', 'desaprobado');

                    $data_ev = $data_ev + [  
                        'correct_answers' => 0,
                        'failed_answers' => $total_questions,
                        'passed' => 0,
                        'answers' => [],
                        'grade' => 0,
                        'status_id' => $status_failed->id,
                    ];
                }

                $row->update($data_ev);

                $row_course = SummaryCourse::updateUserData($row->topic->course);
                $row_user = SummaryUser::updateUserData($row->user);

            } catch (\Exception $e) {

                info($e);
            }
        }
    }
}
