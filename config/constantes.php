<?php

return [
    'tipopreg' => [
    	'texto' => 'Respuesta en texto',
		'simple'=> 'Opción única',
		'multiple'=> 'Opción múltiple',
		'califica'=>'Calificar del 1 al 5'
    ],
    'tipos_scorm'=>[
        'index.html',
        'story.html',
        'genially.html'
    ],
    'modalidad' => [
    	  'regular' => 'REGULAR (dentro de malla)',
		    'extra'=> 'EXTRACURRICULAR (fuera de la malla)',
		    'libre'=> 'LIBRE (no forma parte del progreso)',
    ],

    'tipo_ev' => [
        '' => 'No evaluable',
        'calificada' => 'Calificada',
        'abierta'=> 'Abierta'
    ],

    'arr_estados' => [
      "" => "",
      "aprobado" => "Aprobado",
      "realizado" => "Realizado",
      "revisado" => "Revisado",
      "desaprobado" => "Desaprobado",
      "desarrollo" => "En desarrollo",
      "pendiente" => "Pendiente",
      "enc_pend" => "Encuesta Pendiente",
      "bloqueado" => "Bloqueado",
      "completado" => "Completado",
      "por-iniciar" => "Por iniciar",
      "continuar" => "En desarrollo"
    ],

    //para errores masivos (inicio)
    'género'=>[
      'item_text'=>'nombre',
      'select'=>[
        ['id'=>'M','nombre'=>'M'],
        ['id'=>'F','nombre'=>'F'],
      ],
    ],
    'acción' =>[
      'item_text'=>'nombre',
      'select'=>[
        ['id'=>'Nuevo','nombre'=>'Nuevo'],
        ['id'=>'datos','nombre'=>'datos'],
      ],
    ],
    'acción_migración' =>[
      'item_text'=>'nombre',
      'select'=>[
        ['id'=>'Cambio de carrera','nombre'=>'Cambio de carrera'],
        ['id'=>'Cambio de módulo','nombre'=>'Cambio de módulo'],
        ['id'=>'Cambio de ciclo','nombre'=>'Cambio de ciclo'],
      ],
    ],
    'audit_events' => [
        'created' => 'Creado',
        'updated' => 'Actualizado',
        'deleted' => 'Eliminado',
    ],

    'audit_events_action' => [
        'created' => 'Creó',
        'updated' => 'Actualizó',
        'deleted' => 'Eliminó',
    ],

    'audits_models_app' => [
        'Eventos' => 'Aulas Virtuales',

        'AsistenteEvento' => 'Asistencia a evento',
//        'ActividadEvento' => 'Actividad en evento',
        'ChecklistRpta' => 'Resumen de respuestas de Checklist',
        'ChecklistRptaItem' => 'Respuesta de Checklist',
        'Prueba' => 'Prueba',
        'Usuario_upload' => 'Archivo adjunto',

        'Usuario_rest' => 'Usuario',
        'Videoteca' => 'Videoteca'
    ],
    'audits_models_gestor' => [
        'Abconfig' => 'Módulo',
        'Botica' => 'Botica',
        'Anuncio' => 'Anuncio',

        'Carrera' => 'Carrera',
        'Categoria' => 'Categoría',

        'CheckList' => 'CheckList',
        'CheckListItem' => 'Actividad de Checklist',

        'Ciclo' => 'Ciclo',
        'Criterio' => 'Criterio',
        'CuentaZoom' => 'Cuenta de Zoom',
        'Curricula' => 'Segmentación',

        'Curso' => 'Curso',
        'Curso_encuesta' => 'Curso con Encuesta',
        'Diploma' => 'Diploma',
        'Encuesta' => 'Encuesta',
        'Encuesta_pregunta' => 'Pregunta de Encuesta',

        'Grupo' => 'Grupo de matrícula',

        'Incidencia' => 'Incidencia',
        'Matricula' => 'Matrícula',

//        'Resumen_general' => 'Resumen general',
//        'Resumen_x_curso' => 'Resumen por curso',

        'Media' => 'Multimedia',

        'NotificacionPush' => 'Notificación Push',
        'Posteo' => 'Temas',
        'Pregunta' => 'Pregunta de Tema',
        'Pregunta_frecuente' => 'Pregunta frecuente',


        'Reinicio' => 'Reinicio de intento',
        'User' => 'Administrador de Gestor',
        'Usuario' => 'Usuario',

        'UsuarioAyuda' => 'Soporte al Usuario',
        'Vademecum' => 'Vademecum',
        'Videoteca' => 'Videoteca'
    ],

    'estado' =>[
      'item_text'=>'nombre',
      'select'=>[
        ['id'=>'Activo','nombre'=>'Activo'],
        ['id'=>'Inactivo','nombre'=>'Inactivo'],
      ],
    ],
    //para errores masivos (fin)
    'extensiones' => [
        'image' => ['jpeg', 'jpg', 'png', 'gif', 'svg', 'webp'],
        'office' => ['xls', 'xlsx', 'ppt', 'pptx', 'doc', 'docx'],
        'video' => ['mp4', 'webm', 'mov'],
        'audio' => ['mp3'],
        'pdf' => ['pdf'],
        'scorm' => ['scorm', 'zip'],
    ],
    'extensiones_es' => [
        'image' => 'Imagen',
        'office' => 'Office',
        'video' => 'Video',
        'audio' => 'Mp3',
        'pdf' => 'PDF',
        'scorm' => 'Scorm',
    ],

    'videoteca-test-tags' => [
        'covid', 'covid-19', 'sintomas', 'pandemia', 'vacunas',
        'cupones', 'monedero', 'promociones', 'descuentos', 'bonus',
        'delivery', 'motorizado', 'pedidos', 'online', 'flete',
        'limpieza', 'cuidado-personal', 'protección', 'seguridad', 'mascarilla',
        'nutrición', 'vitaminas', 'suplementos', 'complementos', 'natural',
    ],

    'videoteca-test-categories' => [
        'Adulto Mayor', 'Belleza', 'Cuidado Personal', 'Cuidado Capilar','Dermocosmética', 'Dispositivos Médicos',
        'Cuidado del Bebé'
    ],

    'soporte-estados' => [
        'pendiente' => 'Pendiente',
        'revisando' => 'Revisando',
        'solucionado' => 'Solucionado',
    ],

    'soporte-estados-colors' => [
        'pendiente' => 'red',
        'revisando' => 'orange',
        'solucionado' => 'blue',
    ],

    'media-types' => [
        'youtube' => ['nombre' => 'Youtube', 'id' => 'youtube'],
        'vimeo' => ['nombre' => 'Vimeo', 'id' => 'vimeo'],
        'audio' => ['nombre' => 'Audio', 'id' => 'audio'],
        'video' => ['nombre' => 'Video', 'id' => 'video'],
        'pdf' => ['nombre' => 'PDF', 'id' => 'pdf'],
        'scorm' => ['nombre' => 'SCORM', 'id' => 'scorm'],
        'image' => ['nombre' => 'Imagen', 'id' => 'image'],
    ],

    'cuentas-zoom-tipos' => [
        'PRO' => 'PRO',
        'BUSINESS' => 'BUSINESS',
    ],
];
