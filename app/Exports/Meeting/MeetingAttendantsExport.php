<?php

namespace App\Exports\Meeting;

use App\Models\Attendant;
use App\Models\Meeting;

use App\Models\Workspace;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MeetingAttendantsExport implements FromView, WithTitle, ShouldAutoSize, WithEvents
{
    use Exportable;

    private $filter;

    protected $data;
    protected $meeting;

    public function __construct($data, Meeting $meeting = null)
    {
        $this->data = $data;
        $this->meeting = $meeting;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $data = $this->data;

        $attendants = Attendant::with('usuario', 'user.subworkspace', 'type', 'meeting')
            ->when($data['meeting'] ?? null, function ($q) use ($data) {
                $q->where('meeting_id', $data['meeting']->id);
            })
            ->when($data['starts_at'] ?? null, function ($q) use ($data) {
                $q->whereHas('meeting', function ($q2) use ($data) {
                    $q2->whereDate('starts_at', '>=', $data['starts_at']);
                });
            })
            ->when($data['finishes_at'] ?? null, function ($q) use ($data) {
                $q->whereHas('meeting', function ($q2) use ($data) {
                    $q2->whereDate('starts_at', '<=', $data['finishes_at']);
                });
            })
            ->whereHas('meeting', function($q) {
                $q->where('workspace_id', get_current_workspace()->id);
            })
            ->orderBy('meeting_id')
            ->get();

        // dd($attendants);

        $isAllowedToViewAll = auth()->user()->isMasterOrAdminCursalab();

        info("PUEDE VER TODO: " . $isAllowedToViewAll);
        return view('meetings.exports.meetings_attendants_export', compact('attendants','isAllowedToViewAll'));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $workSheet = $event->sheet->getDelegate();

                $workSheet->getStyle('A1:AH2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $workSheet->freezePane('A2'); // freezing here
                // $workSheet->freezePane('A3'); // freezing here
            },
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return "Detalle de asistentes";
    }
}
