<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use App\Models\Carrera;

class  MeetingService extends Model
{
    protected function getInitialDates($date_time, $duration)
    {
        $starts_at = Carbon::createFromFormat('Y-m-d H:i:s', $date_time);
        $finishes_at = Carbon::createFromFormat('Y-m-d H:i:s', $date_time);
        $finishes_at->addMinutes($duration);

        return [
            'timestamp' => [
                'starts_at' => $starts_at->format('Y-m-d H:i:s'),
                'finishes_at' => $finishes_at->format('Y-m-d H:i:s'),
            ],
            'unixtime' => [
                'starts_at' => $starts_at->timestamp,
                'finishes_at' => $finishes_at->timestamp,
            ],
            'carbon' => [
                'starts_at' => $starts_at,
                'finishes_at' => $finishes_at,
            ],
        ];
    }

    public function getCustomMessage($account, $path)
    {
        $status = $account->active ? 'activo' : 'inactivo';

        return "[ Cuenta #ID {$account->id} => {$account->email} con estado {$status} falló al consultar {$path} ]";
    }

    public function logActivity($account, $path = '', $method)
    {
        $message = "{$account->service->name} #ID {$account->id} - {$account->email} => {$method} => {$path}";

        \Log::channel($account->service->code . '-activity-log')->info($message);
    }

    protected function getIdsCoHostCareers()
    {
        return Carrera::whereIn('nombre',
            ["Monitor de Ventas"]
        )->pluck('id')->toArray();
    }
}
