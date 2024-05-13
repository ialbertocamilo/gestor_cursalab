<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ActivityChecklistImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public array $activities = [];

    public function collection(Collection $collection)
    {
        for ($i=2; $i < count($collection); $i++) { 
            
            $activity =  $collection[$i][0];
            if($activity){
                $code_evaluation = isset($collection[$i][1]) && ( strtolower($collection[$i][1]) == 'opción única' || strtolower($collection[$i][1]) == 'opción unica')
                                    ? 'custom_option' : 'scale_evaluation';
                $is_evaluable = isset($collection[$i][2]) && ( strtolower($collection[$i][2]) == 'sí' || strtolower($collection[$i][2]) == 'si');
                $photo_response = isset($collection[$i][3]) && ( strtolower($collection[$i][3]) == 'sí' || strtolower($collection[$i][3]) == 'si');
                $comment_activity = isset($collection[$i][4]) && ( strtolower($collection[$i][4]) == 'sí' || strtolower($collection[$i][4]) == 'si');
                $this->activities[] = [
                    'activity' => $activity,
                    'is_evaluable' => $is_evaluable,
                    'photo_response' => $photo_response,
                    'comment_activity' => $comment_activity,
                    'code_evaluation' => $code_evaluation
                ];
            }
        }
    }
}
