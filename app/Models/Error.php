<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

use App\Notifications\ErrorExceptionNotification;
use App\Notifications\ErrorNotification;

use hisorange\BrowserDetect\Parser as Browser;

class Error extends Model
{
    protected $fillable = [
        'message', 'file', 'file_path', 'line', 'stack_trace', 'code', 'ip', 'url',
        'status_id', 'platform_id', 'user_id', 'usuario_id',
        'platform_name', 'platform_family', 'platform_version',
        'browser_name', 'browser_family', 'browser_version',
        'device_family', 'device_model',
    ];

    // public function scopeById($query, $value)
    // {
    //     return $query->where('_id', 'like', "%$value%");
    // }

    // public function scopeByMessage($query, $value)
    // {
    //     return $query->where('message', 'like', "%$value%");
    // }

    // public function scopeByStackTrace($query, $value)
    // {
    //     return $query->where('stack_trace', 'like', "%$value%");
    // }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function platform()
    {
        return $this->belongsTo(Taxonomia::class, 'platform_id');
    }

    public function status()
    {
        return $this->belongsTo(Taxonomia::class, 'status_id');
    }

    public function getNotifierData($user, $platform_code)
    {
        if (!$user) return 'Anònimo';

        $notifier = $user->name;

        if ($platform_code == 'app')
            $notifier = $user->nombre . ' (' . $user->dni . ')';

        return $notifier;
    }

    protected function getPlatformByCode($code)
    {
        $platform = cache()->rememberForever('taxonomias_platforms_' . $code,  function () use ($code) {

            return Taxonomia::where('tipo', 'platform')->where('grupo', 'system')
                        ->where('code', $code)
                        ->where('estado', 1)
                        ->first();
        });

        return $platform;
    }

    protected function getStatusByCode($code)
    {
        $status = cache()->rememberForever('taxonomias_error_statuses_' . $code,  function () use ($code) {

            return Taxonomia::where('tipo', 'status')->where('grupo', 'error')
                        ->where('code', $code)
                        ->where('estado', 1)
                        ->first();
        });

        return $status;
    }

    protected function getErrorCode($exception)
    {
        $code = $exception->getCode();

        if ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface)
            $code = $exception->getStatusCode();

