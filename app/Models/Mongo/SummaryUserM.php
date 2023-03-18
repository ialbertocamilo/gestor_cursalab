<?php

namespace App\Models\Mongo;

use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

class SummaryUserM extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'summary_users';

    public static function store(){
        $now_date = now()->format('Y-m-d');
        for ($i=0; $i < 31; $i++) { 
            info($i);
            DB::table('summary_users')->select('id','last_time_evaluated_at', 'courses_assigned', 'user_id', 'attempts', 'score',
            'grade_average', 'courses_completed', 'advanced_percentage')->chunkById(40000, function ($summary_users) use($now_date){
                foreach ($summary_users as $summary) {
                    $summary->generated_date = $now_date; 
                    self::insert((array) $summary);
                }
            });
        }
    }
}
?>