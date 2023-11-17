<?php

namespace App\Exports\Course;

// use App\Exports\Meeting\MeetingAttendantsExport;
// use App\Exports\Meeting\MeetingDetail;

// use App\Models\Meeting;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CourseSegmentationExport implements WithMultipleSheets
{
    use Exportable;

    protected $workspace;

    public function __construct($workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new CourseSegmentationCover($this->workspace);
        $sheets[] = new CourseSegmentationData($this->workspace);

        return $sheets;
    }
}
