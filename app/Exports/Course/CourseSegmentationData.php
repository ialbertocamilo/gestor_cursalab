<?php

namespace App\Exports\Course;

use App\Models\Course;
use App\Models\Workspace;

use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class CourseSegmentationData implements FromView, WithTitle, ShouldAutoSize, WithEvents
{
    use Exportable;

    protected $workspace;

    public function __construct($workspace)
    {
        $this->workspace = $workspace;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $courses = Course::getSegmentationDataByWorkspace($this->workspace);

        return view('cursos.export.course_segmentation_data', compact('courses'));
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
        return "Segmentación de cursos";
    }
}
