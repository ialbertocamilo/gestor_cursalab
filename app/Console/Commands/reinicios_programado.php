<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Posteo;
use App\Models\Prueba;
use App\Models\Abconfig;
use App\Models\Reinicio;
use App\Models\Categoria;
use App\Models\School;
use App\Models\SummaryCourse;
use App\Models\SummaryTopic;
use App\Models\Workspace;
use App\Models\Topic;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class reinicios_programado extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reinicios:programados';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Se reinicia a los usuarios configurados por el cronometro a nivel de subworkspace, curso, escuela';


    /**
     * Collection of courses with its workspace configuration
     *
     * @var array
     */
    protected array $coursesWorkspaces = [];

    /**
     * Array of unique courses ids to be reset
     *
     * @var array
     */
    protected array $coursesIds = [];

    /**
     * Workspaces collection
     */
    protected  $workspaces;

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
       $this->reinicios_programado_v2();
    }

    private $ids_a_reiniciar = [];

    private function reinicios_programado_v2(){

        $this->info(" Inicio: " . now());

        // Initialize workspaces collection

        $this->workspaces = Workspace::all();

        // Generate list of courses to be reset

        $this->findCoursesToBeReset();
        $courses = $this->coursesWorkspaces;
        // Reset attempts and update reset count
        $summary_topics_id = SummaryTopic::where('passed',0)->where('attempts','>',1)->whereHas('topic',function($q) use($courses){
            $q->whereIn('course_id',array_column($courses,'courseId'));
        })->groupBy('topic_id')->select('topic_id')->pluck('topic_id');
        $courses_id = Topic::whereIn('id',$summary_topics_id)->where('active',1)->select('course_id')->pluck('course_id');
        // info($courses_id);
        $_courses = collect($courses)->whereIn('courseId',$courses_id)->all();

        // info($_courses);

        foreach ($_courses as $course) {

            $workspaceId = $course['workspaceId'];
            $courseId = $course['courseId'];
            $config = $this->getWorkspaceConfiguration($workspaceId);
            $schedule = json_decode($course['scheduledRestarts'], true);

            // info("course => {$courseId}");

            // Calculate date for the next reset,
            // counting from current date

            $nextDateFromNow = Carbon::now()->subMinutes($schedule['tiempo_en_minutos']);
            $nextDateFromNow->second = 59;

            // Reset attempts
            $config['nro_intentos'] = 3;
            if($workspaceId ==14){
                $config['nro_intentos'] = 2;
            }
            if($course['mod_evaluaciones']){
                $config = json_decode($course['mod_evaluaciones'], true);
            }
            if ($config) {

                // Reset course attempts

                // SummaryCourse::resetFailedCourseAttemptsAllUsers(
                //     $courseId, $config['nro_intentos'], $nextDateFromNow
                // );

                // Update course's resets count

                // SummaryCourse::updateCourseRestartsCount($courseId);

                // Reset topics attempts
                
                SummaryCourse::resetCourseTopicsAttemptsAllUsers(
                    $courseId, $config['nro_intentos'], $nextDateFromNow
                );

                // // Update topics' resets count

                // $topicsIds = SummaryCourse::getCourseTopicsIds($courseId);
                // foreach ($topicsIds as $topicId) {
                //     SummaryTopic::updateTopicRestartsCount($topicId);
                // }
            }
        }

        $this->info(" Fin: " . now());
    }

    /**
     * Get attempts configuration from workspace
     */
    private function getWorkspaceConfiguration($workspaceId) {

        $workspace = $this->workspaces->find($workspaceId);
        return $workspace->mod_evaluaciones;
    }

    /**
     * Generate a list of courses to be reset
     *
     * @return void
     */
    private function findCoursesToBeReset(): void
    {

        // Courses with scheduled restarts

        $coursesWithRestarts = Curso::query()
            ->join('course_workspace', 'courses.id', '=', 'course_workspace.course_id')
            ->whereJsonContains('courses.scheduled_restarts->activado',true)
            ->select(
                'courses.*',
                'course_workspace.workspace_id',
                'courses.scheduled_restarts'
            )
            ->get();

        // Add courses to collection

        $this->addCoursesToCollection($coursesWithRestarts);


        // Courses from schools with scheduled restarts

        $schoolsWithRestarts = School::query()
            ->with('courses')
            ->join('school_workspace', 'school_workspace.school_id', '=', 'schools.id')
            ->whereJsonContains('schools.scheduled_restarts->activado',true)
            ->select('schools.*','schools.scheduled_restarts', 'school_workspace.workspace_id')
            ->groupBy('schools.id')
            ->get();

        // Add courses to collection

        $this->addCoursesFromItemToCollection($schoolsWithRestarts);

        // Courses from subworkspaces with scheduled restarts

        $workspacesWithRestarts = Workspace::query()
            ->with('courses')
            ->join('course_workspace', 'course_workspace.workspace_id', '=', 'workspaces.id')
            ->whereJsonContains('reinicios_programado->activado', true)
            ->select('workspaces.*', DB::raw('reinicios_programado as scheduled_restarts'), 'course_workspace.workspace_id')
            ->groupBy('workspaces.id')
            ->get();


        // Add courses to collection

        $this->addCoursesFromItemToCollection($workspacesWithRestarts);
    }

    /**
     * Add courses from items collection, all courses
     * will use the same workspace id and schedule to restart
     *
     * @param $items
     * @return void
     */
    private function addCoursesFromItemToCollection($items): void
    {

        foreach ($items as $item) {
            $this->addCoursesToCollection(
                $item->courses,
                $item->workspace_id,
                $item->scheduled_restarts
            );
        }
    }

    /**
     * Add course and its respective workspace id to
     * courses collection
     *
     * @param $courses
     * @param $workspaceId
     * @param $scheduleRestarts
     * @return void
     */
    private function addCoursesToCollection(
        $courses, $workspaceId = null, $scheduleRestarts = null
    ): void
    {
        foreach ($courses as $course) {

            if (!in_array($course->id, $this->coursesIds)) {
                $this->coursesIds[] = $course->id;
                $this->coursesWorkspaces[] = [
                    'courseId' => $course->id,
                    'mod_evaluaciones' => $course->mod_evaluaciones,
                    'workspaceId' => $workspaceId ?? $course->workspace_id,
                    'scheduledRestarts' => $scheduleRestarts ?? $course->scheduled_restarts
                ];
            }
        }
    }
}
