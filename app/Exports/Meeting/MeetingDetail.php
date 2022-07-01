<?php

namespace App\Exports\Meeting;

use App\Models\Meeting;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use function foo\func;

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
            $drawing->setPath(public_path('img/cursalab-logo.png'));
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

        if (isset($data['meeting'])):

            $view = 'meetings.exports.meetings_details_export';
            $meeting = $data['meeting'];
            $view_data['meeting'] = $meeting;

        else:

            $view = 'meetings.exports.general_meetings_details_export';
            $meetings = Meeting::query()
                ->when($data['starts_at'] ?? null, function ($q) use ($data) {
                    $q->whereDate('starts_at', '>=', $data['starts_at']);
                })
                ->when($data['finishes_at'] ?? null, function ($q) use ($data) {
                    $q->whereDate('starts_at', '<=', $data['finishes_at']);
                })
                ->withCount('attendants')
                ->withCount('attendantsWithFirstLogintAt')
                ->get();

//            dd($meetings[0]->attendants_with_first_logint_at_count);

            $view_data['meetings'] = $meetings;

        endif;

//        dd($view, $view_data);

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
