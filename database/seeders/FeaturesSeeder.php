<?php

namespace Database\Seeders;

// use App\Models\Feature;
use App\Models\Taxonomy;

use Illuminate\Database\Seeder;

class FeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $platforms = Taxonomy::where('group', 'system')->where('type', 'platform')->get();
        // $sections = Taxonomy::where('group', 'feature')->where('type', 'section')->get();
        // $types = Taxonomy::where('group', 'feature')->where('type', 'type')->get();

        // //  General

        // $general_id = $sections->where('code', 'general')->first()->id;

        // $boolean_id = $types->where('code', 'boolean')->first()->id;
        // $limit_id = $types->where('code', 'limit')->first()->id;
        // $increment_id = $types->where('code', 'increment')->first()->id;

        // $platform_client_id = $platforms->where('code', 'client')->first()->id;
        // $platform_user_id = $platforms->where('code', 'user')->first()->id;


        // Feature::create([
        //     'name' => 'Almacenamiento de archivos (GB)',
        //     'code' => 'storage',
        //     'section_id' => $general_id,
        //     'type_id' => $increment_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Administradores del Gestor',
        //     'code' => 'administrators',
        //     'section_id' => $general_id,
        //     'type_id' => $limit_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Detalle de avance de usuarios en el gestor',
        //     'description' => 'Notas usuario: Permite ver el avance y datos de evaluaciones de cada colaborador luego de ingresar su DNI o usuario.',
        //     'code' => 'users-progress-detail',
        //     'section_id' => $general_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Número de criterios usados para segmentar',
        //     'code' => 'criteria-segmentation-limit',
        //     'section_id' => $general_id,
        //     'type_id' => $limit_id,
        //     'platform_id' => $platform_client_id,
        // ]);
        
        // Feature::create([
        //     'name' => 'Learning Analytics',
        //     'code' => 'learning-analytics',
        //     'section_id' => $general_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Segmentación de anuncios por criterios',
        //     'code' => 'announcements-criteria-segmentation',
        //     'section_id' => $general_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // // Multimedia

        // $media_id = $sections->where('code', 'media')->first()->id;
        
        // Feature::create([
        //     'name' => 'Descarga de recursos multimedia',
        //     'code' => 'courses-media-download',
        //     'section_id' => $media_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_user_id,
        // ]);

        // Feature::create([
        //     'name' => 'Subida de archivos de video',
        //     'code' => 'courses-media-upload-video',
        //     'section_id' => $media_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Subida de archivos de audio',
        //     'code' => 'courses-media-upload-audio',
        //     'section_id' => $media_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Subida de archivos de PDF',
        //     'code' => 'courses-media-upload-pdf',
        //     'section_id' => $media_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Subida de archivos de Office',
        //     'code' => 'courses-media-upload-office',
        //     'section_id' => $media_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Subida de archivos SCORM',
        //     'code' => 'courses-media-upload-scorm',
        //     'section_id' => $media_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Integración con YouTube',
        //     'code' => 'courses-media-integration-youtube',
        //     'section_id' => $media_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Integración con Vimeo',
        //     'code' => 'courses-media-integration-vimeo',
        //     'section_id' => $media_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // $course_id = $sections->where('code', 'courses')->first()->id;

        // // Cursos

        // Feature::create([
        //     'name' => 'Límite de subida de recursos multimedia',
        //     'code' => 'courses-media-upload',
        //     'section_id' => $course_id,
        //     'type_id' => $limit_id,
        //     'platform_id' => $platform_client_id,
        // ]);
     
        // Feature::create([
        //     'name' => 'Integraciones para ahorrar almacenamiento',
        //     'code' => 'storage-integration',
        //     'section_id' => $course_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Progreso: Avance de cursos completados',
        //     'code' => 'progress-limit',
        //     'section_id' => $course_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);      

        // // Exportaciones

        // $export_id = $sections->where('code', 'exports')->first()->id;

        // Feature::create([
        //     'section_id' => $export_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        //     'name' => 'Reporte de encuestas',
        //     'code' => 'polls-report',
        // ]);

        // Feature::create([
        //     'section_id' => $export_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        //     'name' => 'Descarga de la planilla de colaboradores',
        //     'code' => 'users-download',
        // ]);

        // Feature::create([
        //     'section_id' => $export_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        //     'name' => 'Visitas a los temas',
        //     'code' => 'topics-visits',
        // ]);

        // Feature::create([
        //     'section_id' => $export_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        //     'name' => 'Auditoría de reinicios en colaboradores',
        //     'code' => 'users-audit-restarts',
        // ]);

        // Feature::create([
        //     'section_id' => $export_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        //     'name' => 'Consolidado por curso: filtros a todo nivel (por defecto solo por escuelas)',
        //     'code' => 'report-advanced-course',
        // ]);

        // Feature::create([
        //     'section_id' => $export_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        //     'name' => 'Consolidado por tema: filtros a todo nivel (por defecto solo por escuelas)',
        //     'code' => 'report-advanced-topics',
        // ]);


        // // Importaciones

        // $import_id = $sections->where('code', 'imports')->first()->id;

        // Feature::create([
        //     'name' => 'Subida de cursos',
        //     'code' => 'import-courses',
        //     'section_id' => $import_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Cesar usuarios',
        //     'code' => 'users-inactivate',
        //     'section_id' => $import_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);


        // // Soporte al Cliente

        // $support_id = $sections->where('code', 'support')->first()->id;

        // Feature::create([
        //     'name' => 'Soporte telefónico al cliente',
        //     'code' => 'support-phone',
        //     'section_id' => $support_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Formulario de soporte de usuario al cliente',
        //     'code' => 'support-user-form',
        //     'section_id' => $support_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // // Evaluaciones

        // $test_id = $sections->where('code', 'tests')->first()->id;

        // Feature::create([
        //     'name' => 'Personalizar el sistema de calificación por evaluación',
        //     'code' => 'tests-system-qualification-individual-customization',
        //     'section_id' => $test_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Personalizar número de intentos por evaluación',
        //     'code' => 'tests-max-attemptd',
        //     'section_id' => $test_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Colocar puntaje a las preguntas',
        //     'code' => 'tests-question-points',
        //     'section_id' => $test_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Preguntas libres (abiertas)',
        //     'code' => 'tests-question-free',
        //     'section_id' => $test_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Configurar preguntas obligatorias',
        //     'code' => 'tests-mandatory-questions',
        //     'section_id' => $test_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);


        // // Certificados

        // $certification_id = $sections->where('code', 'certifications')->first()->id;

        // Feature::create([
        //     'name' => 'Plantillas personalizadas',
        //     'code' => 'certification-templates',
        //     'section_id' => $certification_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Certificados por aprobación de cursos',
        //     'code' => 'certification-courses-approved',
        //     'section_id' => $certification_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);


        // Feature::create([
        //     'name' => 'Descargas en alta calidad',
        //     'code' => 'certification-download',
        //     'section_id' => $certification_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_user_id,
        // ]);

        // Feature::create([
        //     'name' => 'Check de aceptación de certificado',
        //     'code' => 'certification-check',
        //     'section_id' => $certification_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_user_id,
        // ]);
        

        // // Encuestas
        
        // $poll_id = $sections->where('code', 'polls')->first()->id;

        // Feature::create([
        //     'name' => 'Crear encuestas libres',
        //     'code' => 'polls-free-create',
        //     'section_id' => $poll_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Realizar Encuestas libres',
        //     'code' => 'polls-free',
        //     'section_id' => $poll_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_user_id,
        // ]);

        // Feature::create([
        //     'name' => 'Encuestas: Opción múltiple',
        //     'code' => 'polls-multiple-choice',
        //     'section_id' => $poll_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Encuestas: Opción introducir texto',
        //     'code' => 'polls-text-choice',
        //     'section_id' => $poll_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Encuestas Libres: Opción múltiple',
        //     'code' => 'polls-free-multiple-choice',
        //     'section_id' => $poll_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Encuestas Libres: Opción introducir texto',
        //     'code' => 'polls-free-text-choice',
        //     'section_id' => $poll_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Descarga de resultados',
        //     'code' => 'polls-download-results',
        //     'section_id' => $poll_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Descarga en cuadros estadísticos',
        //     'code' => 'polls-download-report',
        //     'section_id' => $poll_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // // Otros

        // $other_id = $sections->where('code', 'others')->first()->id;

        // Feature::create([
        //     'name' => 'Envío de Notificaciones',
        //     'code' => 'notifications',
        //     'section_id' => $other_id,
        //     'type_id' => $limit_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Envío de Notificaciones filtrado por módulos',
        //     'code' => 'notifications',
        //     'section_id' => $other_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);

        // Feature::create([
        //     'name' => 'Envío de Notificaciones filtrado por criterios',
        //     'code' => 'notifications',
        //     'section_id' => $other_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_client_id,
        // ]);
        
        // Feature::create([
        //     'name' => 'Ranking filtrado por criterios',
        //     'code' => 'ranking-criteria',
        //     'section_id' => $other_id,
        //     'type_id' => $boolean_id,
        //     'platform_id' => $platform_user_id,
        // ]);
        
    }
}
