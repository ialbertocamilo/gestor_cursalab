<?php

namespace App\Traits;

use Carbon\Carbon;

trait UnixTimestampSerializable
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        // return $date->getTimestamp();
        return Carbon::instance($date)->toIso8601String();
    }
}
