<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class Dc3Controller extends Controller
{
    public function generatePDF()
    {
        $data = [
            'title' => 'Mi primer PDF con Laravel',
            'content' => '¡Hola, este es el contenido de mi PDF!',
        ];

        $pdf = PDF::loadView('pdf.dc3', $data);

        return $pdf->download('mi_pdf.pdf');
    }
}
