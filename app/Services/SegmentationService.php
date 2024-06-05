<?php

namespace App\Services;

use App\Models\Course;
use App\Models\SegmentationCount;
use App\Models\SegmentedUser;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SegmentationService {

    /**
     * Calculate HASH and save of course's segmented users
     * @return void
     */
    public static function hashSegmentation($modelType, $modelId, $usersIds) {

        if ($modelType === 'App\\Models\\Course') {
            $course = Course::find($modelId);
            if ($course) {
                rsort($usersIds);
                $hash = md5(implode('', $usersIds));
                $course->segmentation_hash = $hash;
                $course->save();
            }
        }
    }

    /**
     * Save segmented users list
     * @return void
     */
    public static function saveUsersList($modelType, $modelId, $usersIds) {

        // Delete previous records

        SegmentedUser::query()
            ->where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->delete();

        // Save new list of items

        $records = [];
        foreach ($usersIds as $userId) {
            $records[] = [
                'model_type' => $modelType,
                'model_id' => $modelId,
                'user_id' => $userId,
                'created_at' => now()
            ];
        }

        // Insert the records in a single query

        DB::table('segmented_users')->insertOrIgnore($records);

    }

    /**
     * Replicate users count to other records with the same
     * segmentation hash
     * @return void
     */
    public static function replicateSegmentationStats($modelType, $modelIds) {

        try {
            DB::beginTransaction();

            if ($modelType === 'App\\Models\\Course') {
                $courses = Course::whereIn('id', $modelIds)->get();
                foreach ($courses as $course) {

                    $courseUsersCount = SegmentationCount::query()
                        ->where('model_type', $modelType)
                        ->where('model_id', $course->id)
                        ->first()
                        ->users_count;

                    $courseUsersIds = SegmentedUser::query()
                        ->where('model_type', $modelType)
                        ->where('model_id', $course->id)
                        ->get()
                        ->pluck('user_id')
                        ->toArray();

                    // Get courses with the segmentation
                    // hash of the provided course

                    $coursesIdsToBeUpdated = Course::query()
                        ->whereNot('id', $course->id)
                        ->whereNotNull('segmentation_hash')
                        ->where('segmentation_hash', $course->segmentation_hash)
                        ->select('id')
                        ->get()
                        ->pluck('id')
                        ->toArray();

                    foreach ($coursesIdsToBeUpdated as $id) {

                        // Update stats

                        SegmentationCount::updateOrCreate(
                            [
                                'model_type' => $modelType,
                                'model_id' => $id
                            ],
                            ['users_count' => $courseUsersCount]
                        );

                        // Update users list

                        SegmentationService::saveUsersList(
                            Course::class,
                            $id,
                            $courseUsersIds
                        );
                    }
                }
            }
            DB::commit();

        } catch (\Exception $e) {

            DB::rollback();

            // Handle the exception or log the error
            dd($e->getMessage());
        }
    }

    /**
     * Get courses that belongs to modules in which
     * segmentation has changed
     * @return Builder[]|Collection
     */
    public static function getCoursesWhereSegmentationStateHasChange(): Collection|array
    {
        $subworkspacesIds = Workspace::query()
        ->where(function ($q) {
            $q->where('segmentation_config->has_changed', true);
        })
        ->whereNotNull('parent_id')
        ->get()
        ->pluck('id');

        $query = Course::query()
            ->join('course_school', 'course_school.course_id', '=', 'courses.id')
            ->join('schools', 'schools.id', '=', 'course_school.school_id')
            ->join('school_subworkspace', 'schools.id', '=', 'school_subworkspace.school_id')
            ->whereIn('school_subworkspace.subworkspace_id', $subworkspacesIds)
            ->where('courses.active', 1)
            ->select(['courses.id','courses.segmentation_hash']);

        $withHashes = (clone $query)
            ->whereNotNull('courses.segmentation_hash')
            ->groupBy('courses.segmentation_hash')
            ->get();

        $withoutHashes = (clone $query)
            ->whereNull('courses.segmentation_hash')
            ->get();

        return $withHashes->merge($withoutHashes);
    }

    /**
     * Reset segmentation state flag from all workspaces
     * @return void
     */
    public static function resetSegmentationStateFlag() {

        $workspaces = Workspace::query()
            ->where(function ($q) {
                $q->where('segmentation_config->has_changed', true);
            })
            ->whereNotNull('parent_id')
            ->get();

        foreach ($workspaces as $workspace) {
            $config = $workspace->segmentation_config;
            $config->has_changed = false;
            $workspace->segmentation_config = $config;
            $workspace->save();
        }
    }

    /**
     * Set segmentation state flag to true on provided workspace
     * @param $workspaceId
     * @return void
     */
    public static function segmentationStateHasChanged($workspaceId) {

        $workspace = Workspace::find($workspaceId);

        $config = $workspace->segmentation_config;
        $config->has_changed = true;
        $workspace->segmentation_config = $config;
        $workspace->save();
    }

    /**
     * Save segmentation data like users count for segmented course,
     * ids of segmented users, and users that has been removed
     * from segmented course, with this data, segmentation history can
     * be calculated
     * @return void
     */
    public static function saveSegmentationStats($modelType, $modelId, $usersIds) {

        SegmentationCount::updateOrCreate(
            [
                'model_type' => $modelType,
                'model_id' => $modelId
            ],
            ['users_count' => count($usersIds)]
        );

        // todo: Save users ids .........

    }
}
