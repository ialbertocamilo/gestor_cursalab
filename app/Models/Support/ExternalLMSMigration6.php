<?php

namespace App\Models\Support;

use App\Models\CriterionValue;
use App\Models\Taxonomy;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

use DB;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Hash;

class ExternalLMSMigration6 extends Model
{
    const CHUNK_LENGTH = 5000;

    protected function connect()
    {
        $db_uc_data = config('database.connections.mysql_uc');

        return new OTFConnection($db_uc_data);
    }

    protected function setMigrationData5()
    {
        $db = self::connect();
        $client_LMS_data = [
            'media' => [],
            'taxonomies' => [],
            'taxonomies_es' => [],
            'videoteca' => [],
            'vademecum' => [],
        ];
        $this->setMediaData($client_LMS_data, $db);
        $this->setTaxonomiesData($client_LMS_data, $db);
        $this->setTaxonomiesEsData($client_LMS_data, $db);
        $this->setVideotecaData($client_LMS_data, $db);
        $this->setVademecumData($client_LMS_data, $db);

        return $client_LMS_data;
    }

    protected function setMigrationData6()
    {
        $db = self::connect();
        $client_LMS_data = [
            'meetings' => [],
            'accounts' => [],
            'attendants' => [],
        ];
        $this->setAccountsData($client_LMS_data, $db);
        $this->setMeetingsData($client_LMS_data, $db);
        $this->setAttendantsData($client_LMS_data, $db);

        return $client_LMS_data;
    }

    protected function setMigrationData7()
    {
        $db = self::connect();
        $client_LMS_data = [
            'push_notifications' => [],
            'glossaries' => [],
            'announcements' => [],
            'tickets' => [],
            'faq' => [],
            'user_actions' => [],
            'supervisores' => [],
            'entrenadores' => [],
            'ayuda_app' => [],
        ];
        $this->setPushNotificationsData($client_LMS_data, $db);
        $this->setGlossariesData($client_LMS_data, $db);
        $this->setAnnouncementsData($client_LMS_data, $db);
        $this->setTicketsData($client_LMS_data, $db);
        $this->setFaqData($client_LMS_data, $db);
        $this->setUserActionsData($client_LMS_data, $db);
        $this->setSupervisoresData($client_LMS_data, $db);
        $this->setEntrenadoresData($client_LMS_data, $db);
        $this->setAyudaAppData($client_LMS_data, $db);

        return $client_LMS_data;
    }

