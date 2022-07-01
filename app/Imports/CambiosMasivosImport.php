<?php

namespace App\Imports;

use App\Models\CambiosMasivos;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;

class CambiosMasivosImport implements ToCollection, WithMultipleSheets
{
    use WithConditionalSheets;

	public const SHEET_TO_IMPORT = 'Insertar  - Cambio Datos';

	public $sheet;
	public function collection(Collection $rows)
	{
	}

	public function conditionalSheets(): array
	{
		if (!$this->sheet) {
			$this->sheet = new CambiosMasivos;
		}
		return [
			self::SHEET_TO_IMPORT => $this->sheet,
		];
	}
}
