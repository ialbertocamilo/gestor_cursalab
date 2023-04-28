<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | such as the size rules. Feel free to tweak each of these messages.
    |
    */

    'accepted'             => 'El campo :attribute debe ser aceptado.',
    'active_url'           => 'El campo :attribute no es una URL válida.',
    'after'                => 'El campo :attribute debe ser una fecha posterior a :date.',
    'alpha'                => 'El campo :attribute solo debe contener letras.',
    'alpha_dash'           => 'El campo :attribute solo debe contener letras, números y guiones.',
    'alpha_num'            => 'El campo :attribute solo debe contener letras y números.',
    'array'                => 'El campo :attribute debe ser un conjunto.',
    'before'               => 'El campo :attribute debe ser una fecha anterior a :date.',
    'between'              => [
        'numeric' => 'El campo :attribute tiene que estar entre :min - :max.',
        'file'    => 'El campo :attribute debe pesar entre :min - :max kilobytes.',
        'string'  => 'El campo :attribute tiene que tener entre :min - :max caracteres.',
        'array'   => 'El campo :attribute tiene que tener entre :min - :max ítems.',
    ],
    'boolean'              => 'El campo :attribute debe tener un valor verdadero o falso.',
    'confirmed'            => 'La confirmación de :attribute no coincide.',
    'current_password'     => 'La contraseña actual es incorrecta.',
    'date'                 => 'El campo :attribute no es una fecha válida.',
    'date_format'          => 'El campo :attribute no corresponde al formato :format.',
    'different'            => 'El campo :attribute y :other deben ser diferentes.',
    'digits'               => 'El campo :attribute debe tener :digits dígitos.',
    'digits_between'       => 'El campo :attribute debe tener entre :min y :max dígitos.',
    'distinct'             => 'El campo :attribute contiene un valor duplicado.',
    'email'                => 'El campo :attribute no es un correo válido',
    'exists'               => 'El campo :attribute es inválido.',
    'filled'               => 'El campo :attribute es obligatorio.',
    'image'                => 'El campo :attribute debe ser una imagen.',
    'in'                   => 'El campo :attribute es inválido.',
    'in_array'             => 'El campo :attribute no existe en :other.',
    'integer'              => 'El campo :attribute debe ser un número entero.',
    'ip'                   => 'El campo :attribute debe ser una dirección IP válida.',
    'json'                 => 'El campo :attribute debe tener una cadena JSON válida.',
    'max'                  => [
        'numeric' => 'El campo :attribute no debe ser mayor a :max.',
        'file'    => 'El campo :attribute no debe ser mayor que :max kilobytes.',
        'string'  => 'El campo :attribute no debe ser mayor que :max caracteres.',
        'array'   => 'El campo :attribute no debe tener más de :max elementos.',
    ],
    'mimes'                => 'El campo :attribute debe ser un archivo con formato: :values.',
    'min'                  => [
        'numeric' => 'El tamaño de :attribute debe ser de al menos :min.',
        'file'    => 'El tamaño de :attribute debe ser de al menos :min kilobytes.',
        'string'  => 'El campo :attribute debe contener al menos :min caracteres.',
        'array'   => 'El campo :attribute debe tener al menos :min elementos.',
    ],
    'not_in'               => 'El campo :attribute es inválido.',
    'numeric'              => 'El campo :attribute debe ser numérico.',
    'present'              => 'El campo :attribute debe estar presente.',
    'regex'                => 'El formato de :attribute es inválido.',
    'required'             => 'El campo :attribute es obligatorio.',
    'required_if'          => 'El campo :attribute es obligatorio cuando :other es :value.',
    'required_unless'      => 'El campo :attribute es obligatorio a menos que :other esté en :values.',
    'required_with'        => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_with_all'    => 'El campo :attribute es obligatorio cuando :values está presente.',
    'required_without'     => 'El campo :attribute es obligatorio cuando :values no está presente.',
    'required_without_all' => 'El campo :attribute es obligatorio cuando ninguno de :values estén presentes.',
    'same'                 => 'El campo :attribute y :other deben coincidir.',
    'size'                 => [
        'numeric' => 'El tamaño de :attribute debe ser :size.',
        'file'    => 'El tamaño de :attribute debe ser :size kilobytes.',
        'string'  => 'El campo :attribute debe contener :size caracteres.',
        'array'   => 'El campo :attribute debe contener :size elementos.',
    ],
    'string'               => 'El campo :attribute debe ser una cadena de caracteres.',
    'timezone'             => 'El :attribute debe ser una zona válida.',
    'unique'               => 'El campo :attribute ya ha sido registrado.',
    'url'                  => 'El formato :attribute es inválido.',

    'password' => [
        'letters' => 'El campo :attribute debe contener una letra.',
        'mixed' => 'El campo :attribute debe contener al menos una letra mayúscula y una minúscula.',
        'numbers' => 'El campo :attribute debe contener al menos un número.',
        'symbols' => 'El campo :attribute debe contener al menos un símbolo.',
        'uncompromised' => 'La :attribute proporcinada no es segura. Por favor cambia la :attribute.',
    ],
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'current_password' => [
            'current_password' => 'La contraseña actual no es correcta.',
        ],

        //  'answer_id' => [
        //     'answer_ontime' => 'Respuesta fuera de tiempo',
        // ],

        // 'products.*.product_id' => [
        //     'required' => 'Cada producto debe estar marcado.',
        //     // 'required' => 'Cada producto debe estar marcado.',
        // ],
        // 'products.*.quantity' => [
        //     'required' => 'La cantidad de cada producto es requerida.',
        //     'integer' => 'La cantidad de cada producto debe ser entera.',
        //     'min' => 'La cantidad mínima de cada producto es de :min.',
        //     'max' => 'La cantidad máxima de cada producto es de :max.',
        // ],

        'answer_id' => [
            'answer_ontime' => 'Respuesta fuera de tiempo',
        ],

        'config_id' => [
            'required' => 'Debe de seleccionar un módulo.'
        ],

        'type_id' => [
            'account_type_available' => 'No se encuentra una cuenta disponible para este tipo de reunión.',
        ],

        'host_id' => [
            'available_for_meeting' => 'El anfitrión no se encuentra disponible en este horario.',
        ],
        'meeting_id' => [
            'meeting_can_be_finished' => 'La reunión no se encuentra en progreso o no cuentas con el permiso de  anfitrión para realizar esta acción.',
        ],
        'starts_at' => [
            'meeting_date_after_or_equal_today' => 'El campo fecha y hora debe ser una fecha posterior o igual a la hora actual.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name' => 'nombre',
        'fullname' => 'nombres y apellidos',
        'lastname' => 'apellido paterno',
        'surname' => 'apellido materno',
        'username' => 'usuario',
        'company' => 'empresa',
        'active' => 'activo',
        'email' => 'correo electrónico',
        'first_name' => 'nombre',
        'last_name' => 'apellido',
        'password' => 'contraseña',
        'password_confirmation' => 'confirmación de la contraseña',
        'city' => 'ciudad',
        'country' => 'país',
        'address' => 'dirección',
        'phone' => 'teléfono',
        'telephone' => 'teléfono fijo',
        'cellphone' => 'teléfono celular',
        'mobile' => 'celular',
        'age' => 'edad',
        'sex' => 'sexo',
        'gender' => 'género',
        'year' => 'año',
        'month' => 'mes',
        'day' => 'día',
        'hour' => 'hora',
        'minute' => 'minuto',
        'second' => 'segundo',
        'title' => 'título',
        'body' => 'contenido',
        'description' => 'descripción',
        'excerpt' => 'extracto',
        'date' => 'fecha',
        'time' => 'hora',
        'subject' => 'asunto',
        'message' => 'mensaje',
        'answer_id' => 'respuesta',
        'question_id' => 'pregunta',
        'option_id' => 'opción',
        'subtitle' => 'subtítulo',
        'title_short' => 'título corto',
        'content' => 'contenido',
        'old_passwords' => 'Antigua contraseña',

    
        'status_id' => 'estado',
        'platform_id' => 'plataforma',
        'service_id' => 'servicio',
        'client_id' => 'cliente',
        'type_id' => 'tipo',
        'plan_id' => 'plan',
        'user_id' => 'usuario',
        'source_id' => 'fuente',
        'model_id' => 'modelo',
        'role' => 'rol',
        'role_id' => 'rol / área',
        
        'code' => 'código',
        'storage_id' => 'storage',
        'worker_id' => 'worker',
        'instance_id' => 'instancia',
        'database_id' => 'base de datos',
        'comments' => 'comentarios',
        'registered_at' => 'fecha de registro',
        
        'ruc' => 'RUC',
        'id' => 'ID',
        'dni' => 'DNI',
        'country_id' => 'país',
        'max_limit' => 'límite máximo',
        'identifier' => 'identificador',
        'ip' => 'IP',
        'limit_type_id' => 'tipo de límite',
        'detail' => 'detalle',
        'storage' => 'almacenamiento',

        'created_at' => 'fecha de creación',
        'updated_at' => 'fecha de actualización',
        'deleted_at' => 'fecha de eliminación',

        'subdomain' => 'subdominio',
        'domain' => 'dominio',
        'token' => 'token',
        'host' => 'host',
        'dns' => 'DNS',
        'starts_at' => 'fecha de inicio',
        'finishes_at' => 'fecha de fin',

        'database_name' => 'nombre de base de datos',
        'storage_name' => 'nombre de bucket',

        'abilities' => 'permisos',
        'ability' => 'permiso',
        'slug' => 'slug',
        'app_id' => 'aplicación',
        'section_id' => 'sección',
        'category_id' => 'categoría',
        // 'position' => 'posición',
        'email_verified_at' => 'fecha de verificación de correo',
        'display_name' => 'nombre a mostrar',
        'parent_id' => 'padre',

        'currency' => 'moneda',
        'interval_id' => 'periodo',
        'price' => 'precio',
        'solver_id' => 'encargado',
        'reporter_id' => 'reportador',
        'priority_id' => 'prioridad',
        'reported_at' => 'fecha de reporte',
        'attended_at' => 'fecha de atención',
        'solved_at' => 'fecha de solución',
        'closed_at' => 'fecha de cerrado',

        'required' => 'obligatorio',
        'field_id' => 'campo',
        'sector_id' => 'rubro',
        'validation_id' => 'validación',

        'channel_id' => 'medio',
        'reference_id' => 'referencia',
        'section_id' => 'sección',
        'job_position_id' => 'cargo',
        'max_employee_id' => 'cantidad de empleados',
        'ends_at' => 'fecha fin',
        'link' => 'enlace',
        'generated_conversions' => 'conversiones generadas',
        'model_type' => 'tipo de modelo',
        'uuid' => 'UUID',
        'collection_name' => 'nombre de colección',
        'file_name' => 'nombre de archivo',
        'mime_type' => 'tipo de mime',
        'disk' => 'disco',
        'conversions_disk' => 'conversiones en disco',
        'size' => 'tamaño',
        'manipulations' => 'manipulaciones',
        'custom_properties' => 'propiedades custom',
        'responsive_images' => 'imágenes responsivas',
        'order_column' => 'posición',
        'name_text' => 'nombre (texto)',
        'criterion_id' => 'criterio',
        'assessable' => 'evaluable',
        'module_id' => 'módulo',
        'random' => 'aleatorio',
        'anonymous' => 'anónimo',
        'time_limit' => 'duración límite',
        'area_id' => 'área',
        'gender_id' => 'género',
        'document_type_id' => 'tipo de documento',
        'document_number' => 'número de documento',
        'district_id' => 'distrito',
        'birthdate' => 'fecha de nacimiento',
        'task_id' => 'tarea',
        'author_id' => 'autor',
        'editor_id' => 'editor',
        'schedule_publish_at' => 'horario de publicación',
        'started_at' => 'empezó a',
        'finished_at' => 'finalizó a',
        'approved_at' => 'aprobado a',
        'published_at' => 'publicado a',
        'times_restarted' => 'veces reiniciadas',
        'campaign_id' => 'campaña',
        'summary' => 'resumen',
        'company_id' => 'empresa',
        'contact_id' => 'contacto',
        'account_id' => 'cuenta',

        'original_url' => 'url original',
        'preview_url' => 'url preview',

        'post_id' => 'contenido',
        'value' => 'valor',
        'scheduled_at' => 'programado a',
        'average_rating' => 'calificación promedio',
        'external_id' => 'ID externo',
        'file_logo' => 'logo',
        'position' => 'orden',
        'rpta_ok' => 'respuesta correcta',

        'today' => 'hoy',
        'duration' => 'duración',
        'attendants' => 'asistentes',
        'q' => 'búsqueda',
        'course_id' => 'Curso',
        'visits_count' => 'contador de visitas',
        'topic_requirement_id' => 'requisito de tema',
        'type_evaluation_id' => 'tipo de evaluación',
        'duplicate_id' => 'ID duplicado',
        'evaluation_verified' => 'evaluación verificada',

        'subworkspace_id' => 'módulo',
        'email_gestor' => 'email gestor',
        'expires_code' => 'codigo de expiración',
        'enable_2fa' => 'habilitar 2FA',
        'pass_token_updated' => 'token de la contraseña actualizada',
        'last_pass_updated_at' => 'última contraseña actualizada',
        'attempts' => 'intentos',
        'attempts_lock_time' => 'tiempo de bloqueo de intentos',
        'attempts_times_locks' => 'intentos, tiempo de bloqueo',
        'document' => 'documento',
        'person_number' => 'número de persona',
        'phone_number' => 'número de teléfono',
        'token_firebase' => 'Token Firebase',
        'v_android' => 'Versión Android',
        'v_ios' => 'Versión IOS',
        'last_login' => 'Último Login',
        'summary_user_update' => 'Resumen de actualización de usuario',
        'summary_course_update' => 'Resumen de actualización de curso',
        'summary_course_data' => 'resumen de datos de curso',
        'required_update_at' => 'fecha de la actualización requerida',
        'last_summary_updated_at' => 'Última actualización de resumen',
        'is_updating' => 'Actualizando',
        'secret_key' => 'Clave secreta',
        'user_relations' => 'Relaciones de usuario',
        'completed' => 'completado',
        'assigned' => 'asignado',
        'passed' => 'aprobado',
        'taken' => 'realizado',
        'reviewed' => 'revisado',
        'failed' => 'desaprobado',
        'grade_average' => 'nota promedio',
        'advanced_percentage' => 'porcentaje logrado',
        'views' => 'visitas',
        'restarts' => 'reinicios',
        'restarter_id' => 'renicios id',
        'old_admin_id' => 'antiguo admin id',
        'last_time_evaluated_at' => 'fecha de la última vez evaluado',
        'certification_issued_at' => 'fecha del problema de certificación ',
        'certification_accepted_at' => 'fecha de la certificación aceptada',

        'topic_id' => 'Tema',
        'correct_Answers' => 'respuestas correctas',
        'failed_Answers' => 'respuestas incorrectas',
        'downloads' => 'descargas',
        'grade' => 'nota',
        'answers' => 'respuestas',
        'current_quiz_started_at' => 'fecha del inicio del cuestionario actual',
        'current_quiz_finishes_at' =>
            'fecha del finalización del cuestionario actual',
        'taking_quiz' => 'cuestionario en curso',
        'answers_old' => 'Respuestas anteriores',

        'correct_answers' => 'Respuestas Correctas',
        'failed_answers' => 'Respuestas Incorrectas','publish_date' => 'Fecha de Publicación',
        'end_date' => 'Fecha de Finalización',
        'workspace_id' => 'Id del Workspace',
        'media_id' => 'id de la media',
        'media_type' => 'tipo de media',
        'subcategory_id' => 'id de la subcategoría',
        'attendance_call_first_at' =>
            'fecha de la primera llamada de asistencia',
        'attendance_call_middle_at' =>
            'fecha de la media llamada de asistencia',
        'attendance_call_last_at' => 'fecha de la ultima llamada de asistencia',
        'url_start_generated_at' => 'fecha de generación de la url de inicio',
        'report_generated_at' => 'fecha de generación del reporte',
        'host_id' => 'id del Host',
        'url_start' => 'Url de inicio',
        'categoria_id' => 'id categoría',
        'jerarquia_id' => 'id jerarquia',

        'usuario_id' => 'id usuario',
        'meeting_id' => 'id de la reunión',
        'check_text_area' => 'Verificar Texto',
        'contact' => 'Contacto',
        'info_support' => 'Info Soporte',
        'msg_to_user' => 'Mensaje al usuario',
        'preview_id' => 'Id de la vista previa',
        'media_video' => 'Media Video',
        'value_text' => 'texto valor',
        'courses_assigned' => 'Cursos Asignados',

        'courses_completed' => 'Cursos completados',
        // App\Model\Topic
        'active_results' => 'Activar resultados',
        'has_to_pass_to_continue' => 'Aprobar para continuar',

        // App\Model\Questions
        'rptas_json' => 'Respuestas',

        // App\Model\SectionUpload
        'fields' => 'filtros',
        'file' => 'archivo',


    ],

];
