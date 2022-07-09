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

        // Accounts
        $this->insertAccountsData($data);

        // Vademecum
        $this->insertVademecumData($data);

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
    // Accounts
    public function insertAccountsData($data)
    {
        $temp = [];
        foreach ($data['accounts'] as $item) {

            $service_id = (!is_null($item['service_id'])) ? DB::table('taxonomies')->where('external_id', $item['service_id'])->first('id') : null;
            $plan_id = (!is_null($item['plan_id'])) ?  DB::table('taxonomies')->where('external_id', $item['plan_id'])->first('id') : null;
            $type_id = (!is_null($item['type_id'])) ?  DB::table('taxonomies')->where('external_id', $item['type_id'])->first('id') : null;

            $item['service_id'] = (!is_null($service_id)) ? $service_id->id : null;
            $item['plan_id'] = (!is_null($plan_id)) ? $plan_id->id : null;
            $item['type_id'] = (!is_null($type_id)) ? $type_id->id : null;

            array_push($temp, $item);
        }

        $this->insertChunkedData($temp, 'accounts');
    }
    // Vademecum
    public function insertVademecumData($data)
    {
        $temp = [];
        foreach ($data['vademecum'] as $item) {

            $media_id = (!is_null($item['media_id'])) ? DB::table('media')->where('external_id', $item['media_id'])->first('id') : null;
            $category_id = (!is_null($item['category_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['category_id'])->first('id') : null;
            $subcategory_id = (!is_null($item['subcategory_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['subcategory_id'])->first('id') : null;

            $item['media_id'] = (!is_null($media_id)) ? $media_id->id : null;
            $item['category_id'] = (!is_null($category_id)) ? $category_id->id : null;
            $item['subcategory_id'] = (!is_null($subcategory_id)) ? $subcategory_id->id : null;

            array_push($temp, $item);
        }
        $this->insertChunkedData($temp, 'vademecum');
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
