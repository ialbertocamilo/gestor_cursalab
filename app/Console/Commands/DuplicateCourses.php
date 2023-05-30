<?php

namespace App\Console\Commands;

use App\Models\MediaTema;
use App\Models\Question;
use App\Models\Topic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DuplicateCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courses:duplicate {mode} {originWorkspaceId} {destinationWorkspaceId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Duplicate courses content';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $originWorkspaceId = $this->argument('originWorkspaceId');
        $destinationWorkspaceId = $this->argument('destinationWorkspaceId');

        // Load and validate courses

        $originCourses = $this->loadCourses($originWorkspaceId);
        $destinationCourses = $this->loadCourses($destinationWorkspaceId);

        if ($this->argument('mode') === 'test') {
            $valid = $this->coursesAreValid($originCourses, $destinationCourses);
            if ($valid) {
                $this->info('OK');
            }
        }

        if ($this->argument('mode') === 'save') {
            $this->duplicateCourses($originCourses, $destinationCourses);
        }

        return Command::SUCCESS;
    }

    /**
     *
     */
    public function duplicateCourses($originCourses, $destinationCourses) {
        DB::beginTransaction();

        try {
            $success = true;

            // Iterate every course

            foreach ($originCourses as $originCourse) {

                $originTopics = $this->loadTopics([$originCourse->course_id]);

                $destinationCourse = collect($destinationCourses)
                    ->where('course_name', $originCourse->course_name)
                    ->first();

                if ($destinationCourse) {
                    // Iterate and duplicate every topic

                    foreach ($originTopics as $originTopic) {
                        $destinationTopic = $originTopic->replicate();
                        $destinationTopic->created_at = '2023-05-30 16:00';
                        $destinationTopic->updated_at = null;
                        $destinationTopic->course_id = $destinationCourse->course_id;
                        $destinationTopic->save();

                        $originQuestions = $this->loadQuestions([$originTopic->id]);
                        $originMediaTopics = $this->loadMediaTopics([$originTopic->id]);

                        // Iterate and duplicate questions and media topics

                        foreach ($originQuestions as $originQuestion) {
                            $destinationQuestion = $originQuestion->replicate();
                            $destinationQuestion->created_at = '2023-05-30 16:00';
                            $destinationQuestion->updated_at = null;
                            $destinationQuestion->topic_id = $destinationTopic->id;
                            $destinationQuestion->save();
                        }

                        foreach ($originMediaTopics as $originMediaTopic) {
                            $destinationMediaTopic = $originMediaTopic->replicate();
                            $destinationMediaTopic->created_at = '2023-05-30 16:00';
                            $destinationMediaTopic->updated_at = null;
                            $destinationMediaTopic->topic_id = $destinationTopic->id;
                            $destinationMediaTopic->save();
                        }
                    }
                }
            }

            DB::commit();
            $this->info('Saved successfully');

        } catch (\Exception $e) {
            $this->error($e->getMessage());
            report($e);

            //dd($e->getTrace());
        }
    }

    /**
     * Load courses from database
     */
    public function loadCourses($workspaceId) {

        return DB::select(DB::raw("select
            sw.workspace_id,
            s.id school_id,
            c.id course_id,
            c.name course_name

        from
            courses c
            join course_school cs on c.id = cs.course_id
            join schools s on s.id = cs.school_id

        where
            subworkspace_id  in (SELECT id from workspaces where parent_id = :workspaceId)
            and c.name in (
                'PIC: Prevención y Sanción del Hostigamiento Sexual Laboral',
                'PIC: Conética',
                'PIC: Sesgos Inconscientes',
                'PIC: Evacuación y prevención de incendios',
                'PIC: Primeros auxilios',
                'PIC: Programa IRIS',
                'PIC: Seguridad de la Información 2022'
            )
        "), ['workspaceId' => $workspaceId]);
    }



    /**
     * Courses validation
     */
    public function coursesAreValid($originCourses, $destinationCourses) {

        $errors = [];

        // Validation: courses count should be the same

        $originCoursesCount = count($originCourses);
        $destinationCoursesCount = count($destinationCourses);
        if ($originCoursesCount === $destinationCoursesCount) {

        } else {
            $errors[] = "Courses count does not match: origin $originCoursesCount - destination $destinationCoursesCount";
        }

        // Validation: destination course should have no topics

        $destinationCoursesIds = collect($destinationCourses)
            ->pluck('course_id')
            ->toArray();
        $destinationCoursesTopics = $this->loadTopics($destinationCoursesIds);

        if ($destinationCoursesTopics->count() > 0) {
            $coursesIds = $destinationCoursesTopics
                ->unique('course_id')
                ->pluck('course_id')
                ->toArray();
            $errors[] = "Destination courses already have topics. Courses ids: " . implode(', ', $coursesIds);
        }

        // Show errors

        foreach ($errors as $error) {
            $this->error($error);
        }

        return count($errors) === 0;
    }

    /**
     * Load courses topics
     */
    public function loadTopics($coursesIds) {

        return Topic::whereIn('course_id', $coursesIds)->get();
    }

    /**
     * Load topics questions
     */
    public function loadQuestions($topicsIds) {

        return Question::whereIn('topic_id', $topicsIds)->get();
    }

    /**
     * Load topics media topics
     */
    public function loadMediaTopics($topicsIds) {

        return MediaTema::whereIn('topic_id', $topicsIds)->get();
    }
}
