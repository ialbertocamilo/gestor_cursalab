<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryChecklist extends Model
{
    use HasFactory;
    protected $table = 'summary_checklists';
    protected $fillable = ['checklist_id','assigned','completed','advanced_percentage'];

    protected function updateData($checklist)
    {
        $row_user = self::getCurrentRowOrCreate($checklist);
        if (!$row_user) return true;
        $checklist_id = $checklist->id; 
        $course = new Course();
        if(!$checklist->segments){
            $workspace_id = $checklist->workspace_id; 
            $checklist->segments = Checklist::getChecklistsWorkspace(checklist_id:$checklist_id, with_segments:true, select : 'id');
        }
        $users_assigned = $course->usersSegmented($checklist->segments, $type = 'users_id');
        $completed = ChecklistRpta::where('checklist_id',$checklist_id)->whereIn('student_id',$users_assigned)->where('percent',100)->count();
        $assigned = count($users_assigned);
        $advanced_percentage = self::getGeneralPercentage($assigned, $completed);
        info($assigned);
        info($completed);
        info($advanced_percentage);

        $data = compact('assigned', 'completed', 'advanced_percentage');
        $row_user->update($data);

        return $row_user;
    }

    protected function getCurrentRowOrCreate($checklist){
        $summary_checklist = self::where('checklist_id',$checklist->id)->first();
        if(!$summary_checklist){
            $summary_checklist = self::create([
                'checklist_id'=> $checklist->id,
            ]);
        }
        return $summary_checklist;
    }

    protected function getGeneralPercentage($total, $advance)
    {
        $percent = ($total > 0) ? (($advance / $total) * 100) : 0;
        $percent = round(($percent > 100) ? 100 : $percent); // maximo porcentaje = 100

        return $percent;
    } 
}
