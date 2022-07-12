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

    protected function insertMigrationData5($data)
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

        // Vademecum
        $this->insertVademecumData($data);
    }

    protected function insertMigrationData6($data)
    {

        // Accounts
        $this->insertAccountsData($data);

        // Require the Users Table
        // Meetings
        $this->insertMeetingsData($data);

        // Require the Meetings Table
        // Attendants
        $this->insertAttendantsData($data);
    }

    // Media
    public function insertMediaData($data)
    {
        $this->insertChunkedData($data['media'], 'media');
    }
    // Meetings
    public function insertMeetingsData($data)
    {
        $temp = [];
        foreach ($data['meetings'] as $item) {

            $status_id = (!is_null($item['status_id'])) ?  DB::table('taxonomies')->where('external_id', $item['status_id'])->first('id') : null;

            $type_id = (!is_null($item['type_id'])) ?  DB::table('taxonomies')->where('external_id', $item['type_id'])->first('id') : null;

            $account_id = (!is_null($item['account_id'])) ?  DB::table('accounts')->where('external_id', $item['account_id'])->first('id') : null;

            $host_id = (!is_null($item['host_id'])) ?  DB::table('users')->where('external_id', $item['host_id'])->first('id') : null;

            $user_id = (!is_null($item['user_id'])) ?  DB::table('users')->where('external_id', $item['user_id'])->first('id') : null;


            $item['status_id'] = (!is_null($status_id)) ? $status_id->id : null;
            $item['type_id'] = (!is_null($type_id)) ? $type_id->id : null;
            $item['account_id'] = (!is_null($account_id)) ? $account_id->id : null;
            $item['host_id'] = (!is_null($host_id)) ? $host_id->id : null;
            $item['user_id'] = (!is_null($user_id)) ? $user_id->id : null;

            array_push($temp, $item);
        }

        $this->insertChunkedData($temp, 'meetings');
    }
    // Attendants
    public function insertAttendantsData($data)
    {
        $temp = [];
        foreach ($data['attendants'] as $item) {

            $meeting_id = (!is_null($item['meeting_id'])) ?  DB::table('meetings')->where('external_id', $item['meeting_id'])->first('id') : null;

            $usuario_id = (!is_null($item['usuario_id'])) ?  DB::table('users')->where('external_id', $item['usuario_id'])->first('id') : null;

            $type_id = (!is_null($item['type_id'])) ?  DB::table('taxonomies')->where('external_id', $item['type_id'])->first('id') : null;

            $browser_family_id = (!is_null($item['browser_family_id'])) ?  DB::table('taxonomies')->where('external_id', $item['browser_family_id'])->first('id') : null;

            $browser_version_id = (!is_null($item['browser_version_id'])) ?  DB::table('taxonomies')->where('external_id', $item['browser_version_id'])->first('id') : null;

            $platform_family_id = (!is_null($item['platform_family_id'])) ?  DB::table('taxonomies')->where('external_id', $item['platform_family_id'])->first('id') : null;

            $platform_version_id = (!is_null($item['platform_version_id'])) ?  DB::table('taxonomies')->where('external_id', $item['platform_version_id'])->first('id') : null;

            $device_family_id = (!is_null($item['device_family_id'])) ?  DB::table('taxonomies')->where('external_id', $item['device_family_id'])->first('id') : null;

            $device_model_id = (!is_null($item['device_model_id'])) ?  DB::table('taxonomies')->where('external_id', $item['device_model_id'])->first('id') : null;


            $item['meeting_id'] = (!is_null($meeting_id)) ? $meeting_id->id : null;
            $item['usuario_id'] = (!is_null($usuario_id)) ? $usuario_id->id : null;
            $item['type_id'] = (!is_null($type_id)) ? $type_id->id : null;
            $item['browser_family_id'] = (!is_null($browser_family_id)) ? $browser_family_id->id : null;
            $item['browser_version_id'] = (!is_null($browser_version_id)) ? $browser_version_id->id : null;
            $item['platform_family_id'] = (!is_null($platform_family_id)) ? $platform_family_id->id : null;
            $item['platform_version_id'] = (!is_null($platform_version_id)) ? $platform_version_id->id : null;
            $item['device_family_id'] = (!is_null($device_family_id)) ? $device_family_id->id : null;
            $item['device_model_id'] = (!is_null($device_model_id)) ? $device_model_id->id : null;

            array_push($temp, $item);
        }

        $this->insertChunkedData($temp, 'attendants');
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
