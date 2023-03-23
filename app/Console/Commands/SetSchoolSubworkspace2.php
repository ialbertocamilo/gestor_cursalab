<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\Segment;
use App\Models\SegmentValue;
use App\Models\Workspace;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SetSchoolSubworkspace2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schools:set-default-subworkspace2 {workspace}';

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
        $workspaceId = $this->argument('workspace');

        $schools = School::whereRelation('workspaces', 'id', $workspaceId)
            ->get();

        $bar = $this->output->createProgressBar($schools->count());

        $subworkspaces = Workspace::query()
            // ->where('active', 1)
            ->where('parent_id', $workspaceId)
            ->get();

        foreach ($schools as $school) {

            if (!$this->thereIsACourseWithoutModuleSegmentation($school->id)) {
                // Load subworkspaces used in segmentation
                $_subworkspaces = $this->loadSubworkspaces($school->id);

                if (!$_subworkspaces->count()){
                 
                    $this->setSchoolToSubworkspaces($subworkspaces, $school);
                } else {

                    $this->setSchoolToSubworkspaces($_subworkspaces, $school);
                }

            } else {
                
                $this->setSchoolToSubworkspaces($subworkspaces, $school);
            }

            $bar->advance();
        }

        $bar->finish();
    }

    public function setSchoolToSubworkspaces($subworkspaces, $school)
    {
        foreach ($subworkspaces as $subworkspace) {
            $subworkspace->schools()->attach($school);
        }
    }

    /**
     * Checks if there is at least one course without module segmentation
     * @param $schoolId
     * @return boolean
     */
    public function thereIsACourseWithoutModuleSegmentation($schoolId): bool
    {

        $query = "select
                s.id,
                sum(if (sv.criterion_id = 1, 1, 0)) module_criterion_total
            from
                segments s
                    inner join segments_values sv on s.id = sv.segment_id
            where
                s.model_type = 'App\\\\Models\\\\Course'
                and s.active = 1
                and s.deleted_at IS NULL
                and s.model_id in (
                    select
                        course_id
                    from
                        course_school cs
                    where
                        school_id = :schoolId
                )
            group by s.id
            having module_criterion_total = 0";
        $records = DB::select(DB::raw($query), [ 'schoolId' => $schoolId ]);

        return count($records) >= 1;
    }

    /**
     * Load subworkspaces used in courses segmentation
     *
     * @param $schoolId
     * @return Collection
     */
    public function loadSubworkspaces($schoolId): Collection
    {
        // Load courses segments

        $segments = DB::select(DB::raw("
            select
                s.id
            from
                segments s
            where
                s.model_type = 'App\\\\Models\\\\Course'
                and s.active = 1
                and s.deleted_at IS NULL
                and s.model_id in (
                    select
                        course_id
                    from
                        course_school cs
                    where
                        school_id = :schoolId
                )
        "), [ 'schoolId' => $schoolId ]);

        $segmentsIds = collect($segments)->pluck('id')->toArray();

        $segmentsValues = SegmentValue::query()
            ->where('criterion_id', 1) //  -- module criterion
            ->whereIn('segment_id', $segmentsIds)
            ->select(DB::raw('distinct criterion_value_id as module_id'))
            ->get();

        $modulesIds = $segmentsValues->pluck('module_id')->toArray();

        return Workspace::query()
            // ->where('active', 1)
            ->whereIn('criterion_value_id', $modulesIds)
            ->get();
    }
}
