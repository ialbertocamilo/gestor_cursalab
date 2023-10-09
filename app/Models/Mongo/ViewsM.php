<?php

namespace App\Models\Mongo;

use Carbon\Carbon;
use App\Models\Topic;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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
            self::saveViewsMysql($date);
        }
    }
    protected function saveEngagement(){
        $ledgers = collect(json_decode(Storage::disk('public')->get('json/results_views_by_topic.json',true)));
        $topics_id = $ledgers->pluck('topic_id')->unique();
        $insertData = [];
        foreach ($ledgers as $key => $ledger) {
            $topic = Db::table('topics')->where('id',(int) $ledger->topic_id)->select('id','course_id','name')->first();
            $course = Db::table('courses')->where('id',(int) $topic->course_id)->select('id','name')->first();
            $workspace = Db::table('course_workspace')->join('workspaces','workspaces.id','course_workspace.workspace_id')
                            ->where('course_workspace.course_id',$course->id)->select('workspaces.id','workspaces.name')->first();
            $schools = Db::table('course_school')->join('schools','schools.id','course_school.school_id')
                            ->where('course_school.course_id',$course->id)->select('schools.id','schools.name')->get()->map(function($school){
                                return  [
                                    'school_id' => $school->id,
                                    'shool_name' => $school->name
                                ];
                            })->toArray();
            if($topic){
                $users_quantity = Db::table('users_quantity')->where('workspace_id',$workspace->id)->where('date',$ledger->created_at)->whereNull('subworkspace_id')->first();
                $insertData[]=[
                    'course_id'=> $topic->course_id,
                    'course_name'=> $course->name,
                    'schools' =>   $schools,
                    'topic_id' => $topic->id,
                    'topic_name' => $topic->name,   
                    'workspace_id' => $workspace->id,
                    'workspace_name' => $workspace->name,
                    'views_by_topic' => $ledger->count_user,
                    'views_unique_by_topic'=> $ledger->count_unique_user,
                    'generated_at'=> $ledger->created_at
                ];
            }else{
                info('No encontrado: '.$ledger->topic_id);
            }
        }
        info($insertData);
        dd($insertData[0]);
    }
    public static function saveViews($date){
        //Cambiar consulta a mongo
        // SELECT modified from ledgers where recordable_type = 'App\\Models\\SummaryTopic' and date(created_at) = '2023-02-24' and FIND_IN_SET('"views"', modified) > 0
        // SELECT count(properties->'$.topic_id'),properties->'$.topic_id' from ledgers where recordable_type = 'App\\Models\\SummaryTopic' and date(created_at) = '2023-02-23' and modified like '%"views"%' group by properties->'$.topic_id'
        info('Inicia consulta ledgers');
        $ledgers = DB::table('ledgers')->select( DB::raw("count(properties->'$.topic_id') as 'cantidad', properties->'$.topic_id' as 'topic_id', GROUP_CONCAT(id) as 'ids_ledgers'"))
                        ->where('recordable_type','App\\Models\\SummaryTopic')
                        ->whereDate('created_at',$date)
                        ->where('modified','like','%"views"%')
                        ->groupBy(DB::raw("properties->'$.topic_id'"))
                        ->get();
        // $ledgers = LedgerM::countByTopic($date);
        info('Fin consulta ledgers');
        $insertData = [];
        info('Inicia consulta topics');
        $topics = Topic::select('id','name','course_id')->whereHas('course')->with('course:id,name','course.schools','course.workspaces')->whereIn('id',$ledgers->pluck('_id'))->get();
        info('Fin consulta topics');
        foreach ($ledgers as $ledger) {
            dd($ledger);
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
                    'generated_at'=> $date
                ];
            }
        }
        $insert_chunks = array_chunk($insertData,500);
        foreach ($insert_chunks as $insertDocument) {
            self::insert((array)$insertDocument);
        }
    }
    //Versión 2
    public static function saveViewsMysql($date){
        info('Inicia consulta ledgers');
        $init_date = $date.' 00:00:00';
        $final_date = $date.' 23:59:59';
        // $ledgers = DB::table('ledgers')->select( DB::raw("user_id,properties->'$.topic_id' as 'topic_id',created_at"))
        //                 ->where('recordable_type','App\\Models\\SummaryTopic')
        //                 ->where('modified','like','%views%')
        //                 ->where('created_at','>',$init_date)
        //                 ->where('created_at','<',$final_date)
        //                 ->get();
        // $query = "SELECT user_id, properties->'$.topic_id' as topic_id, created_at, url 
        //   FROM ledgers 
        //   WHERE recordable_type = ? 
        //   AND modified LIKE ? 
        //   AND created_at > ? 
        //   AND created_at < ?";
        // $parameters = ['App\\Models\\SummaryTopic', '%views%', '2023-03-02 00:00:00', '2023-03-02 23:59:59'];<- Demoras demasiado v:<<<

        $ledgers = DB::select($query, $parameters);
        dd($ledgers);
        // $ledgers = LedgerM::countByTopic($date);
        info('Fin consulta ledgers');
        $insertData = [];
        info('Inicia consulta topics');
        $topics = Topic::select('id','name','course_id')->whereHas('course')->with('course:id,name','course.schools','course.workspaces')->whereIn('id',$ledgers->pluck('topic_id'))->get();
        info('Fin consulta topics');
        foreach ($ledgers as $ledger) {
            dd($ledger);
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
                    'generated_at'=> $date
                ];
            }
        }
        $insert_chunks = array_chunk($insertData,500);
        foreach ($insert_chunks as $insertDocument) {
            self::insert((array)$insertDocument);
        }
    }
    public static function saveFromJson(){
        $ledgers = collect(json_decode(Storage::disk('public')->get('json/views_topic_by_user.json',true)));
        dd($ledgers[0]);
    }
}
