<?php

namespace App\Console\Commands;

use App\Models\SegmentValue;
use Illuminate\Console\Command;

class FixSegmentValuesDateRange extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'segment-values:fix-date-range';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $segment_values = SegmentValue::with('criterion_value:id,value_text,value_date')
            ->whereHas('criterion', function ($q) {
                $q->whereRelation('field_type', 'code', 'date');
            })
            ->get();
        $bar = $this->output->createProgressBar($segment_values->count());

        foreach ($segment_values as $segment_value) {
            $date_parse = $segment_value->criterion_value?->value_text;

            $format = null;
            _validateDate($date_parse, 'Y-m-d') && $format = 'Y-m-d';
            _validateDate($date_parse, 'Y/m/d') && $format = 'Y/m/d';
            _validateDate($date_parse, 'd/m/Y') && $format = 'd/m/Y';
            _validateDate($date_parse, 'd-m-Y') && $format = 'd-m-Y';

            if ($format) {
                $date = carbonFromFormat($date_parse, $format)->format("Y-m-d");
                $segment_value->update([
                    'starts_at' => $date,
                    'finishes_at' => $date,
                ]);
            }
            $bar->advance();

        }
        $bar->finish();

    }
}
