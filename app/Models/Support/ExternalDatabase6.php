<?php

namespace App\Models\Support;

use App\Models\Vademecum;
use App\Models\Videoteca;
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
        // $this->insertMediaData($data);

        // Taxonomies
        $this->insertTaxonomiesData($data);
        $this->updateTaxonomiesData();

        // Taxonomies Es
        $this->insertTaxonomiesEsData($data);
        $this->updateTaxonomiesEsData();

        // Videoteca
        // $this->insertVideotecaData($data);

        // Vademecum
        // $this->insertVademecumData($data);
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

    protected function insertMigrationData7($data)
    {
        // Announcements
        $this->insertAnnouncementsData($data);

        // User Actions
        // $this->insertUserActionsData($data);

        // User Actions (Supervisores)
        // $this->insertUserActionsSupervisoresData($data);

        // User Actions (Entrenadores)
        // $this->insertUserActionsEntrenadoresData($data);

        
        // Require the Users Table
        // Push Notifications
        // $this->insertPushNotificationsData($data);

        // Glossaries
        // $this->insertGlossariesData($data);



        // Tickets (Soporte)
        // $this->insertTicketsData($data);

        // Preguntas Frecuentes
        // $this->insertFaqData($data);


        // Ayuda App
        // $this->insertAyudaAppData($data);
    }

    // Push Notifications
    public function insertPushNotificationsData($data)
    {
        $temp = [];
        foreach ($data['push_notifications'] as $item) {

            $creador_id = (!is_null($item['creador_id'])) ?  DB::table('users')->where('external_id', $item['creador_id'])->first('id') : null;

            $item['creador_id'] = (!is_null($creador_id)) ? $creador_id->id : null;

            array_push($temp, $item);
        }

        $this->insertChunkedData($temp, 'push_notifications');
    }

    // Glossaries
    public function insertGlossariesData($data)
    {
        $temp = [];
        foreach ($data['glossaries'] as $item) {

            $categoria_id = (!is_null($item['categoria_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['categoria_id'])->first('id') : null;

            $laboratorio_id = (!is_null($item['laboratorio_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['laboratorio_id'])->first('id') : null;

            $condicion_de_venta_id = (!is_null($item['condicion_de_venta_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['condicion_de_venta_id'])->first('id') : null;

            $via_de_administracion_id = (!is_null($item['via_de_administracion_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['via_de_administracion_id'])->first('id') : null;

            $jerarquia_id = (!is_null($item['jerarquia_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['jerarquia_id'])->first('id') : null;

            $grupo_farmacologico_id = (!is_null($item['grupo_farmacologico_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['grupo_farmacologico_id'])->first('id') : null;

            $forma_farmaceutica_id = (!is_null($item['forma_farmaceutica_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['forma_farmaceutica_id'])->first('id') : null;

            $dosis_adulto_id = (!is_null($item['dosis_adulto_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['dosis_adulto_id'])->first('id') : null;

            $dosis_nino_id = (!is_null($item['dosis_nino_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['dosis_nino_id'])->first('id') : null;

            $recomendacion_de_administracion_id = (!is_null($item['recomendacion_de_administracion_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['recomendacion_de_administracion_id'])->first('id') : null;

            $advertencias_id = (!is_null($item['advertencias_id'])) ?  DB::table('taxonomies')->where('external_id_es', $item['advertencias_id'])->first('id') : null;

            $item['categoria_id'] = (!is_null($categoria_id)) ? $categoria_id->id : null;

            $item['laboratorio_id'] = (!is_null($laboratorio_id)) ? $laboratorio_id->id : null;

            $item['condicion_de_venta_id'] = (!is_null($condicion_de_venta_id)) ? $condicion_de_venta_id->id : null;

            $item['via_de_administracion_id'] = (!is_null($via_de_administracion_id)) ? $via_de_administracion_id->id : null;

            $item['jerarquia_id'] = (!is_null($jerarquia_id)) ? $jerarquia_id->id : null;

            $item['grupo_farmacologico_id'] = (!is_null($grupo_farmacologico_id)) ? $grupo_farmacologico_id->id : null;

            $item['forma_farmaceutica_id'] = (!is_null($forma_farmaceutica_id)) ? $forma_farmaceutica_id->id : null;

            $item['dosis_adulto_id'] = (!is_null($dosis_adulto_id)) ? $dosis_adulto_id->id : null;

            $item['dosis_nino_id'] = (!is_null($dosis_nino_id)) ? $dosis_nino_id->id : null;

            $item['recomendacion_de_administracion_id'] = (!is_null($recomendacion_de_administracion_id)) ? $recomendacion_de_administracion_id->id : null;

            $item['advertencias_id'] = (!is_null($advertencias_id)) ? $advertencias_id->id : null;

            array_push($temp, $item);
        }

        $this->insertChunkedData($temp, 'glossaries');
    }

    // Announcements
    public function insertAnnouncementsData($data)
    {
        // $temp = [];
        // foreach ($data['announcements'] as $item) {

        //     $config_id = json_decode($item['config_id']);
        //     $id_criteria = DB::table('criteria')->where('code', 'module')->first('id');
        //     $id_criteria = (!is_null($id_criteria)) ? $id_criteria->id : null;

        //     $temp_an = array();
        //     if (is_array($config_id) && count($config_id) > 0) {
        //         foreach ($config_id as $config) {

        //             $id = DB::table('criterion_values')
        //                 ->where('criterion_id', $id_criteria)
        //                 ->where('external_id', $config)
        //                 ->first('id');
        //             $id = (!is_null($id)) ? $id->id : null;

        //             array_push($temp_an, $id);
        //         }
        //     }
        //     $item['config_id'] = json_encode($temp_an);

        //     array_push($temp, $item);
        // }

        // $this->insertChunkedData($temp, 'announcements');
    }

    // Tickets
    public function insertTicketsData($data)
    {
        $temp = [];
        foreach ($data['tickets'] as $item) {

            $user_id = (!is_null($item['user_id'])) ?  DB::table('users')->where('external_id', $item['user_id'])->first('id') : null;

            $item['user_id'] = (!is_null($user_id)) ? $user_id->id : null;

            array_push($temp, $item);
        }

        $this->insertChunkedData($temp, 'tickets');
    }

    // Faq
    public function insertFaqData($data)
    {
        $taxonomy_id = DB::table('taxonomies')->where('type', 'section')->where('code', 'faq')->first('id');
        $taxonomy_id = (!is_null($taxonomy_id)) ? $taxonomy_id->id : null;

        $temp = [];
        foreach ($data['faq'] as $item) {
            $item['section_id'] = $taxonomy_id;
            array_push($temp, $item);
        }

        $this->insertChunkedData($temp, 'posts');
    }

    // Ayuda App
    public function insertAyudaAppData($data)
    {
        $taxonomy_id = DB::table('taxonomies')->where('type', 'section')->where('code', 'ayuda_app')->first('id');
        $taxonomy_id = (!is_null($taxonomy_id)) ? $taxonomy_id->id : null;

        $temp = [];
        foreach ($data['ayuda_app'] as $item) {
            $item['section_id'] = $taxonomy_id;
            array_push($temp, $item);
        }

        $this->insertChunkedData($temp, 'posts');
    }

    // User Actions
    public function insertUserActionsData($data)
    {
        $temp = [];
        foreach ($data['user_actions'] as $item) {

            $user_id = (!is_null($item['user_id']))
                ? DB::table('users')
                ->where('external_id', $item['user_id'])
                ->first('id')
                : null;

            $type_id = (!is_null($item['type_id']))
                ? DB::table('taxonomies')
                ->where('external_id_es', $item['type_id'])
                ->first('id')
                : null;

            $item['user_id'] = (!is_null($user_id)) ? $user_id->id : null;
            $item['type_id'] = (!is_null($type_id)) ? $type_id->id : null;

            if ($item['model_type'] == 'App\Vademecum') {

                $item['model_type'] = Vademecum::class;
                $model_id = (!is_null($item['model_id']))
                    ? DB::table('vademecum')
                    ->where('external_id', $item['model_id'])
                    ->first('id')
                    : null;
                $item['model_id'] = (!is_null($model_id)) ? $model_id->id : null;
            } else if ($item['model_type'] == 'App\Videoteca') {

                $item['model_type'] = Videoteca::class;
                $model_id = (!is_null($item['model_id']))
                    ? DB::table('videoteca')
                    ->where('external_id', $item['model_id'])
                    ->first('id')
                    : null;
                $item['model_id'] = (!is_null($model_id)) ? $model_id->id : null;
            } else {
                $item['model_type'] = null;
                $item['model_id'] = null;
            }

            array_push($temp, $item);
        }
        $this->insertChunkedData($temp, 'user_actions');
    }

    // User Actions (Supervisores)
    public function insertUserActionsSupervisoresData($data)
    {
        $id_criteria = DB::table('criteria')->where('code', 'group')->first('id');
        $id_criteria = (!is_null($id_criteria)) ? $id_criteria->id : null;

        $temp = [];
        foreach ($data['supervisores'] as $item) {

            $user_id = (!is_null($item['user_id']))
                ? DB::table('users')
                ->where('external_id', $item['user_id'])
                ->first('id')
                : null;

            $item['user_id'] = (!is_null($user_id)) ? $user_id->id : null;

            $model_id = (!is_null($item['model_id']))
                ? DB::table('criterion_values')
                ->where('external_id', $item['model_id'])
                ->where('criterion_id', $id_criteria)
                ->first('id')
                : null;
            $item['model_id'] = (!is_null($model_id)) ? $model_id->id : null;

            array_push($temp, $item);
        }
        $this->insertChunkedData($temp, 'user_actions');
    }

    // User Actions (Entrenadores)
    public function insertUserActionsEntrenadoresData($data)
    {
        $temp = [];
        foreach ($data['entrenadores'] as $item) {

            $entrenador_id = (!is_null($item['user_id']))
                ?  DB::table('users')
                ->where('external_id', $item['user_id'])
                ->first('id')
                : null;

            $usuario_id = (!is_null($item['model_id']))
                ?  DB::table('users')
                ->where('external_id', $item['model_id'])
                ->first('id')
                : null;

            $item['model_id'] = (!is_null($usuario_id)) ? $usuario_id->id : null;

            $item['user_id'] = (!is_null($entrenador_id)) ? $entrenador_id->id : null;

            array_push($temp, $item);
        }
        $this->insertChunkedData($temp, 'user_actions');
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