        return $code;
    }

    protected function storeAndNotificateException($exception, $request = null, $additional_message = '')
    {
        $request = $request ?? request();
        // Verificar si el guardado de errores está habilitado
        if (!config('errors.database_store_enable')) return false;

        $message = trim($exception->getMessage());

        $filename = str_replace(DIRECTORY_SEPARATOR, '', strrchr($exception->getFile(), DIRECTORY_SEPARATOR ));

        $code = Error::getErrorCode($exception);

        if ( $this->errorIsManagedByFormRequest($message, $filename) ) return false;
        if ( $this->errorIsTokenExpired($message, $filename) ) return false;
        if ( $this->errorIsFaviconIco($request->getRequestUri()) ) return false;
        if ( $this->errorIsUnauthenticated($message, $filename) ) return false;
        if ( $this->errorIsZoom3001($exception, $code, $additional_message) ) return false;
        if ( $this->errorIsJSMaps($request->getRequestUri(), $filename) ) return false;

        $platform_code = $request->is('api/*') ? 'app' : 'gestor';
        $user_field = $platform_code == 'app' ? 'usuario_id' : 'user_id';
        $platform = Error::getPlatformByCode($platform_code);
        $status = Error::getStatusByCode('pending');

        if ($message AND $additional_message)
            $message = $message . ' ' . $additional_message;

        if (!$message AND $additional_message)
            $message = $additional_message;

        if (!$message AND !$additional_message)
            $message = 'Error code ' . $code;

        $new_data = [
            'stack_trace' => $exception->getTraceAsString(),
            'message' => $message,
            'file' => $filename,
            'file_path' => $exception->getFile(),
            'line' => $exception->getLine(),
            $user_field => \Auth::id(),
            'code' => $code,
            'url' => $request->getRequestUri(),
            'ip' => $request->ip(),
            'platform_id' => $platform->id ?? NULL,
            'status_id' => $status->id ?? NULL,
        ];

        $new_data = $new_data + $this->getErrorUserDeviceInfo();

        $error = Error::create($new_data);

        // Verificar si las notificaciones por slack están habilitadas
        if (!config('slack.notification_enable')) return false;

        $this->sendErrorExceptionNotification($error, $platform_code);

        return true;
    }

    protected function storeAndNotificateAppException($data, $request)
    {
        // Verificar si el guardado de errores está habilitado
        if (!config('errors.database_store_enable')) return false;

        $platform = Error::getPlatformByCode('app');
        $status = Error::getStatusByCode('pending');

        $additional_message = $this->getZoomMessageFromEvent($data);

        if ($additional_message)
            $data['message'] .= ' ' . $additional_message;

        $new_data = [
            'stack_trace' => '',
            'message' => $data['message'],
            'file' => $data['method'] ?? '',
            'file_path' => '',
            'line' => '',
            'usuario_id' => \Auth::id(),
            'code' => $data['code'] ?? '',
            'url' => '',
            'ip' => $request->ip(),
            'platform_id' => $platform->id ?? NULL,
            'status_id' => $status->id ?? NULL,
        ];

        $new_data = $new_data + $this->getErrorUserDeviceInfo();

        $error = Error::create($new_data);

        // Verificar si las notificaciones por slack están habilitadas
        if (!config('slack.notification_enable')) return false;

        $this->sendErrorExceptionNotification($error, 'app');

        return true;
    }

    protected function storeAndNotificateDefault($message, $section = 'default', $subsection = 'default')
    {
        $request = request();

        // Verificar si el guardado de errores está habilitado
        if (!config('errors.database_store_enable')) return false;

        $code = 422;

        $platform_code = $request->is('api/*') ? 'app' : 'gestor';
        $user_field = $platform_code == 'app' ? 'usuario_id' : 'user_id';
        $platform = Error::getPlatformByCode($platform_code);
        $status = Error::getStatusByCode('pending');

        $new_data = [
            'message' => $message,
            $user_field => \Auth::id(),
            'code' => $code,
            'url' => $request->getRequestUri(),
            'ip' => $request->ip(),
            'platform_id' => $platform->id ?? NULL,
            'status_id' => $status->id ?? NULL,
        ];

        $new_data = $new_data + $this->getErrorUserDeviceInfo();

        $error = Error::create($new_data);

        // Verificar si las notificaciones por slack están habilitadas
        if (!config('slack.notification_enable')) return false;

        $this->sendErrorNotification($error, $section, $subsection);

        return true;
    }

    public function getZoomMessageFromEvent($data)
    {
        if (isset($data['evento_id']) AND $data['evento_id'])
        {
            $evento = Eventos::find($data['evento_id']);

            if ($evento)
            {
                $cuenta_zoom = $evento->cuenta_zoom;
                $estado = $cuenta_zoom->estado ? 'activo' : 'inactivo';

                return "[ Cuenta ID {$cuenta_zoom->id} => {$cuenta_zoom->correo} con estado {$estado} ]";
            }
        }

        return '';
    }


    public function sendErrorExceptionNotification($error, $platform_code)
    {
        $user = \Auth::user();

        $notifier = Error::getNotifierData($user, $platform_code);

        // Get user to send the notification
        $user = $user ?? User::find(1);

        $user->notify(new ErrorExceptionNotification($error, $notifier));

        return true;
    }

    public function sendErrorNotification($error, $section, $subsection)
    {
        // Get user to send the notification
        $user = User::find(1);

        $user->notify(new ErrorNotification($error, $section, $subsection));

        return true;
    }

    public function getErrorUserDeviceInfo()
    {
        return [

            'browser_name' => Browser::browserName(),
            'browser_family' => Browser::browserFamily(),
            'browser_version' => Browser::browserVersion(),

            'platform_name' => Browser::platformName(),
            'platform_family' => Browser::platformFamily(),
            'platform_version' => Browser::platformVersion(),

            'device_family' => Browser::deviceFamily(),
            'device_model' => Browser::deviceModel(),
        ];
    }

    protected function getUserDeviceInfo()
    {
        $browser_family = Taxonomy::getFirstOrCreate('browser', 'browser_family', Browser::browserFamily());
        $browser_version = Taxonomy::getFirstOrCreate('browser', 'browser_version', Browser::browserVersion(), $browser_family->id ?? NULL);

        $platform_family = Taxonomy::getFirstOrCreate('browser', 'platform_family', Browser::platformFamily());
        $platform_version = Taxonomy::getFirstOrCreate('browser', 'platform_version', Browser::platformVersion(), $platform_family->id ?? NULL);

        $device_family = Taxonomy::getFirstOrCreate('browser', 'device_family', Browser::deviceFamily());
        $device_model = Taxonomy::getFirstOrCreate('browser', 'device_model', Browser::deviceModel(), $device_family->id ?? NULL);

        return [

            'ip' => request()->ip(),

            // 'browser_name' => Browser::browserName(),
            'browser_family_id' => $browser_family->id ?? NULL,
            'browser_version_id' => $browser_version->id ?? NULL,

            // 'platform_name' => Browser::platformName(),
            'platform_family_id' => $platform_family->id ?? NULL,
            'platform_version_id' => $platform_version->id ?? NULL,

            'device_family_id' => $device_family->id ?? NULL,
            'device_model_id' => $device_model->id ?? NULL,
        ];
    }

    public function errorIsManagedByFormRequest($message, $filename)
    {
        if ($message == 'The given data was invalid.' AND $filename == 'FormRequest.php')
            return true;

        return false;
    }

    public function errorIsTokenExpired($message, $filename)
    {
        if ($message == 'Token has expired' AND $filename == 'BaseMiddleware.php')
            return true;

        return false;
    }

    public function errorIsUnauthenticated($message, $filename)
    {
        if ($message == 'Unauthenticated.' AND $filename == 'Authenticate.php')
            return true;

        return false;
    }

    public function errorIsZoom3001($exception, $code, $additional_message)
    {
        if ($additional_message)
        {
            $body = json_decode($exception->getResponse()->getBody());

            $body_code = $body->code ?? NULL;

            if ($body_code == 3001 AND $code == 404)
                return true;
        }

        return false;
    }

    public function errorIsFaviconIco($uri)
    {
        return $uri == '/favicon.ico';
    }

    public function errorIsJSMaps($uri, $filename)
    {
        $uris = [
            '/vendor/popper.js/umd/popper.min.js.map',
            '/vendor/bootstrap/js/bootstrap.min.js.map'
        ];

        if (in_array($uri, $uris) AND $filename == 'RouteCollection.php')
            return true;

        return false;
    }

    protected function search($request)
    {
        $query = self::with('user', 'usuario', 'platform', 'status');

        if ($request->q)
        {
            if (is_numeric($request->q))
            {
                $query->where('id', $request->q);

            } else {

                $query->where(function($q) use ($request){
                    $q->where('message', 'like', "%$request->q%");
                    $q->orWhere('file', 'like', "%$request->q%");
                    $q->orWhere('url', 'like', "%$request->q%");
                    $q->orWhere('ip', 'like', "%$request->q%");
                });
            }
        }

        if ($request->status)
            $query->where('status_id', $request->status);

        if ($request->platform)
            $query->where('platform_id', $request->platform);

        if (!$request->sortBy AND !$request->sortDesc)
        {
            $field = 'created_at';
            $sort = 'DESC';

        } else {

            $field = $request->sortBy ?? 'created_at';
            $sort = $request->sortDesc == 'true' ? 'DESC' : 'ASC';
        }

        $query->orderBy($field, $sort)->orderBy('id', $sort);

        return $query->paginate($request->paginate);
    }

}
