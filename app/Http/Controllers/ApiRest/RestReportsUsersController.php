<?php

namespace App\Http\Controllers\ApiRest;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RestReportsUsersController extends Controller
{

    public function fetchUserHistoryFilters(Request $request): JsonResponse
    {
        $user = auth('api')->user();
        $url = rtrim(env('REPORTS_BASE_URL'), '/') . '/' .
            trim('/filtros/historial_usuario', '/');

        $filtersResponse = Http::post($url, [
            'document' => $user->document
        ]);

        // return reponse according status from external API

        if ($filtersResponse->status() === 200) {
            $body = json_decode($filtersResponse->body());
            return response()->json($body);

        } else {
            return response()->json([]);
        }
    }

    public function fetchUserHistory(Request $request): JsonResponse
    {
        // Perform request to reports API
        $user = auth('api')->user();
        $url = rtrim(env('REPORTS_BASE_URL'), '/') . '/' .
            trim('/exportar/historial_usuario', '/');

        $reportResponse = Http::post($url, [
            'document' => $user->document,
            'schoolId' => $request->schoolId,
            'page' => $request->page,
            'search' => $request->search,
            'type' => 'paginated'
        ]);

        // return reponse according status from external API

        if ($reportResponse->status() === 200) {
            $body = json_decode($reportResponse->body());
            return response()->json($body);

        } else {
            return response()->json([]);
        }

    }
}
