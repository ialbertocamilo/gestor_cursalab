<?php

namespace App\Imports;

use App\Models\Taxonomy;
use App\Models\CheckListItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ActivityChecklistImport implements ToCollection
{
    public array $activities = [];
    public $checklist = null;

    public function collection(Collection $collection)
    {
        $isGroupedByArea = $this->checklist->isGroupedByArea();
        $typeResponseActivity = collect([
            ['code' => 'scale_evaluation', 'label' => 'escala de evaluación'],
            ['code' => 'custom_option', 'label' => 'opción única'],
            ['code' => 'write_option', 'label' => 'texto'],
        ]);
        $workspace = get_current_workspace();

        for ($i = 2; $i < count($collection); $i++) {
            if ($isGroupedByArea) {
                $this->processGroupedByArea($collection[$i], $typeResponseActivity, $workspace);
            } else {
                $this->processUngrouped($collection[$i], $typeResponseActivity);
            }
        }
    }

    private function processGroupedByArea($row, $typeResponseActivity, $workspace)
    {
        $area = trim(strtolower($row[0]));
        $tematica = trim(strtolower($row[1]));
        $activity = trim($row[2]);

        if ($area && $tematica && $activity) {
            $checklistResponse = $this->getChecklistResponse($typeResponseActivity, $row[3]);

            $areaTaxonomy = $this->getOrCreateTaxonomy('areas', $area, $workspace->id);
            $tematicaTaxonomy = $this->getOrCreateTaxonomy('tematicas', $tematica, $workspace->id, $areaTaxonomy->id);

            $extraAttributes = $this->getExtraAttributes($row, 4, 5, 6);

            $dataActivity = [
                'area_id' => $areaTaxonomy->id,
                'tematica_id' => $tematicaTaxonomy->id,
                'activity' => $activity,
                'checklist_id' => $this->checklist->id,
                'active' => 1,
                'checklist_response_id' => $checklistResponse->id,
                'extra_attributes' => json_encode($extraAttributes),
            ];
            CheckListItem::insert($dataActivity);
        }
    }

    private function processUngrouped($row, $typeResponseActivity)
    {
        $activity = trim($row[0]);

        if ($activity) {
            $checklistResponse = $this->getChecklistResponse($typeResponseActivity, $row[1]);

            $extraAttributes = $this->getExtraAttributes($row, 2, 3, 4);

            $dataActivity = [
                'activity' => $activity,
                'checklist_id' => $this->checklist->id,
                'active' => 1,
                'checklist_response_id' => $checklistResponse->id,
                'extra_attributes' => json_encode($extraAttributes),
            ];
            CheckListItem::insert($dataActivity);
        }
    }

    private function getChecklistResponse($typeResponseActivity, $labelEvaluation)
    {
        $codeEvaluation = $typeResponseActivity->firstWhere('label', strtolower($labelEvaluation))['code'];
        return Taxonomy::where('group', 'checklist')
            ->where('type', 'type_response_activity')
            ->where('code', $codeEvaluation)
            ->first();
    }

    private function getOrCreateTaxonomy($type, $name, $workspaceId, $parentId = null)
    {
        $taxonomy = Taxonomy::where('group', 'checklist')
            ->where('type', $type)
            ->where('workspace_id', $workspaceId)
            ->where('name', $name)
            ->where('parent_id', $parentId)
            ->first();

        if (!$taxonomy) {
            $taxonomy = Taxonomy::create([
                'workspace_id' => $workspaceId,
                'group' => 'checklist',
                'type' => $type,
                'name' => $name,
                'parent_id' => $parentId,
                'active' => 1,
            ]);
        }

        return $taxonomy;
    }

    private function getExtraAttributes($row, $isEvaluableIndex, $photoResponseIndex, $commentActivityIndex)
    {
        return [
            "is_evaluable" => isset($row[$isEvaluableIndex]) && (strtolower($row[$isEvaluableIndex]) == 'sí' || strtolower($row[$isEvaluableIndex]) == 'si'),
            "photo_response" => isset($row[$photoResponseIndex]) && (strtolower($row[$photoResponseIndex]) == 'sí' || strtolower($row[$photoResponseIndex]) == 'si'),
            "comment_activity" => isset($row[$commentActivityIndex]) && (strtolower($row[$commentActivityIndex]) == 'sí' || strtolower($row[$commentActivityIndex]) == 'si'),
            "computational_vision" => false,
            "type_computational_vision" => null,
            "type_computational_value" => null
        ];
    }
}
