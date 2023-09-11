<?php
    setlocale(LC_TIME, 'es_PE.UTF-8');
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
        
        @if($data['old_template'] === true)

            <img src="{{ $data['image'] }}" alt="Certificado">
            <span class="nombre default">{{ $data['users'] }}</span>
            <span class="curso default">{{ $data['courses'] }}</span>
        
            @if ($data['show_certification_date'])
                <span class="fecha">{{ fechaCastellano($data['fecha']) }}</span>
            @endif

        @else

            <img style="position: absolute; z-index: 0; top: 0; left: 0"
                 src="{{ $data['image'] }}"
                 alt="Certificado">
        @endif

    </div>
</body>
</html>
