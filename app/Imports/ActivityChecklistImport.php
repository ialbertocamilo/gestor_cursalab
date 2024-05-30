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
    public $checklist = null;

    public function collection( Collection $collection )
 {
        $isGroupedByArea = $this->checklist->isGroupedByArea();
        for ( $i = 2; $i < count( $collection );$i++ ) {
            $activity =  $collection[ $i ][ 0 ];
            if ( $activity ) {
                $code_evaluation = isset( $collection[ $i ][ 1 ] ) && ( strtolower( $collection[ $i ][ 1 ] ) == 'opción única' || strtolower( $collection[ $i ][ 1 ] ) == 'opción unica' )
                ? 'custom_option' : 'scale_evaluation';
                $is_evaluable = isset( $collection[ $i ][ 2 ] ) && ( strtolower( $collection[ $i ][ 2 ] ) == 'sí' || strtolower( $collection[ $i ][ 2 ] ) == 'si' );
                $photo_response = isset( $collection[ $i ][ 3 ] ) && ( strtolower( $collection[ $i ][ 3 ] ) == 'sí' || strtolower( $collection[ $i ][ 3 ] ) == 'si' );
                $comment_activity = isset( $collection[ $i ][ 4 ] ) && ( strtolower( $collection[ $i ][ 4 ] ) == 'sí' || strtolower( $collection[ $i ][ 4 ] ) == 'si' );
                $data_activity = [
                    'activity' => $activity,
                    'active' => 1,
                    'checklist_id' => $this->checklist->id,
                    'position' => $data_activity[ 'position' ],
                    'checklist_response_id'=>$data_activity[ 'checklist_response' ][ 'id' ],
                    'extra_attributes' => $data_activity[ 'extra_attributes' ],
                ];
                if ($isGroupedByArea) {

                }
            }
        }
    }
}
