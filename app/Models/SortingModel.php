<?php

namespace App\Models;

use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;

class SortingModel extends Model
{
    use ApiResponse;

    protected function changeItemsPosition($current, $new, $field = 'orden')
    {
        $current_order = $current->$field;

        $current->$field = $new->$field;

        $new->$field = $current_order;

        $current->save();

        $new->save();

        return true;
    }

    protected function getLastItem($item, $filters = [], $columnName = 'orden')
    {
        return SortingModel::getItemsInOrder(
            $item,
            $filters,
            'DESC',
            'ASC',
            $columnName
        );
    }

    protected function getLastItemOrderNumber($item, $filters = [], $columnName = 'orden')
    {
        $last = SortingModel::getLastItem($item, $filters, $columnName)->first();

        return $last->$columnName;
    }

    protected function getNextItemOrderNumber($item, $filters = [], $columnName = 'orden')
    {
        $last = SortingModel::getLastItem($item, $filters, $columnName)->first();

        return $last ? $last->$columnName + 1 : 1;
    }

    protected function getItemsInOrder(
        $item,
        $filters = [],
        $orden_sort = 'ASC',
        $updated_sort = 'ASC',
        $columnName = 'orden'
    ) {
        $model = is_object($item) ? get_class($item) : $item;

        $query = $model::query();

        foreach ($filters as $key => $filter) {
            $query->where($key, $filter);
        }

        $query->orderBy($columnName, $orden_sort)->orderBy('updated_at', $updated_sort);

        return $query;
    }

    protected function reorderItems(
        $item,
        $filters = [],
        $item_last_order = NULL,
        $columnName = 'orden'
    ) {
        $updated_sort = 'DESC';

        if ($item_last_order) {

            if (is_object($item)) {

                $updated_sort = $item->$columnName > $item_last_order
                    ? 'ASC'
                    : 'DESC';
            }
        } else {

            $last = SortingModel::getLastItem($item, $filters, $columnName)->first();

            if ($last->id == $item->id)
                return true;
        }

        $rows = SortingModel::getItemsInOrder(
            $item,
            $filters,
            'ASC',
            $updated_sort,
            $columnName
        )->get();

        foreach ($rows as $index => $row) {

            $position = $index + 1;

            if ($row->orden != $position) {

                $row->update([$columnName => $position]);
                // info("- {$position} => ID {$row->id} UPDATED");
            }
        }

        return true;
    }

    protected function getNextAndPreviousItem($items, $request, $query)
    {
        $query_next = clone ($query);
        $query_prev = clone ($query);

        if ($items->currentPage() > 1 and $items->lastPage() > $items->currentPage()) {
            $items->_previous = $this->getNextOrPreviousItem('previous', $items->first(), $request, $query_prev);
            $items->_next = $this->getNextOrPreviousItem('next', $items->last(), $request, $query_next);
        }

        if ($items->currentPage() == 1 and $items->lastPage() != 1) {
            $items->_previous = NULL;
            $items->_next = $this->getNextOrPreviousItem('next', $items->last(), $request, $query_next);
        }

        if ($items->lastPage() == $items->currentPage()) {
            $items->_previous = $this->getNextOrPreviousItem('previous', $items->first(), $request, $query_prev);
            $items->_next = NULL;
        }

        return $items;
    }

    protected function getNextOrPreviousItem($action, $item, $request, $query)
    {
        $operator = $action == 'next' ? '>' : '<';
        $order    = $action == 'next' ? 'asc' : 'desc';

        if ($item != null) {
            $query->where('orden', $operator, $item->orden);
            $query->orderBy('orden', $order);
        }

        return $query->first();
    }

    protected function setChangeOrder($request)
    {
        // try {
            $models_with_position_pivot = ['SchoolSubworkspace','CourseSchool'];
            $model = "App" . '\\' . "Models" . '\\' . $request->model;
            $model = app($model);

            $field = $request->field ?? 'position';
            $action = $request->action;
            if(in_array($request->model,$models_with_position_pivot)){
                $this->updatePositionInPivotTable($model,$request,$field,$action);
                return $this->success(['msg' => 'Orden actualizado correctamente.']);
            }

            $resource = $model::find($request->id);
            // if ($resource->$field === 1)
            // {
            //     if ($action == 'down')
            //         return $this->error('No es posible bajar de posición', 422);
            // }

            $new_orden = $action == 'up' ? $resource->position + 1 : $resource->position - 1;

            if ($request->model == 'Poll') {
                $next_resource = $model::where('position', $new_orden)->where('workspace_id', $resource->workspace_id)->first();
            } else if ($request->model == 'Topic') {
                $next_resource = $model::where('position', $new_orden)->where('course_id', $resource->course_id)->first();
            } else {
                $next_resource = $model::where('position', $new_orden)->first();
            }
            DB::beginTransaction();
            if ($next_resource) {
                $next_resource->position = $resource->position;
                $next_resource->save();
            }

            $resource->position = $new_orden;
            $resource->save();

            if ($request->model === 'Topic') {
                Topic::fixTopicsPosition($resource->course_id, $resource->id);
            }

            DB::commit();

        // } catch (\Exception $e) {
        //     // info($e->getMessage());
        //     DB::rollBack();

        //     return $this->error('Error al actualizar', 422);
        // }

        return $this->success(['msg' => 'Orden actualizado correctamente.']);
    }

