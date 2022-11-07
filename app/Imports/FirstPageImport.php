<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class FirstPageImport implements WithMultipleSheets 
{
    public $model;

    public function __construct($model){
        $this->model = $model;
    }

    public function sheets(): array
    {
        return [
            0 => $this->model,
        ];
    }
}
