<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class Dc3Controller extends Controller
{
    public function generatePDF($data)
    {
        $title = $this->setTitle($data);
        $fileName = $this->setNameDC3PDF($title);
        $filePath = 'dc3/'.$fileName;
        $data['title'] = $title;
        $pdf = PDF::loadView('pdf.dc3', $data);
        // Guardar en S3
        
        Storage::disk('s3')->put($filePath, $pdf->output());
        // Devolver la URL del archivo en S3
        return $filePath;
    }
    
    private function setNameDC3PDF($title,$ext='pdf'){
        $str_random = Str::random(4);
        // // workspace creation reference
        // $workspace_code = 'wrkspc-' . $data['subworkspace']['id'];
        // $course_code = 'crs-'.$data['course']['id'];
        // $document = 'dc-'.$data['user']['document'];
        // $name = $workspace_code . '-' . $course_code . '-' . $document . '-' . $str_random;
        $fileName = $title . '-' . $str_random. '.' . $ext;
        return $fileName;
    }
    private function setTitle($data){
        $slug = Str::slug($data['course']['name']);
        $title = $data['user']['document'].'-'.$slug; 
        return $title;
    }
}
