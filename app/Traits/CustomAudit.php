<?php

namespace App\Traits;

trait CustomAudit
{
    use \Altek\Accountant\Recordable;
    use \Altek\Eventually\Eventually;

    // public $defaultRelationships = [];
    
    public function loadDefaultRelationships()
    {
        $relationships = array_values($this->defaultRelationships);

        return $this->load($relationships);
    }
}

