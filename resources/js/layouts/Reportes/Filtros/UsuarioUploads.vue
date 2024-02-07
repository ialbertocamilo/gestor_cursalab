<template>
	<v-main>

		<ResumenExpand :dialog="infoDialog" @onCancel="infoDialog = false" titulo="Resumen del reporte">
			<template v-slot:resumen>
				Descarga los archivos o links subidos por los usuarios a la plataforma.
			</template>
			<list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
			<list-item
				titulo="Grupo sistema"
				subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
			/>
			<list-item titulo="Grupo" subtitulo="Grupo al que pertenece el usuario" />
			<list-item titulo="Botica" subtitulo="Botica en la que se ubica el usuario" />
			<list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />
			<list-item titulo="Carrera (Usuario)" subtitulo="Carrera actual en la que se encuentra" />
			<list-item titulo="Ciclo actual (Usuario)" subtitulo="Ciclo actual en la que se encuentra" />
			<list-item titulo="Cargo" subtitulo="Cargo que tiene asignado el usuario" />
			<list-item titulo="Link" subtitulo="Link adjuntado por el usuario" />
			<list-item titulo="Archivo" subtitulo="Archivo subido por el usuario" />
			<list-item titulo="Descripción" subtitulo="Descripción adjunta por el usuario" />
			<list-item titulo="Fecha de carga" subtitulo="Fecha en la que se adjunto el archivo" />
		</ResumenExpand>

		<v-divider class="col-12 m-0 p-0"></v-divider>
		<div class="col-6">
			<UsuariosFiltro ref="UsuariosFiltroComponent" />
		</div>
		<div class="px-4">
			<button
				@click="descargarUsuarioUploads"
				class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2 mt-5"
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
		descargarUsuarioUploads() {
			let UFC = this.$refs.UsuariosFiltroComponent;
			axios
				.post(this.API_REPORTES + "usuario_uploads", {
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
	},
	data() {
		return {
			infoDialog: false,
		};
	},
};
</script>
<style></style>
