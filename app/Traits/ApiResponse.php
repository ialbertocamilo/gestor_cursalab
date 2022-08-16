<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Symfony\Component\HttpFoundation\Response as ResponseCode;

trait ApiResponse
{

    public function success(mixed $data = [], string $message = '', int $http_code = ResponseCode::HTTP_OK): JsonResponse
    {
        return $this->json(data: $data, message: $message, type: 'success', http_code: $http_code);
    }

    public function error(string $message = '', int $http_code = ResponseCode::HTTP_BAD_REQUEST): JsonResponse
    {
        return $this->json(message: $message, type: 'error', http_code: $http_code);
    }

    public function json(mixed $data = [], string $message = '', string $type = 'info', mixed $http_code = ResponseCode::HTTP_OK): JsonResponse
    {
        return response()->json(get_defined_vars(), $http_code);
    }

    public function successApp($data, int $code = ResponseAlias::HTTP_OK): JsonResponse
    {
        return response()->json($data, $code);
    }

    public function errorApp($data, $code = 500)
    {
        return response()->json($data, $code);
    }
    // static function notification(mixed $data, mixed $message, mixed $message_type = 'notification', int $http_code = 200)
    // {
    //     $instance = new self(data: $data, message_text: $message, message_type: $message_type);

    //     return $instance->json($http_code);
    // }

}
