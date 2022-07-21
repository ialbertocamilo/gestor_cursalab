<?php

namespace App\Models;

class UsuarioAyuda extends BaseModel
{
    protected $table = "usuario_ayuda";

    protected $fillable = [
        'usuario_id',
        'motivo',
        'detalle',
        'contacto',
        'info_soporte',
        'msg_to_user',
        'estado',
        'created_at',
        'updated_at'
    ];

    public function usuario()
    {
        return $this->belongsTo('App\Usuario', 'usuario_id');
    }

    public static function send_message_to_slack($mensaje)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('LOG_SLACK_WEBHOOK_URL'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"text\":\"$mensaje\"}");
        // curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['text'=>$mensaje]));
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
    }

    protected function search($request)
    {
        $query = self::with('usuario.config');

        if ($request->q || $request->modulo)
        {
            $query->where(function($qu) use ($request){

                $qu->whereHas('usuario', function($q) use ($request) {

                    if ($request->q)
                    {
                        $q->where('nombre', 'like', "%$request->q%");

                        if (strlen($request->q) > 4)
                            $q->orWhere('dni', 'like', "%$request->q%");
                    }

                    if ($request->modulo)
                        $q->where('config_id', $request->modulo);
                });

                if ($request->q)
                    $qu->orWhere('id', 'like', "%$request->q%");
            });

        }

        if ($request->estado)
            $query->where('estado', $request->estado);

        if ($request->starts_at)
            $query->whereDate('created_at', '>=', $request->starts_at);

        if ($request->ends_at)
            $query->whereDate('created_at', '<=', $request->ends_at);

        $request->sortBy = $request->sortBy == 'status' ? 'estado' : $request->sortBy;

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $query->orderBy($field, $sort);

        return $query->paginate($request->paginate);
    }

}
