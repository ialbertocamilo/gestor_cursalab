<?php

namespace App\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Builder;

class Internship extends BaseModel
{
    protected $table = 'internships';

    protected $fillable = [
        'title',
        'leaders',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('active', 1);
    }

    protected function getPasantiasAsignadas($user_id = null)
    {
        $internships = Internship::where('active', true)->get();
        if($internships)
        {
            foreach($internships as $internship)
            {
                $leaders = $internship->leaders ? json_decode($internship->leaders) : null;
                if(is_array($leaders))
                {
                    foreach ($leaders as $leader)
                    {
                        if($user_id == $leader) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
}
