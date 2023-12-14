<?php

namespace App\Http\Controllers;

use App\Models\Taxonomy;
use Barryvdh\DomPDF\PDF;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\NationalOccupationCatalog;

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
    public function generatePDFDownload(){
        $national_occupations_catalog = NationalOccupationCatalog::select('code','name')->get()->toArray();
        $catalog_denominations = Taxonomy::where('group','course')->where('type','catalog-denomination-dc3')->select('code','name')->get()->toArray();
        $data = [
            'national_occupations_catalog'=>$national_occupations_catalog,
            'catalog_denominations'=>$catalog_denominations,
            "title"=>'74130119-sostenibilidad',
            "user" => [
              "name" => \Str::title("Marisol CABRERA CABRERA"),
              "curp" => '145L0789asd',
              "document" => "74130119",
              "occupation" => '01.2',
              "position" => "Asistente de Talento y Desarrollo"
            ],
            "subworkspace" => [
              "id" => 4,
              "name_or_social_reason" => "Intercorp IRC",
              "shcp" => "IMF1-70223A702",
              "subworkspace_logo" => get_media_url("images/wrkspc-1-logo-corporativo-1-02-20220829130652-iSA1RxfF31iv2fD.png",'s3')
            ],
            "course" =>  [
              "id" => 112,
              "name" => "Sostenibilidad",
              "duration" => "0.35",
              "instructor" => "Aldo Ramirez",
              "instructor_signature" => get_media_url("images/wrkspc-1-1-20231205170625-dapwpNAKnyK4lPL.png","s3"),
              "legal_representative" => "Representante 3",
              "legal_representative_signature" => get_media_url("images/wrkspc-1-1-20231205173447-5s3MNfRDbDb8HFB.png","s3"),
              "catalog_denomination_dc3" => "8000",
              "init_date_course_year" => 2023,
                "init_date_course_month" => 12,
                "init_date_course_day" => 5,
                "final_date_course_year" => 2023,
                "final_date_course_month" => 12,
                "final_date_course_day" => 5
            ]
        ];
        $pdf = PDF::loadView('pdf.dc3', $data);
        return $pdf->download('prueba.pdf');
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