    public function updatePositionInPivotTable($model, $request, $field, $action){
        $resource = null;
        $modelKey = '';
        $pivotField = '';
        $class_model = get_class($model);
        switch ($class_model) {
            case SchoolSubworkspace::class:
                $resource = $model::where('subworkspace_id', $request->pivot_id_selected)
                                ->where('school_id', $request->id)
                                ->first();
                $modelKey = 'subworkspace_id';
                $pivotField = 'school_id';
                break;
            case CourseSchool::class:
                $modelKey = 'school_id';
                $pivotField = 'course_id';

                self::fixNullPositionsInCourseSchols(
                    $request->pivot_id_selected
                );

                $resource = $model::where('school_id', $request->pivot_id_selected)
                                ->where('course_id', $request->id)
                                ->first();
                break;
            default:
                return;
        }
        $new_position = $action == 'up'
            ? $resource->position + 1
            : $resource->position - 1;

        DB::transaction(function () use ($model, $resource, $new_position, $modelKey, $pivotField) {
            // Actualizar el siguiente recurso
            $model::where('position', $new_position)
                ->where($modelKey, $resource->{$modelKey})
                ->update(['position' => $resource->position]);

            // Actualizar el recurso actual
            $model::where($modelKey, $resource->{$modelKey})
                ->where($pivotField, $resource->{$pivotField})
                ->update(['position' => $new_position]);
        });
        if ($class_model === SchoolSubworkspace::class) {
            cache_clear_model(School::class);
        } else if ($class_model === CourseSchool::class) {
            cache_clear_model(Course::class);
        }
    }

    /**
     * Fix null, duplicates and negative position values
     */
    public static function fixNullPositionsInCourseSchols($schoolId) {

        $coursePositions = CourseSchool::query()
            ->join('courses', 'courses.id', '=', 'course_id')
            ->where('school_id',  $schoolId)
            ->whereNull('courses.deleted_at')
            ->orderBy('position')
            ->get();

        $lastPosition = 1;
        foreach ($coursePositions as $coursePosition) {

            if ($coursePosition->position != $lastPosition) {
                CourseSchool::query()
                    ->where('school_id',  $schoolId)
                    ->where('course_id', $coursePosition->course_id)
                    ->update([
                        'position' => $lastPosition
                    ]);
            }

            $lastPosition++;
        }
    }

    public static function setLastPositionInPivotTable($pivotModel,$model,$data,$fields_to_search){
        //example data structure ['subworkspace_id'=>$subworkspace->id,'school_id' => $school->id]
        $last_postion =  $pivotModel::where(function($q) use ($fields_to_search){
                            foreach ($fields_to_search as $key => $value) {
                                $q->where($key,$value);
                            }
                        })->orderBy('position','desc')
                        ->first()?->position;
        //insert
        $insertModel = $data;
        $insertModel['position'] = $last_postion+1 ?? 1;
        // $pivotModel::updateOrCreate($data,$insertModel);
        $findRelation = $pivotModel::where(function($q) use ($data){
            foreach ($data as $key => $value) {
                $q->where($key,$value);
            }
        })->first();
        if($findRelation){
            $pivotModel::where(function($q) use ($data){
                foreach ($data as $key => $value) {
                    $q->where($key,$value);
                }
            })->update(['position'=>$insertModel['position']]);
        }else{
            $pivotModel::insert($insertModel);
        }
        cache_clear_model($model);
    }
    public static function deletePositionInPivotTable($pivotModel,$model,$fields_to_search){
        $pivotModel::where(function($q) use ($fields_to_search){
            foreach ($fields_to_search as $key => $value) {
                $q->where($key,$value);
            }
        })->delete();
        cache_clear_model($model);
    }
    public static function reorderItemsInPivotTable($class_model,$pivot_id_selected,$position){
        //event create
        // info($class_model);
        // switch ($class_model) {
        //     case 'SchoolSubworkspace':
                // $resource = $class_model::where('subworkspace_id', $pivot_id_selected)
                //             ->where('position', '>=', $position)
                //             ->where('position', '<', function($query) use ($position){
                //                 $query->selectRaw('MIN(position)+1')
                //                     ->from('school_subworkspace')
                //                     ->where('position', '>=', $position);
                //             })
                //             ->orderby('position','desc')->first();
                $subworkspace = $class_model
                ::selectRaw('@group := IF(@prev_position + 1 = position, @group, @group + 1) AS pos_group, @prev_position := position as position')
                ->crossJoin(DB::raw('(SELECT @prev_position := NULL, @group := 0) AS vars'))
                ->where('position', '>=', $position)
                ->where('subworkspace_id', $pivot_id_selected)
                ->orderBy('position', 'ASC')
                ->get();

            $groups = $subworkspace->groupBy('pos_group')->map(function ($group) {
                return [ 'start_position' => $group->min('position'), 'end_position' => $group->max('position'),    ];
            })->values();

            $result = DB::table(DB::raw('(' . $groups->toSql() . ') as groups'))
                ->mergeBindings($groups)
                ->select('start_position', 'end_position')
                ->groupBy('pos_group')
                ->limit(1);
            dd($result);
                $modelKey = 'subworkspace_id';
                $pivotField = 'school_id';
        //         break;
        //     case CourseSchool::class:
        //         $resource = $model::where('school_id', $request->pivot_id_selected)
        //                         ->where('course_id', $request->id)
        //                         ->first();
        //         $modelKey = 'school_id';
        //         $pivotField = 'course_id';
        //         break;
        //     default:
        //         return;
        // }
    }
}
