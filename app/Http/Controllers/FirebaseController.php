<?php

namespace App\Http\Controllers;

use Config;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Eventos;

use Illuminate\Http\Request;

class FirebaseController extends Controller
{
    public function notificationValues()
    {
        $data = config('app.notifications');

        return response()->json($data);
    }

    public function appVersions()
    {
        return response()->json(['data' => config('app.versions')], 200);
    }

    public function guardarToken(Request $request)
    {
        auth()->user()->update(['token_firebase' => $request->fcm_token]);

        return response()->json(['msg' => 'Token guardado.'], 200);
    }

    // PRUEBAS NOTIFICACIONES INDIVIDUALES PUSH
    public function test_push_firebase()
    {
        $usuario = Usuario_rest::find(7432);
        // dd($usuario->token_firebase);

        $headers = array(
            'Authorization: key=' . env('FIREBASE_API_KEY'),
            'Content-Type: application/json'
        );

        $mPush = array(
            'title' => '2020-11-16 11:48',
            'body' => 'test individual 1'
        );

        $bodyPost = array(
            'to' => 'f60M3lByiBc:APA91bHpjIQ2m0jCOmrSiMp2XKaZB6uu3B66oihwRyusm9t11AdWcy9nNTqgfIXrGqxxfUEw-tfW-AZnNA-PxEWxeypFapGOqUA6QJEYk6L4k2aLvgEa39vSDeF2SMhEiuCcY6A6YOkA',
            'notification' => $mPush
        );
        // ENVIAR NOTIFICACION PUSH
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyPost));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    // PRUEBAS CREAR GRUPOS FIREBASE
    public function test_agregar_grupo_firebase()
    {
        $usuario = Usuario_rest::find(7432, ['token_firebase']);

        $headers = array(
            'Content-Type: application/json',
            'Authorization: key=' . env('FIREBASE_API_KEY'),
            'project_id:' . env('FIREBASE_SENDER_ID')
        );

        $bodyPost = array(
            "operation" => "create",
            "notification_key_name" => "grupo-test9",
            "registration_ids" => [$usuario->token_firebase]
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/notification');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyPost));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);

        return $result;
        // return response()->json(json_decode($result)->notification_key, 200);
    }

    // PRUEBAS ENVIAR PUSH A GRUPO FIREBASE
    public function enviar_mensaje_grupo_firebase()
    {
        $usuario = Usuario_rest::find(18528, ['token_firebase']);

        $headers = array(
            'Content-Type: application/json',
            'Authorization: key=' . env('FIREBASE_API_KEY'),
        );

        $mPush = array(
            'title' => 'Test message desde grupo',
            'body' => 'body message desde grupo'
        );

        $bodyPost = array(
            'to' => 'APA91bHTWIx67CpqKCNWts7nKgF5knT2qW5LN-RqlQfdrzgnEC9l0XEbeQB31LaFcZHf3qPkH9z8iKpbCGK7gVJgPDCAHn6uf4P5_FaPwuTQ8pY5-zpaisU',
            'notification' => $mPush
        );

        // ENVIAR NOTIFICACION PUSH
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyPost));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    public function crearGrupoFirebase($asistentes, $evento_id)
    {
        // return $asistentes;
        $headers = array(
            'Content-Type: application/json',
            'Authorization: key=' . env('FIREBASE_API_KEY'),
            'project_id:' . env('FIREBASE_SENDER_ID')
        );

        $bodyPost = array(
            "operation" => "create",
            "notification_key_name" => "reunion-zoom-test-" . $evento_id,
            "registration_ids" => $asistentes
        );

        // ENVIAR NOTIFICACION PUSH
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/notification');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyPost));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        // \Log::info('-------------------------Log FirebaseController---------------------------');
        // \Log::info($result);
        $evento = Eventos::find($evento_id);
        $evento->firebase_grupo = "reunion-zoom-test-" . $evento_id;
        $evento->firebase_grupo_key = json_decode($result)->notification_key;
        $evento->save();
        // \Log::info('-------------------------Fin Log FirebaseController---------------------------');
        return json_decode($result)->notification_key;
    }

    public function enviarNotificacionesGrupo($title, $body, $arrayTokens)
    {
        $headers = array(
            'Content-Type: application/json',
            'Authorization: key=' . env('FIREBASE_API_KEY'),
        );

        $mPush = array(
            'title' => $title,
            'body' => $body
        );


        $bodyPost = array(
            'registration_ids' => $arrayTokens,
            'notification' => $mPush
        );

        // ENVIAR NOTIFICACION PUSH
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bodyPost));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
}
