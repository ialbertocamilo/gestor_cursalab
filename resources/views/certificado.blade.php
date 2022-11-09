<?php
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
    <script src="{{ asset('js/canvas/html2canvas.min.js') }}"></script>
</head>
<body>
<?php
setlocale(LC_TIME, 'es_PE.UTF-8');
?>
<input type="text" id="curso_nombre" style="display:none;" value="{{$data['video']}}">

<div class="container" id="certi">
    {{-- <img id="bg" src="{{ Storage::url($data['plantilla']) }}"> --}}
    <img id="bg" src="{{ $data['image'] }}">
    <span class="nombre">{{ $data['usuario'] }}</span>
    <span class="curso">{{ $data['video'] }}</span>
    @if($data['show_certification_date'])
        <span class="fecha">{{ fechaCastellano($data['fecha']) }}</span>
    @endif
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/1.3.8/FileSaver.min.js"></script>
<script>
    function screenshot() {
        const curso_nombre = document.getElementById('curso_nombre').value.replace(/\s/g, '_').toLowerCase();
        html2canvas(document.body, {
            allowTaint: true,
            useCORS: true,
            logging: true,
        }).then(function (canvas) {
            canvas.toBlob(function (blob) {
                saveAs(blob, "diploma_" + curso_nombre + ".png");
            });
        });
    }

    window.onload = function () {
        // var image = document.getElementById('bg').src;
        // var timestamp = new Date().getTime();
        // var imageWithTimestamp = image.includes('?') ? `${image}&v=${timestamp}` : `${image}?v=${timestamp}`;

        // document.getElementById('bg').src = imageWithTimestamp

        setTimeout(function () {
            screenshot();
        }, 1000);
    };
</script>
</body>
</html>
