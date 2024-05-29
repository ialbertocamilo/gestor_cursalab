<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SegmentValue extends BaseModel
{
    protected $table = 'segments_values';

    protected $fillable = [
        'segment_id', 'criterion_id', 'criterion_value_id', 'type_id',
        'starts_at', 'finishes_at',
        'days_less_than', 'days_greater_than', 'days_duration'
    ];

    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    // public function courses()
    // {
    //     return $this->belongsToMany(Course::class, 'segment_course');
    // }

    // public function requirements()
    // {
    //     return $this->hasMany(SegmentRequirement::class);
    // }
    public function type()
    {
        return $this->belongsTo(Taxonomy::class, 'type_id');
    }

    public function criterion()
    {
        return $this->belongsTo(Criterion::class);
    }

    public function criterion_value()
    {
        return $this->belongsTo(CriterionValue::class);
    }

    // public function values()
    // {
    //     return $this->hasMany(SegmentValue::class);
    // }

    /**
     * Load segment criteria ids used in a specific workspace
     *
     * @return array
     */
    public static function loadWorkspaceSegmentationCriteriaIds($workspaceId) {

        // Load workspace's courses ids

        $_courseIds = DB::select(DB::raw('
            select distinct course_id
            from course_workspace
            where workspace_id = :workspaceId'), ['workspaceId' => $workspaceId]);
        $courseIds = collect($_courseIds)->pluck('course_id')->toArray();


        // Load criteria ids used in workspace

        $segmentValues = Segment::query()
            ->join('segments_values as sv', 'segments.id', 'sv.segment_id')
            ->join('criteria as c', 'c.id', 'sv.criterion_id')
            ->where('model_type', 'App\\Models\\Course')
            ->where('segments.active', 1)
            ->where('c.active', 1)
            ->whereIn('model_id', $courseIds)
            ->groupBy('criterion_id')
            ->orderBy('criterion_id')
            ->get();

        return $segmentValues->pluck('criterion_id')->toArray();
    }

    public function getCriterionValueText()
    {
        $segment_value = $this;
        $text = $segment_value->criterion_value->value_text ?? 'NOT_DEFINED';

        if($segment_value->starts_at && $segment_value->finishes_at) {

            if ($segment_value->starts_at == $segment_value->finishes_at) {

                $text = Carbon::parse($segment_value->starts_at)->format('d/m/Y');

            } else {
                $text = Carbon::parse($segment_value->starts_at)->format('d/m/Y') . ' - ' .
                Carbon::parse($segment_value->finishes_at)->format('d/m/Y');
            }
        }

        return $text;
    }
}
