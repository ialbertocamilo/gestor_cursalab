<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromCollection;

class ExportVotacionesCandidatos implements FromView
{
    private $candidates;
    private $campaign;
    
    public function __construct($candidates, $campaign)
    {
        $this->candidates = $candidates;
        $this->campaign = $campaign;
    }

    public function view(): View
    {
        $start_date = date('d/m/Y', strtotime($this->campaign->start_date));
        $end_date = date('d/m/Y', strtotime($this->campaign->end_date));

        return view('exportar.exportar_votacion_candidatos', [
            'candidates' => $this->candidates,
            'campaign' => $this->campaign,
            'start_date' => $start_date,
            'end_date' => $end_date
        ]);
    }
}