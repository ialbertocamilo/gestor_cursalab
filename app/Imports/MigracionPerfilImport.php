<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class MigracionPerfilImport implements ToCollection
{
	public $data;
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        //
        // return $collection;
        $this->data = $collection;
    }
}
