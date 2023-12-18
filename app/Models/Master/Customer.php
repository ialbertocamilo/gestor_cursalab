<?php

namespace App\Models\Master;

class Customer extends BaseModel
{
    protected $connection = 'mysql_master';

    protected $dates = [
        'platform_start_date',
        'platform_finish_date',
        'platform_cutoff_date',
    ];

    public function country()
    {
        return $this->belongsTo(Taxonomy::class, 'country_id');
    }

    public function recurrence_type()
    {
        return $this->belongsTo(Taxonomy::class, 'recurrence_type_id');
    }

    protected function getCurrentStatusByCode($code)
    {
        $customer = Customer::where('name', $code)->first();

        if (!$customer) return 'not-found';

        if (!$customer->auto_deactivation) return 'not-configured';

        if (!$customer->active) return 'inactive';

        return 'active';
    }

    protected function isAvailableByCode($code)
    {
        $status = Customer::getCurrentStatusByCode($code);

        if ($status == 'inactive') return false;

        return true;
    }
}
