<template>
	<v-main>
		<!-- Resumen del reporte -->
		<ResumenExpand titulo="Resumen del reporte">
			<template v-slot:resumen>
				Descarga el registro de las versiones instaladas de la aplicación por usuario.
				<b>Solo Android.</b>
			</template>
			<list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
			<list-item
				titulo="Grupo sistema"
				subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
			/>
			<list-item titulo="Grupo" subtitulo="Grupo al que pertenece el usuario" />
			<list-item titulo="Botica" subtitulo="Botica en la que se ubica el usuario" />
			<list-item titulo="Documento, Apellidos y Nombres, Género" subtitulo="Datos personales" />
			<list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
			<list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
			<list-item
				titulo="Estado (Usuario)"
				subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
			/>
			<list-item titulo="Cargo" subtitulo="Cargo que tiene asignado el usuario" />
			<list-item
				titulo="Versión APP"
				subtitulo="Versión de la app instalada en dispositivo del usuario"
			/>
			<list-item titulo="Última sesión" subtitulo="Fecha de la última sesión en la plataforma" />
		</ResumenExpand>
		<!-- Formulario del reporte -->
		<!-- <div class="col-12">
			<p>Reporte de todas las versiones de la aplicación usadas</p>
			<small>Válido para instalaciones desde la versión 3.5.8 (Android)</small>
		</div> -->
		<!-- <v-divider class="col-12 m-0 p-0"></v-divider> -->
		<div class="col-6">
			<UsuariosFiltro ref="UsuariosFiltroComponent" />
		</div>
		<div class="px-4">
			<button
				@click="descargarVersionesUsadas"
				class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2 mt-8"
			>
				<i class="fas fa-download"></i>
				<span>Descargar</span>
			</button>
		</div>
	</v-main>
</template>

<script>
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import UsuariosFiltro from "./partials/UsuariosFiltro.vue";
export default {
	components: { UsuariosFiltro, ResumenExpand, ListItem },
	props: ["API_REPORTES"],
	methods: {
		descargarVersionesUsadas() {
			$("#pageloader").fadeIn();
			let UFC = this.$refs.UsuariosFiltroComponent;
			axios
				.post(this.API_REPORTES + "versiones_usadas", {
					UsuariosActivos: UFC.UsuariosActivos,
					UsuariosInactivos: UFC.UsuariosInactivos
				})
				.then((res) => {
					if (!res.data.error) this.$emit("emitir-reporte", res);
					else {
						alert("Se ha encontrado el siguiente error : " + res.data.error);
						$("#pageloader").fadeOut();
					}
				})
				.catch((error) => {
					console.log(error);
					console.log(error.message);
					alert("Se ha encontrado el siguiente error : " + error);
					$("#pageloader").fadeOut();
				});
		}
	}
};
</script>
<style></style>
