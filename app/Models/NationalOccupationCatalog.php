<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NationalOccupationCatalog extends Model
{
    use HasFactory;
    protected $table = 'mx_national_occupations_catalog';
    protected $fillable = ['code','name'];
    public $timestamps = false;

    public function scopeSearchByCodeOrName($query, $searchTerm)
    {
        return $query->where('code', '=', $searchTerm)
                     ->orWhere('name', '=', $searchTerm);
    }
}
