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
        try {

            DB::beginTransaction();

            $model = "App" . '\\' . "Models" . '\\' . $request->model;
            $model = app($model);

            $field = $request->field ?? 'position';
            $action = $request->action;

            if($request->model == 'SchoolSubworkspace'){
                $resource = $model::where('subworkspace_id',$request->subworkspace_id)->where('school_id',$request->id)->first();
            }else{
                $resource = $model::find($request->id);
            }
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
            } else if ($request->model == 'SchoolSubworkspace'){
                $next_resource = $model::where('position', $new_orden)->where('subworkspace_id', $resource->subworkspace_id)->first();
            }else {
                $next_resource = $model::where('position', $new_orden)->first();
            }

            if ($next_resource) {
                $next_resource->position = $resource->position;
                $next_resource->save();
            }

            $resource->position = $new_orden;
            $resource->save();

            DB::commit();
            if ($request->model == 'SchoolSubworkspace'){
                cache_clear_model(School::class);
            }
        } catch (\Exception $e) {
            // info($e->getMessage());
            DB::rollBack();

            return $this->error('Error al actualizar', 422);
        }

        return $this->success(['msg' => 'Orden actualizado correctamente.']);
    }
}
