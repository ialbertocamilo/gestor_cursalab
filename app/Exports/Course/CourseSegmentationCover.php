<?php

namespace App\Exports\Course;

use App\Models\Course;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithDrawings;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class CourseSegmentationCover implements FromView, ShouldAutoSize, WithTitle, WithDrawings
{

    private $workspace;

    public function __construct($workspace)
    {
        $this->workspace = $workspace;
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Cursalab');
        $drawing->setDescription('Logo Cursalab');
        $drawing->setPath(public_path('img/logo_cursalab_v2_black.png'));
        $drawing->setHeight(80);
        $drawing->setCoordinates('B2');

        return $drawing;
    }

    public function view(): View
    {
        $workspace = $this->workspace;
        $view = 'cursos.export.course_segmentation_cover';

        $data['courses']['active_count'] = $courses = Course::whereRelationIn('workspaces', 'id', [$workspace->id])->where('active', ACTIVE)->count();
        $data['courses']['inactive_count'] = $courses = Course::whereRelationIn('workspaces', 'id', [$workspace->id])->where('active', '<>', ACTIVE)->count();

        return view($view, compact('workspace', 'data'));
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return "Datos generales";
    }
}
