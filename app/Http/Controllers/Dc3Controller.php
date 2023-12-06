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
        // Guardar en S3
        $filePath = 'pdfs/mi_pdf.pdf';
        Storage::disk('s3')->put($filePath, $pdf->output());
        // Devolver la URL del archivo en S3
        return Storage::disk('s3')->url($filePath);
    }
}
