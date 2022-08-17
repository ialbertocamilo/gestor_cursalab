<?php 
function fechaCastellano ($fecha) {
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
  return $numeroDia." de ".$nombreMes." de ".$anio;
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
</head>
<body>
	<?php 
		setlocale(LC_TIME, 'es_PE.UTF-8');
	 ?>
	<div class="container" id="certi" download="canvasexport.png">
		{{-- <img src="{{ Storage::url($data['plantilla']) }}"> --}}
		<img src="{{ $data['image'] }}">
		<span class="nombre">{{ $data['usuario'] }}</span>
		<span class="curso">{{ $data['video'] }}</span>
		<!-- <span class="fecha">{{ fechaCastellano($data['fecha']) }}</span> -->
		<!-- <span class="horas">02</span> -->
	</div>
</body>
</html>