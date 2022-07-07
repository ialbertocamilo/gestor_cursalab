<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExternalDatabase6 extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect($db_data)
    {
        return new OTFConnection($db_data);
    }

    protected function insertMigrationData($data)
    {
        // Media
        $this->insertMediaData($data);

        // Taxonomies
        $this->insertTaxonomiesData($data);
        $this->updateTaxonomiesData();

        // Taxonomies Es
        $this->insertTaxonomiesEsData($data);
        $this->updateTaxonomiesEsData();

        // Videoteca
        $this->insertVideotecaData($data);

        // TODO: Meetings
        // $this->insertMeetingsData($data);
    }
    // Media
    public function insertMediaData($data)
    {
        $this->insertChunkedData($data['media'], 'media');
    }
    // Meetings
    public function insertMeetingsData($data)
    {
        $this->insertChunkedData($data['meetings'], 'meetings');
    }
    // Videoteca
    public function insertVideotecaData($data)
    {
        $temp = [];
        foreach ($data['videoteca'] as $item) {
            $taxonomies = DB::table('taxonomies')->where('external_id_es', $item['category_id'])->first('id');
            $media_id = (!is_null($item['media_id'])) ? DB::table('media')->where('external_id', $item['media_id'])->first('id') : null;
            $preview_id = (!is_null($item['preview_id'])) ?  DB::table('media')->where('external_id', $item['preview_id'])->first('id') : null;

            $item['category_id'] = $taxonomies->id;
            $item['media_id'] = (!is_null($media_id)) ? $media_id->id : null;
            $item['preview_id'] = (!is_null($media_id)) ? $preview_id->id : null;

            array_push($temp, $item);
        }

        $this->insertChunkedData($temp, 'videoteca');
    }
    // Taxonomies - Insert
    public function insertTaxonomiesData($data)
    {
        $this->insertChunkedData($data['taxonomies'], 'taxonomies');
    }
    // Taxonomies - Insert
    public function insertTaxonomiesEsData($data)
    {
        $this->insertChunkedData($data['taxonomies_es'], 'taxonomies');
    }
    // Taxonomies - Update
    public function updateTaxonomiesData()
    {
        $taxonomies = DB::table('taxonomies')->get();

        foreach ($taxonomies as $tax) {
            if (!is_null($tax->external_parent_id)) {

                $id = DB::table('taxonomies')
                    ->where('external_id', $tax->external_parent_id)
                    ->first('id');

                DB::table('taxonomies')
                    ->where('id', $tax->id)
                    ->update(['parent_id' => $id->id]);
            }
        }
    }
    // Taxonomies - Update
    public function updateTaxonomiesEsData()
    {
        $taxonomies = DB::table('taxonomies')->get();

        foreach ($taxonomies as $tax) {
            if (!is_null($tax->external_parent_id_es)) {

                $id = DB::table('taxonomies')
                    ->where('external_id_es', $tax->external_parent_id_es)
                    ->first('id');

                DB::table('taxonomies')
                    ->where('id', $tax->id)
                    ->update(['parent_id' => $id->id]);
            }
        }
    }


    public function insertChunkedData($data, $table_name)
    {
        foreach ($data as $chunk)
            DB::table($table_name)->insert($chunk);
    }
}
