<?php

namespace App\Models;

use App\Models\CheckList;
// use App\Models\BaseModel;
class ChecklistSupervisor extends CheckList
{
    
    public function segments()
    {   
        return $this->morphMany(Segment::class, 'model');
    }
}
