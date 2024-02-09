<?php

namespace App\Models\Mongo;
use Jenssegers\Mongodb\Eloquent\Model;

class WorkspaceCustomEmail extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'workspace_custom_email';

    private $customEmails = [
        [
            'title'=>'Correo de bienvenida',
            'code'=>'welcome-email',
            'default'=> [
                'content' => '<div style="text-align: center;">Nos emociona darte la bienvenida a la familia Cursalab. Esperamos que tu tiempo con nosotros est&eacute; lleno de oportunidades emocionantes, aprendizaje y crecimiento.<br /><strong>&iexcl;Bievenido a bordos!<br /><br /></strong></div>
                <div style="text-align: center;">Recuerda que tu ingreso a la plataforma se realizar&aacute; con tu <strong>n&uacute;mero de documento</strong> como usuario y contrase&ntilde;a.</div>',
                'title'=>'Bievenido a Cursalab',
                'show_subworkspace_logo'=>false
            ]
        ] //code email
    ];
    protected $fillable = [
        'workspace_id', 
        'code_email',
        'data_custom',
    ];

    protected function list($workspace){
        $emails = $this->customEmails;
        $data = [];
        
        
        foreach ($emails as $email) {
            $workspace_email = self::where('workspace_id',$workspace->id)->where('code_email',$email['code'])->first();
            $data[] = [
                'title' => $email['title'],
                'code_email' => $email['code'],
                'data_custom' => $workspace_email['data_custom'] ?? $email['default'],
                'expand' => ['status'=>true]
            ];
        }
        return $data;
    }

    protected function search($workspace,$code_email){
        return self::where('workspace_id',$workspace->id)->where('code_email',$code_email)->first();
    }

    protected function saveCustomEmail($workspace,$data){
        $custom_email = $data['custom_email'];
        
        $email = self::where('workspace_id',$workspace->id)->where('code_email',$custom_email['code_email'])->first();
        if($email){
            $email->data_custom = $custom_email['data_custom'];
            $email->save();
        }else{
            self::insert([
                'workspace_id' => $workspace->id,
                'code_email' => $custom_email['code_email'],
                'data_custom' => $custom_email['data_custom']
            ]);
        }
    }
}
