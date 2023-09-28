<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AmbienteRequest;
use App\Models\{ Ambiente, Media };

class AmbienteController extends Controller
{
    public function updateStore(AmbienteRequest $request) 
    {
        $data = $request->all();
        $count_ambiente = Ambiente::count();

        // info(['data' => $data ]);

        //gestor
        $data = Media::requestUploadFile($data, 'fondo');
        $data = Media::requestUploadFile($data, 'logo');
        $data = Media::requestUploadFile($data, 'icono');
        $data = Media::requestUploadFile($data, 'logo_empresa');

        //app
        $data = Media::requestUploadFile($data, 'fondo_app');
        $data = Media::requestUploadFile($data, 'fondo_invitados_app');

        $data = Media::requestUploadFile($data, 'logo_app');
        $data = Media::requestUploadFile($data, 'logo_cursalab');
        $data = Media::requestUploadFile($data, 'completed_courses_logo');
        $data = Media::requestUploadFile($data, 'enrolled_courses_logo');
        $data = Media::requestUploadFile($data, 'diplomas_logo');
        $data = Media::requestUploadFile($data, 'male_logo');
        $data = Media::requestUploadFile($data, 'female_logo');

        if ($count_ambiente) {
            $ambiente = Ambiente::first();
            $ambiente->update($data);
        } else {
            $ambiente = new Ambiente;
            $ambiente->create($data); 
        }

        return $this->success(['msg' => 'Ambiente guardado correctamente.']);
    }
    
    public function edit() 
    {
        $ambiente = Ambiente::first();
     
        if($ambiente) {
            $ambiente['show_blog_btn'] = (bool) $ambiente->show_blog_btn;
            $ambiente['is_superuser'] = auth()->user()->isAn('super-user');
           
            return $this->success($ambiente);
        }
        return $this->success($ambiente);
    }
}
