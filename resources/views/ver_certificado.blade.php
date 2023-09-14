<?php
setlocale(LC_TIME, 'es_PE.UTF-8');

function fechaCastellano($fecha)
{
    $fecha = substr($fecha, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));
    $dias_ES = array("Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo");
    $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $nombredia = str_replace($dias_EN, $dias_ES, $dia);
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    // return "Lima, ".$numeroDia." de ".$nombreMes." de ".$anio;
    return $numeroDia . " de " . $nombreMes . " del " . $anio;
}

/* Editable templates' values positions
---------------------------------------- */


$position_certificate = $data['position_certificate'];

$position_user = $data['position_user'];
$position_course = $data['position_course'];
$position_grade = $data['position_grade'];
$position_date = $data['position_date'];
$background_width = $data['background_width'];

?>
    <!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="all,follow">

    <title>Certificado</title>
    <link type="text/css" media="all" rel="stylesheet" href="{{ asset('css/certi.css') }}">
</head>
<body>

<div class="container" id="certi" download="canvasexport.png">

    {{-- Old template
    ========================================--}}

    @if (!$position_user && !$position_course)

        <img src="{{ $data['image'] }}" alt="">
        <span class="nombre default">{{ $data['usuario'] }}</span>
        <span class="curso default">{{ $data['video'] }}</span>
        @if ($data['show_certification_date'])
            <span class="fecha">{{ fechaCastellano($data['fecha']) }}</span>
        @endif

    @else

    {{-- Editable templates
    ========================================--}}


        <span style="position: absolute;
                     z-index: 1;
                     width: {{ $background_width . 'px' }};
                     text-align: {{ $position_course->centrado ? 'center' : 'left'  }};
                     top: {{ $position_user->top.'px' }};
                     left: {{ $position_user->centrado ? 0 : $position_user->left - $position_certificate->left .'px' }};
                     font-weight: {{ $position_user->fontWeight }};
                     font-style: {{ $position_user->fontStyle }};
                     font-size: {{ $position_user->fontSize - ($position_user->fontSize * $position_user->zoomX) .'px' }};
                     color: {{ $position_user->fill }}"
                     >
            {{ $data['usuario'] }}
        </span>

        <span style="position: absolute;
                     z-index: 1;
                     width: {{ $background_width . 'px' }};
                     top: {{ $position_course->top.'px' }};
                     left: {{ $position_course->centrado ? 0 : $position_course->left - $position_certificate->left .'px' }};
                     text-align: {{ $position_course->centrado ? 'center' : 'left'  }};
                     font-weight: {{ $position_course->fontWeight }};
                     font-style: {{ $position_course->fontStyle }};
                     font-size: {{ $position_course->fontSize - ($position_course->fontSize * $position_course->zoomX) .'px' }};
                     color: {{ $position_course->fill }}"
        >
            {{ $data['video'] }}
        </span>

        @if ($position_grade)
        <span style="position: absolute;
                     z-index: 1;
                     top: {{ $position_grade->top.'px' }};
                     left: {{ $position_grade->left - $position_certificate->left.'px' }};
                     font-weight: {{ $position_grade->fontWeight }};
                     font-style: {{ $position_grade->fontStyle }};
                     font-size: {{ $position_grade->fontSize - ($position_grade->fontSize * $position_grade->zoomX) .'px' }};
                     color: {{ $position_grade->fill }}">
            {{ $data['grade'] }}
        </span>
        @endif

        @if ($position_date)
        <span style="position: absolute;
                     z-index: 1;
                     width: 100%;
                     top: {{ $position_date->top.'px' }};
                     left: {{ $position_date->left - $position_certificate->left.'px' }};
                     font-weight: {{ $position_date->fontWeight }};
                     font-style: {{ $position_date->fontStyle }};
                     font-size: {{ $position_date->fontSize - ($position_date->fontSize * $position_date->zoomX) .'px' }};
                     color: {{ $position_date->fill }}">
            {{ fechaCastellano($data['fecha']) }}
        </span>
        @endif

        <img style="position: absolute;
                     z-index: 0;
                     top: 0;
                     left: 0"
             src="{{ $data['image'] }}"
             alt="plantilla diploma">


    @endif
</div>
</body>
</html>
