<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriterionWorkspace extends Model
{
    use HasFactory;

    protected $table = 'criterion_workspace';


    /**
     * Load workspace's criteria for reports
     * @param $workspaceId
     * @return mixed
     */
    public static function loadWorkspaceReportCriteria($workspaceId) {

        return self::join('criteria', 'criteria.id', '=', 'criterion_workspace.criterion_id')
            ->where('available_in_reports', 1)
            ->where('workspace_id', $workspaceId)
            ->where('criteria.active', 1)
            ->select(['criteria.id', 'criteria.name'])
            ->get();
    }
}
