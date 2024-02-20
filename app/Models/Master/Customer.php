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

    public function getCurrentStatus()
    {
        $customer = $this;

        if (!$customer) return 'not-found';

        // if (!$customer->auto_deactivation) return 'not-configured';

        if (!$customer->estado) return 'inactive';

        if ($customer->platform_finish_date <= now()) return 'payment-missing';

        if ($customer->platform_cutoff_date <= now()) return 'platform-closed';

        return 'active';
    }

    protected function getCurrentStatusByCode($code)
    {
        $customer = Customer::getCurrentByCode($code);

        return $customer->getCurrentStatus();
    }

    public function hasServiceAvailable()
    {
        // TEMP
        // return true;

        $status = $this->getCurrentStatus();

        if ($status == 'inactive') return false;

        return true;
    }

    protected function getCurrentSession()
    {
        // TEMP
        // return NULL;

        $customer = cache('current_customer');

        if (!$customer) {
            
            $customer_id = config('app.customer.id');
            $customer = Customer::getCurrentById($customer_id);

            cache(['current_customer' => $customer], now()->addHours(2));
        }

        return $customer;
    }

    protected function getCurrentByCode($code)
    {
        return Customer::where('slug_empresa', $code)->first();
    }

    protected function getCurrentById($id)
    {
        return Customer::find($id);
    }

    public function hasStatusCode($status_code)
    {
        $current_status = $this->getCurrentStatus();

        return $status_code == $current_status;
    }

    public function showStatusMessage($status_code)
    {
        return ($this->enable_messages && $this->hasStatusCode($status_code));
    }

    public function getDaysToCuttoff()
    {
        $days = 0;

        if ($this->platform_cutoff_date) {

            // $days = $this->platform_cutoff_date->diffInDays(now(), false);
            $days = now()->diffInDays($this->platform_cutoff_date, false);
            $days = $days > 0 ? $days : 0;
        }

        return $days;
    }
}
