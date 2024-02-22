<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\CourseSchool;
use App\Models\MediaTema;
use App\Models\Question;
use App\Models\School;
use App\Models\Topic;
use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DuplicateSchoolCourses extends Command
{

    const DATE_TIME = '2024-01-15 17:30';

    /**
     * The name and signature of the console command
     * @var string
     */
    protected $signature = 'courses:duplicate-school-courses {mode} {originWorkspaceId} {destinationWorkspaceId} {schoolsIds} {destinationSchoolIds?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Duplicate schools' courses content";

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $originWorkspaceId = $this->argument('originWorkspaceId');
        $destinationWorkspaceId = $this->argument('destinationWorkspaceId');
        $originSchoolsIds = explode(',', $this->argument('schoolsIds'));
        $destinationSchoolsIds = explode(',', $this->argument('destinationSchoolIds'));

        // Print workspaces names

        $this->info('Origin: ' . Workspace::find($originWorkspaceId)->name);
        $this->info('Destination: ' . Workspace::find($destinationWorkspaceId)->name);


        // Print school's names and validate if all supplied schools
        // id has been found

        $originSchools = School::query()
            ->whereIn('id', $originSchoolsIds)
            ->get();

        $this->info(PHP_EOL);
        $this->info('Found origin schools:');
        $foundSchools = [];
        foreach ($originSchools as $school) {
            $this->info($school->id . ' - ' . $school->name);
            $foundSchools[] = $school->id;
        }

        if (count($foundSchools) < count($originSchoolsIds)) {
            $this->error(
                'Only '
                . count($foundSchools)
                . ' schools has been found: '
                . implode(',', $foundSchools)
            );
        }

        // Print found origin courses

        $originCourses = $this->loadCourses(
            $originWorkspaceId,
            $originSchools->pluck('id')->toArray()
        );

        $this->info(PHP_EOL);
        $this->info('Found origin courses:');
        $this->printCourses($originCourses);

        // Print found destination schools

        $destinationSchools = count($destinationSchoolsIds) === 0
            ?
                $this->loadSchoolsByName(
                    $destinationWorkspaceId,
                    $originSchools->pluck('name')->toArray()
                )
            : School::whereIn('id', $destinationSchoolsIds)->get();

        $this->info(PHP_EOL);
        $this->info('Found destination schools:');
        foreach ($destinationSchools as $school) {
            $this->info($school->id . ' - ' . $school->name);
        }

        // Load destination courses

        $destinationCourses = $this->loadCourses(
            $destinationWorkspaceId,
            [],
            $originSchools->pluck('name')->toArray()
        );

        // School validation

        if (count($originSchools) != count($destinationSchools)) {
            $this->error('Schools in destination not found');
            return;
        }

        // Courses validation

//        $valid = $this->coursesAreValid($originCourses, $destinationCourses);
//        if ($valid) {
//            $this->info(PHP_EOL);
//            $this->info('OK - No courses exists in destination schools');
//
//
//            if ($this->argument('mode') === 'save') {
//                $this->duplicateCourses(
//                    $originCourses, $destinationSchools, $destinationWorkspaceId
//                );
//            }
//        }

        if ($this->argument('mode') === 'save') {
            $this->duplicateCourses(
                $originCourses, $destinationSchools, $destinationWorkspaceId
            );
        }


        return Command::SUCCESS;
    }

    /**
     *
     */
    public function duplicateCourses(
        $originCourses, $destinationSchools, $destinationWorkspaceId
    ) {
        DB::beginTransaction();

        try {
            $success = true;

            // Iterate every course

            foreach ($originCourses as $_originCourse) {

                $originSchoolName = $_originCourse->school_name;
                $originCourse = Course::find($_originCourse->course_id);

                // Validation: origin course exists

                if ($originCourse == null) {
                    $this->error($_originCourse->course_id . ' course not found');
                    $success = false;
                }

                // Create destination course

                $this->info('Duplicating course: ' . $originCourse->id);
                $destinationCourse = $originCourse->replicate();
                $destinationCourse->certificate_template_id = null;
                $destinationCourse->external_id = null;
                $destinationCourse->created_at = self::DATE_TIME;
                $destinationCourse->updated_at = null;
                $destinationCourse->save();

                // Validation: Destination course was created and has an id

                if ($destinationCourse) {
                    if (!$destinationCourse->id) {
                        $this->error($destinationCourse->name . ' has not an id');
                        $success = false;
                    }
                }

                // Create course-school relationship

                $destinationSchool = collect($destinationSchools)
                    ->where('name', $originSchoolName)
                    ->first();

                CourseSchool::firstOrCreate([
                    'school_id' => $destinationSchool->id,
                    'course_id' => $destinationCourse->id
                ],
                    [
                        'position' => 1
                    ]);

                DB::table('course_workspace')->insert([
                    'workspace_id' => $destinationWorkspaceId,
                    'course_id' => $destinationCourse->id
                ]);

                // Iterate and duplicate every topic

                $originTopics = $this->loadTopics([$originCourse->id]);

                foreach ($originTopics as $originTopic) {
                    $destinationTopic = $originTopic->replicate();
                    $destinationTopic->external_id = null;
                    $destinationTopic->created_at = self::DATE_TIME;
                    $destinationTopic->updated_at = null;
                    $destinationTopic->course_id = $destinationCourse->id;
                    $destinationTopic->save();

                    $originQuestions = $this->loadQuestions([$originTopic->id]);
                    $originMediaTopics = $this->loadMediaTopics([$originTopic->id]);

                    // Iterate and duplicate questions and media topics

                    foreach ($originQuestions as $originQuestion) {
                        $destinationQuestion = $originQuestion->replicate();
                        $destinationQuestion->created_at = self::DATE_TIME;
                        $destinationQuestion->updated_at = null;
                        $destinationQuestion->topic_id = $destinationTopic->id;
                        $destinationQuestion->save();
                    }

                    foreach ($originMediaTopics as $originMediaTopic) {
                        $destinationMediaTopic = $originMediaTopic->replicate();
                        $destinationMediaTopic->created_at = self::DATE_TIME;
                        $destinationMediaTopic->updated_at = null;
                        $destinationMediaTopic->topic_id = $destinationTopic->id;
                        $destinationMediaTopic->save();
                    }
                }
            }

            if ($success) {
                DB::commit();
                $this->info('Saved successfully');
            } else {
                DB::rollBack();
            }


        } catch (\Exception $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            report($e);

            //dd($e->getTrace());
        }
    }

    /**
     * Load courses from database
     */
    public function loadCourses($workspaceId, $schoolsIds = [], $schoolsNames = []) {

        $query = "select
            s.id school_id,
            c.id course_id,
            c.name course_name,
            s.name school_name

        from
            courses c
            join course_school cs on c.id = cs.course_id
            join schools s on s.id = cs.school_id

        where
            s.id in (
                select school_id
                from school_subworkspace ss
                where subworkspace_id  in (
                    select id from workspaces where parent_id = :workspaceId
                )
            )
            and c.deleted_at is null
        ";

        if (count($schoolsIds) > 0)
            $query .= " and s.id in (" . implode(',', $schoolsIds) . ")";

        if (count($schoolsNames) > 0)
            $query .= " and s.name in ('" . implode("','", $schoolsNames) . "')";


        return DB::select(DB::raw($query), ['workspaceId' => $workspaceId]);
    }

    /**
     * Load schools from workspace with provided names
     * @param $workspaceId
     * @param $schoolsNames
     * @return array
     */
    public function loadSchoolsByName($workspaceId, $schoolsNames = []) {

        $query = "select
            id,
            name
        from
            schools s

        where
            id in (
                select school_id
                from school_subworkspace ss
                where subworkspace_id  in (
                    select id from workspaces where parent_id = :workspaceId
                )
            )
        ";

        $query .= " and name in ('" . implode("','", $schoolsNames) . "')";

        return DB::select(DB::raw($query), ['workspaceId' => $workspaceId]);
    }

    /**
     * Courses validation
     */
    public function coursesAreValid($originCourses, $destinationCourses) {

        $errors = [];

        // Validation: destination courses count should be zero

        $originCoursesCount = count($originCourses);
        $destinationCoursesCount = count($destinationCourses);
        if ($destinationCoursesCount != 0) {

            $error = "Courses in destination already exists";
            $errors[] = $error;

            $this->info(PHP_EOL);
            $this->error($error);
            $this->printCourses($destinationCourses, 'warning');
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

    /**
     * Print courses info on console
     * @param $courses
     * @param string $type
     * @return void
     */
    public function printCourses($courses, string $type = 'info') {

        foreach ($courses as $course) {

            $message = 'School: '
                . $course->school_id
                . ' | Course: '
                . $course->course_id
                . ' - '
                . $course->course_name;

            if ($type === 'info') $this->info($message);
            if ($type === 'warning') $this->warn($message);
        }
    }
}
