<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryUserChecklist extends Summary
{
    use HasFactory;

    protected $table = 'summary_user_checklist';
    protected $fillable = ['user_id','assigned','completed','advanced_percentage'];

    protected function updateUserData($user = null)
    {
        $user = $user ?? auth()->user();
        $row_user = self::getCurrentRowOrCreate(null, $user);
        if (!$row_user) return true;
        $checklist_user = self::getChecklistByUser($user,'id');
        $checklist_assigned = array_unique(
                array_merge(
                    $checklist_user['checklist_libre_assigned']->pluck('id')->toArray(),
                    $checklist_user['checklist_course_assigned']->pluck('id')->toArray()
                )
            );
        $completed = ChecklistRpta::where('student_id',$user->id)->whereIn('checklist_id',$checklist_assigned)->where('percent',100)->count();
        $assigned = count($checklist_assigned);
        $advanced_percentage = self::getGeneralPercentage($assigned, $completed);
        $data = compact('assigned', 'completed', 'advanced_percentage');

        $row_user->update($data);
        return $row_user;
    }
    public static function getChecklistByUser(User $user,$select_checklist,$withChecklistFreeRelations=[],$withChecklistCourseRelations,$mergeChecklist=false){
        $checklist_libre_assigned = $user->getSegmentedByModelType(CheckList::class,withModelRelations:$withChecklistFreeRelations);
        
        $cursos_x_user = $user->getCurrentCourses();
        $cursos_ids = $cursos_x_user->pluck('id')->toArray();
        $defaultChecklistCourseRelation = [
            'courses'=>function($q) use ($cursos_ids){
                $q->select('id,name')->whereIn('id',$cursos_ids);
            },
            'courses.schools:id,name'
        ];
        $checklist_course_assigned = Checklist::whereHas('courses',function($q) use ($cursos_ids){
            $q->whereIn('id',$cursos_ids);
        })->when(count($withChecklistCourseRelations)>0 , function($q) use ($withChecklistCourseRelations){
            $q->with(array_merge($withChecklistCourseRelations,$defaultChecklistCourseRelation));
        })->where('active',1)->select($select_checklist)->get();
        if($mergeChecklist){
            return $checklist_course_assigned->merge($checklist_libre_assigned);
        }
        return compact('checklist_libre_assigned','checklist_course_assigned');
    }
    protected function getGeneralPercentage($total, $advance)
    {
        $percent = ($total > 0) ? (($advance / $total) * 100) : 0;
        $percent = round(($percent > 100) ? 100 : $percent); // maximo porcentaje = 100

        return $percent;
    } 
}
