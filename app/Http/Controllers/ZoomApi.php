<?php

namespace App\Http\Controllers;

use App\Models\CuentaZoom;
use Firebase\JWT\JWT;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ZoomApi extends Controller
{
    public function generateJWTKey($cuenta_zoom_id) {
        $dataZoom = CuentaZoom::find($cuenta_zoom_id);

        $key = $dataZoom->api_key;
        $secret = $dataZoom->client_secret;
        $token = array(
            "iss" => $key,
            "exp" => time() + 3600
        );
        $token = JWT::encode( $token, $secret , 'HS256');
        $dataZoom->refresh_token = $token;
        $dataZoom->save();
        return  $token;
    }

    // public function regenerateSDK_ZAK_token()
    // {
    //     $this->sdk_token = $this->regenerarToken('token', $this->zoom_userid, $this->refresh_token);
    //     $this->zak_token = $this->regenerarToken('zak', $this->zoom_userid, $this->refresh_token);

    //     $this->save();

    //     return ['sdk_token' => $this->sdk_token, 'zak_token' => $this->zak_token];
    // }

     public function regenerateSdk_ZAK_token(CuentaZoom $zoom_acc)
     {
         $zoom_acc->sdk_token = Zoom::getToken($zoom_acc, 'token');
         $zoom_acc->zak_token = Zoom::getToken($zoom_acc, 'zak');

         $zoom_acc->save();

         return $this->success(compact('zoom_acc'));
     }
}
