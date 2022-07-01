<?php

namespace App\Imports;

use App\Models\UsuariosFarmaHistorial;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;

class UsuariosFarmaHistorialImport implements ToCollection,WithMultipleSheets
{
    use WithConditionalSheets;

	public const SHEET_TO_IMPORT = 'Insertar  - Nuevos';

	public $sheet;
	public function collection(Collection $rows)
	{
	}

	public function conditionalSheets(): array
	{
		if (!$this->sheet) {
			$this->sheet = new UsuariosFarmaHistorial;
		}
		return [
			self::SHEET_TO_IMPORT => $this->sheet,
		];
    }
}
