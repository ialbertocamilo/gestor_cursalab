<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class PushNotification extends Model
{
    const CHUNK = 500;
    const DELAY_ENVIO_INICIO = 2;
    const DELAY_ENVIO_INTERVALO = 15;

    protected $fillable = [
        'titulo', 'texto', 'destinatarios', 'creador_id',
        'id', 'success', 'failure', 'created_at'];

    protected $hidden = [
        'updated_at'
    ];

    public static function enviar($titulo, $texto, $usuarios_tokens, $data = [])
    {
        $result = null;
        try {

            $headers = array(
                'Content-Type: application/json',
                'Authorization: key=' . env('FIREBASE_API_KEY'),
            );

            $mPush = array(
                'title' => $titulo,
                'body' => $texto,
                'click_action' => 'FCM_PLUGIN_ACTIVITY'
            );

            $bodyPost = array(
                'registration_ids' => $usuarios_tokens,
                'notification' => $mPush,
//            'data' => $data
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyPost));
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result, true);
            info($result);
        } catch (\Exception $e) {
            info($e);
            Error::storeAndNotificateException($e, request());
        }

        return $result;
    }

    protected static function search($request)
    {
        $q = self::query();

        if ($request->q) {
            $q->where(function ($q_search) use ($request) {
                $q_search->where('titulo', 'like', "%$request->q%");
//                    ->orWhere('dni', 'like', "%$request->q%");
            });
        }

        if ($request->fecha) {
            $count_fechas = count($request->fecha);
            if ($count_fechas > 1) {

                $date_init = $request->fecha[0] < $request->fecha[1] ? $request->fecha[0] : $request->fecha[1] ;
                $date_final = $request->fecha[0] < $request->fecha[1] ? $request->fecha[1] : $request->fecha[0] ;
                $q->whereBetween('created_at',[$date_init,$date_final]);

            } else {
                $q->whereDate('created_at',$request->fecha[0]);
            }
        }

        $field = $request->sortBy ?? 'created_at';
        $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';

        $q->orderBy($field, $sort);

        return $q->paginate($request->paginate);
    }
}
