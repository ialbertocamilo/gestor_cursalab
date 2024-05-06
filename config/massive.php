<?php

return [
    'max-uploads'=>collect([
        ['type'=>'user_update_massive','max'=>500],
        ['type'=>'change_status_massive','max'=>2500],
        ['type'=>'upload_topic_grades_massive','max'=>6000],
    ]),
    'upload-topic-grades' =>[
        'Solo se actualizan las notas si el usuario tiene el curso asignado.',
        'Solo se registran o actualizan notas a temas que son evaluables calificadas y no evaluables.',
        'En el caso de las calificadas solo se actualiza la nota del excel si es mayor a la nota del sistema.',
        'Si no existe una nota, se registra la nota del excel.',
        'Se registran y actualizan las notas del excel para todos los temas del curso seleccionado; en caso el curso contenga temas no evaluables estas se marcarán como revisados.',
        'Las notas en el excel deben estar en el rango correspondiente al sistema de calificación del curso.',
        'El progreso del usuario se verá reflejado en los reportes luego de unos minutos.',
        'La cantidad máxima es de 6000 (seis mil) filas por excel',
        'Si se encuentra algún error, se podrá descargar un excel con la lista de errores.',
        'Para los campos de las fechas se puede usar dos formatos: dd/mm/yyyy ó dd/mm/yyyy hh:mm, tomando en cuenta que la hora esta expresada en formato
         24 horas, ejemplo: 23/12/2024 14:33'
    ]
];
