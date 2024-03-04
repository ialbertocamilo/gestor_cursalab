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
        // $count_ambiente = Ambiente::count();

        // info(['data' => $data ]);

        //gestor
        if($data['type'] == 'general'){
            $data = Media::requestUploadFile($data, 'fondo');
            $data = Media::requestUploadFile($data, 'logo');
            $data = Media::requestUploadFile($data, 'icono');
            $data = Media::requestUploadFile($data, 'logo_empresa');
        }
        if($data['type'] == 'workspace'){
            $data['workspace_id'] = get_current_workspace()->id;
        }

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

        // When there is at least one app color selected,
        // if the other ones are not selected, set black
        // (#0000000) by default

        if (isset($data['color_primario_app']) ||
            isset($data['color_secundario_app']) ||
            isset($data['color_terciario_app'])) {

            if (!isset($data['color_primario_app'])) $data['color_primario_app'] = '#000000';
            if (!isset($data['color_secundario_app'])) $data['color_secundario_app'] = '#000000';
            if (!isset($data['color_terciario_app'])) $data['color_terciario_app'] = '#000000';
        }

        Ambiente::updateOrCreate(
        [
            'type' => $data['type'],
            'workspace_id' => $data['type'] == 'workspace' ? $data['workspace_id'] : null
        ]
        ,$data);


        return $this->success(['msg' => 'Ambiente guardado correctamente.']);
    }

    public function edit($type)
    {
        $ambiente = $type == 'general'
            ? Ambiente::whereNull('workspace_id')->where('type','general')->first()
            : Ambiente::whereNotNull('workspace_id')->where('workspace_id',get_current_workspace()->id)->where('type','workspace')->first();
        if($ambiente) {
            $ambiente['show_blog_btn'] = (bool) $ambiente->show_blog_btn;
            $ambiente['is_superuser'] = auth()->user()->isAn('super-user');
            $ambiente['app_password_expiration_days'] = config('app.passwords.app.expiration_days');

            return $this->success($ambiente);
        }
        return $this->success($ambiente);
    }
}