    public function setPushNotificationsData(&$result, $db)
    {
        $temp['temp_notifications'] = $db->getTable('notificaciones_push')
            ->select()
            ->get();
        foreach ($temp['temp_notifications'] as $user) {
            $result['push_notifications'][] = [
                'titulo' => $user->titulo,
                'texto' => $user->texto,
                'creador_id' => $user->creador_id,
                'destinatarios' => $user->destinatarios,
                'success' => $user->success,
                'failure' => $user->failure,
                'detalles_json' => $user->detalles_json,
                'estado_envio' => $user->estado_envio,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }
    }

    public function setGlossariesData(&$result, $db)
    {
        $temp['temp_glossaries'] = $db->getTable('glosarios')
            ->select()
            ->get();
        foreach ($temp['temp_glossaries'] as $user) {
            $result['glossaries'][] = [
                'external_id' => $user->id,
                'name' => $user->nombre,
                'active' => $user->estado,
                'categoria_id' => $user->categoria_id,
                'laboratorio_id' => $user->laboratorio_id,
                'condicion_de_venta_id' => $user->condicion_de_venta_id,
                'via_de_administracion_id' => $user->via_de_administracion_id,
                'jerarquia_id' => $user->jerarquia_id,
                'grupo_farmacologico_id' => $user->grupo_farmacologico_id,
                'forma_farmaceutica_id' => $user->forma_farmaceutica_id,
                'dosis_adulto_id' => $user->dosis_adulto_id,
                'dosis_nino_id' => $user->dosis_nino_id,
                'recomendacion_de_administracion_id' => $user->recomendacion_de_administracion_id,
                'advertencias_id' => $user->advertencias_id,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }
    }

    public function setAnnouncementsData(&$result, $db)
    {
        $temp['temp_announcements'] = $db->getTable('anuncios')
            ->select()
            ->get();
        foreach ($temp['temp_announcements'] as $user) {
            $result['announcements'][] = [
                'config_id' => $user->config_id,
                'nombre' => $user->nombre,
                'contenido' => $user->contenido,
                'imagen' => $user->imagen,
                'archivo' => $user->archivo,
                'destino' => $user->destino,
                'link' => $user->link,
                'position' => $user->orden,
                'active' => $user->estado,
                'publish_date' => $user->publish_date,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }
    }

    public function setUserActionsData(&$result, $db)
    {
        $temp['temp_user_actions'] = $db->getTable('usuario_acciones')
            ->select()
            ->get();
        foreach ($temp['temp_user_actions'] as $user) {
            $result['user_actions'][] = [
                'user_id' => $user->user_id,
                'type_id' => $user->type_id,
                'model_type' => $user->model_type,
                'model_id' => $user->model_id,
                'score' => $user->score,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }
    }

    public function setSupervisoresData(&$result, $db)
    {
        $taxonomy_id = FacadesDB::table('taxonomies')
            ->where('type', 'action')
            ->where('code', 'supervise')
            ->first('id');
        $taxonomy_id = (!is_null($taxonomy_id)) ? $taxonomy_id->id : null;

        $temp['temp_supervisores'] = $db->getTable('supervisores')
            ->select()
            ->get();

        foreach ($temp['temp_supervisores'] as $user) {
            $result['supervisores'][] = [
                'user_id' => $user->usuario_id,
                'type_id' => $taxonomy_id,
                'model_type' => CriterionValue::class,
                'model_id' => $user->criterio_id,
                'score' => 0,

                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }
    }

    public function setEntrenadoresData(&$result, $db)
    {
        $taxonomy_id = FacadesDB::table('taxonomies')
            ->where('type', 'action')
            ->where('code', 'to_train')
            ->first('id');
        $taxonomy_id = (!is_null($taxonomy_id)) ? $taxonomy_id->id : null;

        $temp['temp_entrenadores'] = $db->getTable('entrenadores_usuarios')
            ->select()
            ->get();

        foreach ($temp['temp_entrenadores'] as $user) {
            $result['entrenadores'][] = [
                'user_id' => $user->entrenador_id,
                'type_id' => $taxonomy_id,
                'model_type' => User::class,
                'model_id' => $user->usuario_id,
                'score' => 0,

                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }
    }

    public function setTicketsData(&$result, $db)
    {
        $temp['temp_tickets'] = $db->getTable('usuario_ayuda')
            ->select()
            ->get();
        foreach ($temp['temp_tickets'] as $user) {
            $result['tickets'][] = [
                'external_id' => $user->id,
                'user_id' => $user->usuario_id,
                'reason' => $user->motivo,
                'detail' => $user->detalle,
                'contact' => $user->contacto,
                'info_support' => $user->info_soporte,
                'msg_to_user' => $user->msg_to_user,
                'status' => $user->estado,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }
    }

    public function setAyudaAppData(&$result, $db)
    {
        $temp['temp_ayuda_app'] = $db->getTable('ayuda_app')
            ->select()
            ->get();
        foreach ($temp['temp_ayuda_app'] as $user) {
            $result['ayuda_app'][] = [
                'title' => $user->nombre,
                'content' => $user->check_text_area,
                'position' => $user->orden,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }
    }

    public function setFaqData(&$result, $db)
    {
        $temp['temp_faq'] = $db->getTable('preguntas_frec')
            ->select()
            ->get();
        foreach ($temp['temp_faq'] as $user) {
            $result['faq'][] = [
                'title' => $user->pregunta,
                'content' => $user->respuesta,
                'active' => $user->estado,
                'position' => $user->orden,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }
    }


    public function setMediaData(&$result, $db)
    {
        $temp['temp_media'] = $db->getTable('media')
            ->select()
            ->get();
        foreach ($temp['temp_media'] as $user) {
            $result['media'][] = [
                'external_id' => $user->id,
                'title' => $user->title,
                'description' => $user->description,
                'file' => $user->file,
                'ext' => $user->ext,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        $result['media'] = array_chunk($result['media'], self::CHUNK_LENGTH, true);
    }

    public function setAccountsData(&$result, $db)
    {
        $temp['temp_accounts'] = $db->getTable('accounts')
            ->select()
            ->get();
        foreach ($temp['temp_accounts'] as $user) {
            $result['accounts'][] = [
                'external_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'password' => $user->password,
                'key' => $user->key,
                'secret' => $user->secret,
                'token' => $user->token,
                'refresh_token' => $user->refresh_token,
                'identifier' => $user->identifier,
                'sdk_token' => $user->sdk_token,
                'zak_token' => $user->zak_token,

                'service_id' => $user->service_id,
                'plan_id' => $user->plan_id,
                'type_id' => $user->type_id,

                'description' => $user->description,
                'active' => $user->active,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        // $result['accounts'] = array_chunk($result['accounts'], self::CHUNK_LENGTH, true);
    }

    public function setVademecumData(&$result, $db)
    {
        $temp['temp_vademecum'] = $db->getTable('vademecum')
            ->select()
            ->get();
        foreach ($temp['temp_vademecum'] as $user) {
            $result['vademecum'][] = [
                'external_id' => $user->id,
                'name' => $user->nombre,

                'media_id' => $user->media_id,
                'category_id' => $user->categoria_id,
                'subcategory_id' => $user->subcategoria_id,

                'active' => $user->estado,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        // $result['vademecum'] = array_chunk($result['vademecum'], self::CHUNK_LENGTH, true);
    }

    public function setVideotecaData(&$result, $db)
    {
        $temp['temp_videoteca'] = $db->getTable('videoteca')
            ->select()
            ->get();
        foreach ($temp['temp_videoteca'] as $user) {
            $result['videoteca'][] = [
                'external_id' => $user->id,
                'title' => $user->title,
                'description' => $user->description,
                'category_id' => $user->category_id,
                'media_video' => $user->media_video,
                'media_type' => $user->media_type,
                'media_id' => $user->media_id,
                'preview_id' => $user->preview_id,
                'active' => $user->active,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        // $result['videoteca'] = array_chunk($result['videoteca'], self::CHUNK_LENGTH, true);
    }

    public function setMeetingsData(&$result, $db)
    {
        $temp['temp_meetings'] = $db->getTable('meetings')
            ->select()
            ->get();
        foreach ($temp['temp_meetings'] as $user) {
            $result['meetings'][] = [
                'external_id' => $user->id,
                'name' => $user->name,
                'description' => $user->description,
                'embed' => $user->embed,
                'model_type' => $user->model_type,
                'model_id' => $user->model_id,
                'starts_at' => $user->starts_at,
                'finishes_at' => $user->finishes_at,
                'duration' => $user->duration,
                'started_at' => $user->started_at,
                'finished_at' => $user->finished_at,
                'url' => $user->url,
                'url_start' => $user->url_start,
                'identifier' => $user->identifier,
                'username' => $user->username,
                'password' => $user->password,
                'attendance_call_first_at' => $user->attendance_call_first_at,
                'attendance_call_middle_at' => $user->attendance_call_middle_at,
                'attendance_call_last_at' => $user->attendance_call_last_at,
                'url_start_generated_at' => $user->url_start_generated_at,
                'report_generated_at' => $user->report_generated_at,
                'status_id' => $user->status_id,
                'account_id' => $user->account_id,
                'type_id' => $user->type_id,
                'host_id' => $user->host_id,
                'user_id' => $user->user_id,
                'raw_data_response' => $user->raw_data_response,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        // $result['meetings'] = array_chunk($result['meetings'], self::CHUNK_LENGTH, true);
    }

    public function setAttendantsData(&$result, $db)
    {
        $temp['temp_attendants'] = $db->getTable('attendants')
            ->select()
            ->get();
        foreach ($temp['temp_attendants'] as $user) {
            $result['attendants'][] = [
                'external_id' => $user->id,
                'meeting_id' => $user->meeting_id,
                'usuario_id' => $user->usuario_id,
                'link' => $user->link,

                'total_logins' => $user->total_logins,
                'total_logouts' => $user->total_logouts,
                'total_duration' => $user->total_duration,

                'first_login_at' => $user->first_login_at,
                'first_logout_at' => $user->first_logout_at,

                'last_login_at' => $user->last_login_at,
                'last_logout_at' => $user->last_logout_at,

                'present_at_first_call' => $user->present_at_first_call,
                'present_at_middle_call' => $user->present_at_middle_call,
                'present_at_last_call' => $user->present_at_last_call,

                'confirmed_attendance_at' => $user->confirmed_attendance_at,
                'type_id' => $user->type_id,
                'online' => $user->online,
                'first_attempt_at' => $user->first_attempt_at,
                'identifier' => $user->identifier,
                'ip' => $user->ip,

                'browser_family_id' => $user->browser_family_id,
                'browser_version_id' => $user->browser_version_id,
                'platform_family_id' => $user->platform_family_id,
                'platform_version_id' => $user->platform_version_id,
                'device_family_id' => $user->device_family_id,
                'device_model_id' => $user->device_model_id,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        // $result['attendants'] = array_chunk($result['attendants'], self::CHUNK_LENGTH, true);
    }

    public function setTaxonomiesData(&$result, $db)
    {
        $temp['temp_tax'] = $db->getTable('taxonomies')
            ->select()
            ->get();
        foreach ($temp['temp_tax'] as $user) {
            $result['taxonomies'][] = [
                'external_id' => $user->id,
                'group' => $user->group,
                'type' => $user->type,
                'position' => $user->position,
                'code' => $user->code,
                'name' => $user->name,
                'path' => $user->path,
                'alias' => $user->alias,
                'icon' => $user->icon,
                'color' => $user->color,
                'slug' => $user->slug,
                'active' => $user->active,
                'description' => $user->description,
                'external_parent_id' => $user->parent_id,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        $result['taxonomies'] = array_chunk($result['taxonomies'], self::CHUNK_LENGTH, true);
    }

    public function setTaxonomiesEsData(&$result, $db)
    {
        $temp['temp_tax'] = $db->getTable('taxonomias')
            ->select()
            ->get();
        foreach ($temp['temp_tax'] as $user) {
            $result['taxonomies_es'][] = [
                'external_id_es' => $user->id,
                'group' => $user->grupo,
                'type' => $user->tipo,
                'code' => $user->code,
                'name' => $user->nombre,
                'active' => $user->estado,
                'external_parent_id_es' => $user->parent_taxonomia_id,

                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        }

        $result['taxonomies_es'] = array_chunk($result['taxonomies_es'], self::CHUNK_LENGTH, true);
    }
}
