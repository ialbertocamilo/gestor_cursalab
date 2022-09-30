<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SegmentSearchByDocumentImport implements ToCollection
{

    public array $data = [];

    public function __construct()
    {
    }


    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $count = count($collection);

        for ($i = 1; $i < $count; $i++) {
            $this->data[] = $collection[$i][0];
        }
    }

    public function getProccesedData()
    {
        return $this->data;
    }

}
