<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Redis;

class TestController extends Controller
{
    public function storeValuesInRedis()
    {
        // Obtén los valores desde el archivo .env
        $appName = env('APP_NAME');
        $customerId = env('CUSTOMER_ID');
        $customerSlug = env('CUSTOMER_SLUG');

        // Crea un array asociativo con los valores
        $values = [
            'APP_NAME' => $appName,
            'CUSTOMER_ID' => $customerId,
            'CUSTOMER_SLUG' => $customerSlug,
        ];

        // Almacena el array en Redis con una clave específica
        Redis::set('env_values_in_redis', json_encode($values));

        return "Valores almacenados en Redis.";
    }

    public function retrieveValuesFromRedis()
    {
        // Recupera el array de valores desde Redis
        $valuesFromRedis = json_decode(Redis::get('env_values_in_redis'), true);

        // Imprime los valores obtenidos
        return "Valores almacenados en Redis: " . implode(', ', $valuesFromRedis);
    }
}
