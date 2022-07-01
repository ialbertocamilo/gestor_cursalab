<?php

namespace App\Exports\Meeting;

use App\Exports\Meeting\MeetingAttendantsExport;
use App\Exports\Meeting\MeetingDetail;

use App\Models\Meeting;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MeetingExport implements WithMultipleSheets
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
