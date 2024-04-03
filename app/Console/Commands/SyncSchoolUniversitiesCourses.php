<?php

namespace App\Console\Commands;

use Aws\S3\S3Client;
use App\Models\Topic;
use App\Models\Course;
use App\Models\School;
use App\Models\Taxonomy;
use App\Models\Workspace;
use App\Models\Master\TopicM;
use App\Models\Master\CourseM;
use Illuminate\Console\Command;
use App\Models\Master\QuestionM;
use Illuminate\Support\Facades\Storage;

class SyncSchoolUniversitiesCourses extends Command
{

    protected $signature = 'sync:school-university-courses {workspace_id?}';

    protected $description = 'Sync courses from master manager';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->syncCourses();
    }

    private function syncCourses(){
        $workspace_id = $this->argument('workspace_id');

        $cursalab_university_name = 'Cursalab University - T 🎓';
        $courses_to_migrate = CourseM::with(['type:id,code','modality:id,code'])->orderBy('position','ASC')->where('active',1)->get();
        $workspaces = Workspace::select('id','name','qualification_type_id')->when($workspace_id, function($q) use ($workspace_id){
            $q->where('id', $workspace_id);
        })->whereRelation('functionalities','code','cursalab-university')->get();
        // dd($workspaces->pluck('id','name'));
        $filter_questions_by_date = false;
        foreach ($workspaces as $workspace) {
            $subworkspaces_id = Workspace::where('parent_id',$workspace->id)->select('id')->get()->pluck('id');
            request()->workspace_id = $workspace->id;
            $school = School::whereHas('subworkspaces', function ($j) use ($subworkspaces_id) {
                $j->whereIn('subworkspace_id', $subworkspaces_id);
            })->with('courses')->where('code','cursalab-university')->first();
            $filter_by_date = false;
            if(!$school){
                $school = School::storeRequest(
                    [ 
                        "name" => $cursalab_university_name,
                        'code'=> 'cursalab-university',
                        "modalidad" => null,
                        "position" => null,
                        "active" => true,
                        "subworkspaces" =>$subworkspaces_id->toArray(),
                        "scheduled_restarts" => '{"activado":false,"tiempo_en_minutos":null,"reinicio_dias":null,"reinicio_horas":null,"reinicio_minutos":1}',
                        "imagen" => null
                    ]
                );
                $school->load('courses');
                $filter_by_date = true; 
                $filter_questions_by_date = true;
            }
            $courses_id_to_create = $courses_to_migrate->pluck('id')->diff($school->courses->pluck('external_id'));
            $courses_to_create = $courses_to_migrate->whereIn('id',$courses_id_to_create)->all();
            $courses_to_update = $courses_to_migrate->whereIn('id',$school->courses->pluck('external_id'))->all();
            $bar = $this->output->createProgressBar(count($courses_to_update));

            foreach ($courses_to_update as $key => $course) {
                $_course_to_update = $school->courses->where('external_id',$course->id)->first();
                $_course = $this->duplicateCourse($course,$workspace,$school,$_course_to_update);

                $topics_to_migrate = TopicM::getTopicsToMigrate($course);
                $topics_duplicated = Topic::where('course_id',$_course_to_update->id)->orderBy('position','ASC')->get();

                $topics_id_to_create = $topics_to_migrate->pluck('id')->diff($topics_duplicated->pluck('external_id'));
                $topics_to_create = $topics_to_migrate->whereIn('id',$topics_id_to_create)->all();
                $topics_to_update = $topics_to_migrate->whereIn('id',$topics_duplicated->pluck('external_id'))->all();
                foreach ($topics_to_create as $topic) {
                    $this->duplicateTopic($topic,$_course_to_update,$workspace);
                }
                foreach ($topics_to_update as $topic) {
                    $_topic_to_update = $topics_duplicated->where('external_id',$topic->id)->first();
                    $this->duplicateTopic($topic,$_course_to_update,$workspace,$_topic_to_update);
                }
                $bar->advance();
            }
            $bar->finish();

            $bar = $this->output->createProgressBar(count($courses_to_create));
            foreach ($courses_to_create as $course) {
                $topics =  TopicM::getTopicsToMigrate($course);
                $_course = $this->duplicateCourse($course,$workspace,$school);
                foreach ($topics as $topic) {
                    $this->duplicateTopic($topic,$_course,$workspace);
                }
                $bar->advance();
            }
            $bar->finish();
        }
        //MIGRATE QUESTIONS
        info('start migrate questions');
        QuestionM::migrateQuestions($filter_questions_by_date);
        info('finish migrate questions');
        return;
    }
    function duplicateCourse($course,$workspace,$school,$_course_to_update=null){
        if($course->imagen || $course->imagen != $_course_to_update?->imagen){
            $this->copyFileBetweenBuckets($course->imagen);
        }
        if($course->imagen && !Storage::disk('s3')->exists($course->imagen)){
            $this->copyFileBetweenBuckets($course->imagen);
        }
        $type_id =  Taxonomy::getFirstData('course', 'type', $course->type->code)->id;
        $modality_id = Taxonomy::getFirstData('course', 'modality', $course->modality->code)->id;

        $data = [
            'external_id' => $course->id,
            'name'=>$course->name,
            'external_code'=>$course->code,
            'description'=>$course->description,
            'imagen' => $course->imagen,
            'position' => $course->position,
            'reinicios_programado' => json_decode($course->scheduled_restarts),
            'mod_evaluaciones' => json_decode($course->mod_evaluaciones),
            'active' => $course->active,
            'type_id'=> $type_id,
            'modality_id' => $modality_id,
            'qualification_type_id' => $workspace->qualification_type_id,
            'lista_escuelas'=>[$school->id],
            'escuelas'=>[$school->id],
            'requisito_id'=>null
        ];
        $_course = Course::storeRequest($data,$_course_to_update);
        return $_course;
    }
    function duplicateTopic($topic,$_course,$workspace,$_topic_to_update=null){
        if($topic->imagen || $topic->imagen != $_topic_to_update?->imagen){
            $this->copyFileBetweenBuckets($topic->imagen);
        }
        $type_evaluation_id = null;
        if($topic->evaluation_type){
            $type_evaluation_id =Taxonomy::getFirstData('topic', 'evaluation-type',$topic->evaluation_type->code)->id;
        }
        $topic_requirement_id = null;
        if($topic->topic_requirement_id){
            $topic_requirement_id = Topic::where('external_id',$topic->topic_requirement_id)->select('id')->first()?->id;
        }
        $medias = [];
        $_medias = $topic->medias->sortBy('position')->toArray();
        foreach ($_medias as $media) {
            if(in_array($media['type_id'],['pdf','audio','office','image','video']) && !Storage::disk('s3')->exists($media['value'])){
                $this->copyFileBetweenBuckets($media['value']);
            }
            $medias[] = [
                "valor" => $media['value'],
                "titulo" => $media['title'],
                "tipo" => $media['type_id'],
                "embed" => $media['embed'],
                "descarga" => $media['downloadable'],
                "ia_convert" => 0,
            ];
        }
        $topic_data = [
            'course_id'=>$_course->id,
            'external_id'=>$topic->id,
            'imagen'=>$topic->imagen,
            'name'=>$topic->name,
            'code'=>'cursalab-university',
            'description'=>$topic->description,
            'position'=> $topic->position,
            'assessable'=> $topic->assessable,
            'review_all_duration_media'=> $topic->review_all_duration_media,
            'qualification_type_id'=>$workspace->qualification_type_id,
            'evaluation_verified'=>$topic->evaluation_verified,
            'active'=>$topic->active,
            'active_results'=>$topic->active_results,
            'type_evaluation_id'=>$type_evaluation_id,
            'topic_requirement_id'=>$topic_requirement_id,
            'medias'=> $medias
        ];
        $_topic=Topic::storeRequest($topic_data,$_topic_to_update);
    }
    function copyFileBetweenBuckets(string $sourceKey){
        $bucket_master_manager = 'statics-zona1';
        $folder_master_manager = 'master-cursalab';
        $config = config('filesystems.disks.s3');

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => $config['region'],
            'credentials' => [
                'key' => $config['key'],
                'secret' => $config['secret'],
            ],
            'endpoint'    => 'https://sfo2.digitaloceanspaces.com',
            'options' => [
                'CacheControl' => 'max-age=25920000, no-transform, public',
            ]
        ]);

        $bucket = $bucket_master_manager;
        $from_sourceKey = $folder_master_manager . '/' . $sourceKey;

        $result = $s3Client->getObject([
            'Bucket' => $bucket,
            'Key' =>  $from_sourceKey,
        ]);
        $content = $result['Body']->getContents();
        Storage::disk('s3')->put($sourceKey, $content, 'public');
        return true;   
    }
}
