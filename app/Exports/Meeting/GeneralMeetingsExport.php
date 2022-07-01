<?php

namespace App\Exports\Meeting;

use App\Exports\Meeting\MeetingAttendantsExport;
use App\Exports\Meeting\MeetingDetail;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

use App\Models\Meeting;

class GeneralMeetingsExport implements WithMultipleSheets
{
    use Exportable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new MeetingDetail($this->data);
        $sheets[] = new MeetingAttendantsExport($this->data);

        return $sheets;
    }
}
