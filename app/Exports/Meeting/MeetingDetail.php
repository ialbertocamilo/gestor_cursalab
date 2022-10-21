<?php

namespace App\Exports\Meeting;

use App\Models\Meeting;

use App\Models\Workspace;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class MeetingDetail implements FromView, ShouldAutoSize, WithTitle, WithDrawings
{

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function drawings()
    {
        $data = $this->data;

        if (isset($data['meeting'])):
            $drawing = new Drawing();

            $drawing->setName('Cursalab');
            $drawing->setDescription('Logo Cursalab');
            $drawing->setPath(public_path('img/logo_cursalab.png'));
            $drawing->setHeight(80);
            $drawing->setCoordinates('B2');
            return $drawing;

        endif;
        return [];
    }

    public function view(): View
    {
        $data = $this->data;
        $view_data = [];

        if (isset($data['meeting'])) {

            $view = 'meetings.exports.meetings_details_export';
            $meeting = $data['meeting'];
            $view_data['meeting'] = $meeting;

        } else {

            $view = 'meetings.exports.general_meetings_details_export';
            $meetings = Meeting::query()
                ->when($data['starts_at'] ?? null, function ($q) use ($data) {
                    $q->whereDate('starts_at', '>=', $data['starts_at']);
                })
                ->when($data['finishes_at'] ?? null, function ($q) use ($data) {
                    $q->whereDate('starts_at', '<=', $data['finishes_at']);
                })
                ->where('workspace_id', get_current_workspace()->id)
                ->withCount('attendants')
                ->withCount('attendantsWithFirstLogintAt')
                ->get();

            $view_data['meetings'] = $meetings;

        }

        return view($view, $view_data);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return "Datos Generales";
    }
}
