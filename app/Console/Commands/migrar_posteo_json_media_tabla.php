<?php

namespace App\Console\Commands;

use App\Models\Posteo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class migrar_posteo_json_media_tabla extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrar:posteo_json_media_tabla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar la columna medias en la tabla posteos a la nueva tabla media_temas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $now = now();

        $media_migrados = DB::table('media_temas')
            ->groupBy('tema_id')
            ->pluck('tema_id');

        $q = Posteo::whereNotIn('id', $media_migrados);

        $posteos = $q->get();

        $count_posteos = $posteos->count();

        $this->info('Cantidad de posteos: ' . $count_posteos);
        $bar = $this->output->createProgressBar($count_posteos);
        $this->info("====== INICIO DE MIGRACION DE MEDIAS \n");

        $temp = [];

        foreach ($posteos as $post) {
            $medias = json_decode($post->media, true);
            if ($medias) {
                foreach ($medias as $media_key => $media) {
                    $temp[] = [
                        'tema_id' => $post->id,
                        'titulo' => $media['titulo'] ?? '',
                        'valor' => $media['valor'] ?? '',
                        'tipo' => $media['tipo'] ?? '',
                        'descarga' => $media['descarga'] ?? 0,
                        'embed' => $media['embed'] ?? 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                        'orden'=>($media_key+1)
                    ];
                }

            }
            $bar->advance();
        }
        DB::table('media_temas')->insert($temp);

        $bar->finish();

        $this->info("\n ====== FIN DE MIGRACION DE MEDIAS \n");
    }
}
