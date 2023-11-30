<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NationalOccupationCatalog extends Model
{
    use HasFactory;
    protected $table = 'mx_national_occupations_catalog';
    protected $fillable = ['workspace_id','course_id','indications','active'];
    public $timestamps = false;

}
