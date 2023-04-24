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
                $resource = $model::where('school_id', $request->pivot_id_selected)
                                ->where('course_id', $request->id)
                                ->first();
                $modelKey = 'school_id';
                $pivotField = 'course_id';
                break;
            default:
                return;
        }
        $new_position = $action == 'up' ? $resource->position + 1 : $resource->position - 1;
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

    public static function reorderItemsInPivotTable($class_model,$pivot_id_selected,$position){
        //event create
        // info($class_model);
        switch ($class_model) {
            case 'SchoolSubworkspace':
                $resource = $class_model::where('subworkspace_id', $pivot_id_selected)
                            ->where('position', '>=', $position)
                            ->where('position', '<', function($query) use ($position){
                                $query->selectRaw('MIN(position)+1')
                                    ->from('school_subworkspace')
                                    ->where('position', '>=', $position);
                            })
                            ->orderby('position','desc')->first();
                dd($pivot_id_selected,$position,$resource);
                $modelKey = 'subworkspace_id';
                $pivotField = 'school_id';
                break;
            case CourseSchool::class:
                $resource = $model::where('school_id', $request->pivot_id_selected)
                                ->where('course_id', $request->id)
                                ->first();
                $modelKey = 'school_id';
                $pivotField = 'course_id';
                break;
            default:
                return;
        }
    }
}
