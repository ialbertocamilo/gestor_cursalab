<?php

declare(strict_types = 1);

namespace App\Charts;

use Chartisan\PHP\Chartisan;
use ConsoleTVs\Charts\BaseChart;
use Illuminate\Http\Request;

class LineChart extends BaseChart
{
    /**
     * Handles the HTTP request for the given chart.
     * It must always return an instance of Chartisan
     * and never a string or an array.
     */
    public function handler(Request $request): Chartisan
    {
        return Chartisan::build()
            ->labels(['Lun', 'Mar', 'Mie', 'Jue', 'Vie'])
            ->dataset('Sample A', [4, 2, 3, 8, 2])
            ->dataset('Sample B', [5, 6, 2, 3, 7])
            ->dataset('Sample C', [3, 2, 7, 4, 1]);
    }
}