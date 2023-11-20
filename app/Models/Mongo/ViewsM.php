<?php

namespace App\Models\Mongo;

use Carbon\Carbon;
use App\Models\Topic;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;

class ViewsM extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'views';
    public static function generateOldData(){
        $start_date = Carbon::parse('2022-10-01');
        $end_date = Carbon::parse('2023-03-02');
        $period = CarbonPeriod::create($start_date, $end_date);
        foreach ($period->days() as $day) {
            $date = $day->format('Y-m-d');
            self::saveViews($date);
        }
    }
    public static function saveViews($date){
        //Cambiar consulta a mongo
        // SELECT modified from ledgers where recordable_type = 'App\\Models\\SummaryTopic' and date(created_at) = '2023-02-24' and FIND_IN_SET('"views"', modified) > 0
        // SELECT count(properties->'$.topic_id'),properties->'$.topic_id' from ledgers where recordable_type = 'App\\Models\\SummaryTopic' and date(created_at) = '2023-02-23' and modified like '%"views"%' group by properties->'$.topic_id'
        $ledgers = DB::table('ledgers')->select(DB::raw("count(properties->'$.topic_id') as 'cantidad',properties->'$.topic_id' as 'topic_id'"))
                        ->where('recordable_type','App\\Models\\SummaryTopic')
                        ->whereDate('created_at',$date)
                        ->where('modified','like','%"views"%')
                        ->groupBy(DB::raw("properties->'$.topic_id'"))
                        ->get();
        // $ledgers = LedgerM::countByTopic($date);
        $insertData = [];
        $topics = Topic::select('id','name','course_id')->whereHas('course')->with('course:id,name','course.schools','course.workspaces')->whereIn('id',$ledgers->pluck('_id'))->get();
        foreach ($ledgers as $ledger) {
            $topic = $topics->where('id',$ledger->_id)->first();
            if($topic){
                $schools = $topic->course->schools->map(function($school){
                    return  [
                        'school_id' => $school?->id,
                        'shool_name' => $school?->name
                    ];
                })->toArray();
                $insertData[]=[
                    'course_id'=> $topic->course_id,
                    'course_name'=> $topic->course?->name,
                    'schools' =>   $schools,
                    'topic_id' => $topic->id,
                    'topic_name' => $topic->name,
                    'workspace_id' => $topic->course->workspaces[0]?->id,
                    'workspace_name' => $topic->course->workspaces[0]?->name,
                    'views_by_topic' => $ledger->count,
                    'generated_at'=> $date,
                ];
            }
        }
        $insert_chunks = array_chunk($insertData,500);
        foreach ($insert_chunks as $insertDocument) {
            self::insert((array)$insertDocument);
        }
    }
}
