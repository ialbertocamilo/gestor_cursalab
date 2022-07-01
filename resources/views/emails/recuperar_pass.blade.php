<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<title>CursaLab</title>
	<style>
	@import url('https://fonts.googleapis.com/css?family=Roboto');
	</style>
</head>
<body style="background-color: #f1f1f1;padding: 15px;">
	<div style="width: 600px;margin: 0 auto;">
		<div style="font-family: 'Roboto', sans-serif;margin-top:20px;">
			<h3>Recuperar Contraseña</h3>
			<p>Hemos generado una nueva contraseña para tu cuenta.</p>
			<div style="font-family: 'Roboto', sans-serif;margin-top:20px;background-color: #1d253d;padding: 1em;text-align: center;color:white;">
				<p style="font-family: 'Roboto', sans-serif;font-size: 1.2em;">{{ $random }}</p>
			</div>
			<p>Úsalo en tu próximo inicio de sesión</p>
		</div>

		<div style="font-family: 'Roboto', sans-serif;margin-top:20px;">
			<small>Mensaje enviado desde CursaLab</small>
		</div>
	</div>
</body>
</html>