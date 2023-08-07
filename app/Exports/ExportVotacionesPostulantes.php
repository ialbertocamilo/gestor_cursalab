<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportVotacionesPostulantes implements FromView
{
    private $postulates;
    private $campaign;
    
    public function __construct($postulates, $campaign)
    {
        $this->postulates = $postulates;
        $this->campaign = $campaign;
    }

    public function view(): View
    {
        $start_date = date('d/m/Y', strtotime($this->campaign->start_date));
        $end_date = date('d/m/Y', strtotime($this->campaign->end_date));

        return view('exportar.exportar_votacion_postulantes', [
            'postulates' => $this->postulates,
            'campaign' => $this->campaign,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }
}