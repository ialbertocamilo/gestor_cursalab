<?php

namespace App\Imports\Meeting;

use App\Models\Attendant;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class AttendantsFormImport implements ToCollection
{
    /**
     * @param Collection $collection
     */

    public $attendants = [];
    public $data = [];
    public $msg = 'Invitados agregados exitosamente';

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection(Collection $excelData)
    {
        $filteredExcelData = collect();

        $excelData = $excelData->reject(function ($value, $key) {
            return $key === 0;
        });
        $excelData->each(function ($value, $key) use ($filteredExcelData) {
            $filteredExcelData->push($value[0]);
        });

        $this->data['usuarios_dni'] = $filteredExcelData->all();
    }

    public function get_data()
    {
        return $this->data['usuarios_dni'];
    }
}
