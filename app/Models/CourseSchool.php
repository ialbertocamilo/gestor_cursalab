<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseSchool extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'course_school';
    protected $fillable = [
    	'school_id',
        'course_id',
        'position',
    ];
}
