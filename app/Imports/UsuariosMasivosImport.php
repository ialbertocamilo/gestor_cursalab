<?php

namespace App\Imports;

use App\Models\UsuariosMasivos;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithConditionalSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class UsuariosMasivosImport implements ToCollection, WithMultipleSheets
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
			$this->sheet = new UsuariosMasivos;
		}
		return [
			self::SHEET_TO_IMPORT => $this->sheet,
		];
	}
}
