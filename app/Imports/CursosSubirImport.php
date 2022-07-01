<?php

namespace App\Imports;

use App\Models\TemasSubir;
use App\Models\CursosSubir;
use App\Models\EvaluacionSubir;

use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
class CursosSubirImport implements WithMultipleSheets
{
	use WithConditionalSheets;
	public $cls_curso,$cls_tema,$cls_evalucion;
    public function conditionalSheets(): array
    {
		Log::info('Inicio Verifica Hojas');
		$this->cls_curso = new CursosSubir();
		$this->cls_tema = new TemasSubir();
		$this->cls_evalucion = new EvaluacionSubir();

        return [
            'Carga de Cursos' =>$this->cls_curso ,
            'Carga de Temas' => $this->cls_tema,
            'Carga Evaluaciones' => $this->cls_evalucion,
        ];
    }
}
