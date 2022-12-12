<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class getUsuariosFromExcelImport implements ToCollection
{
    /**
     * @param Collection $collection
     */
    public $data = [];

    public function collection(Collection $excelData)
    {
        $usuarios_ids = collect();
        $filtered = $excelData->reject(function ($value, $key) {
            return $key == 0;
        });
        $filtered = $filtered->each(function ($value, $key) use ($usuarios_ids) {
            if (trim($value[0]))
                $usuarios_ids->push(trim($value[0]));
        });

        $workspace = get_current_workspace();

        $usuarios = User::whereIn('document', $usuarios_ids->all())
            ->select('id', 'document', 'name', 'lastname', 'surname')
            ->whereRelation('subworkspace', 'parent_id', $workspace->id)
            ->onlyClientUsers()
            ->get();

        $diff = $usuarios_ids->diff($usuarios->pluck('document')->all());

        $this->data = [
            'ok' => $usuarios->pluck('fullname', 'document')->all(),
            'error' => $diff->all()
        ];
    }

    public function get_data()
    {
        return $this->data;
    }
}
