<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\SegmentationCount;
use App\Models\User;
use App\Services\SegmentationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CountSegmentation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'segmentation:count-users';

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
        // Command should run only for companies with less than 1000 users

        $usersCount = User::query()
            ->where('active', 1)
            ->count();

        if ($usersCount > 2000) {
            $this->info('Only 2000 or less users are allowed.');
            return Command::SUCCESS;
        }

        $allCourses = SegmentationService::getCoursesWhereSegmentationStateHasChange();

        $coursesChunks = $allCourses->chunk(100);
        foreach ($coursesChunks as $courses) {
            $this->info('Processed courses: ' . $courses->count());

            try {
                DB::beginTransaction();

                $coursesToBeHashed = [];

                // Calculate courses segmentation and save its count totals

                foreach ($courses as $course) {

                    $segmentedUsersIds = $course->usersSegmented(
                        $course->segments, 'users_id'
                    );

                    SegmentationService::saveSegmentationStats(
                        Course::class,
                        $course->id,
                        $segmentedUsersIds
                    );

                    $coursesToBeHashed[] = [
                        'course' => $course,
                        'segmentedUsersIds' => $segmentedUsersIds
                    ];
                }

                // Hash courses segmentation, this is not performed in the
                // previous loop since is slower because the database is
                // busy with the segmentation queries

                foreach ($coursesToBeHashed as $courseToBeHashed) {
                    SegmentationService::hashSegmentation(
                        Course::class,
                        $courseToBeHashed['course']->id,
                        $courseToBeHashed['segmentedUsersIds']
                    );
                }

                DB::commit();

            } catch (\Exception $e) {

                DB::rollback();

                // Handle the exception or log the error
                dd($e->getMessage());
            }

            SegmentationService::replicateSegmentationStats(
                Course::class, $courses->pluck('id')
            );
        }

        SegmentationService::resetSegmentationStateFlag();

        $this->info('Segmentation count calculation has finished successfully.');

        return Command::SUCCESS;
    }
}
