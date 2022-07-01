<template>
	<v-main>
		<!-- Resumen del reporte -->
		<ResumenExpand titulo="Resumen del reporte">
			<template v-slot:resumen>
				En este reporte se obtienen todos los usuarios matriculados en la plataforma.
			</template>
			<list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
			<list-item
				titulo="Grupo sistema"
				subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
			/>
			<list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
			<list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
			<list-item titulo="DNI, Apellidos y Nombres, Género" subtitulo="Datos personales" />
			<list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
			<list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
			<list-item
				titulo="Estado"
				subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
			/>
		</ResumenExpand>
		<!-- Formulario del reporte -->
		<form class="row col-6" @submit.prevent="exportUsuariosDW">
			<div class="col-12">
				<b-form-text text-variant="muted">Módulo</b-form-text>
				<select class="form-control" v-model="modulo" @change="moduloChange">
					<option value>- [Todos] -</option>
					<option v-for="(item, index) in Modulos" :key="index" :value="item.id">
						{{ item.etapa }}
					</option>
				</select>
			</div>
			<EstadoFiltro ref="EstadoFiltroComponent" @emitir-cambio="moduloChange" />
			<!-- Carrera -->
			<div class="col-12">
				<b-form-text text-variant="muted">Carrera</b-form-text>
				<v-select
					attach
					solo
					chips
					clearable
					multiple
					hide-details="false"
					:loading="loadingCarreras"
					@change="carreraChange"
					:disabled="!Carreras[0]"
					v-model="carrera"
					:items="Carreras"
					:label="!Carreras[0] ? 'Selecciona un #Módulo ' : 'Carreras'"
					:background-color="!Carreras[0] ? 'grey lighten-3' : 'light-blue lighten-5'"
				></v-select>
			</div>
			<!-- Grupos -->
			<div class="col-12">
				<b-form-text text-variant="muted">Áreas</b-form-text>
				<v-select
					attach
					solo
					chips
					clearable
					multiple
					hide-details="false"
					:loading="loadingGrupos"
					:disabled="!Grupos[0] || !carrera[0]"
					v-model="grupo"
					:items="Grupos"
					:label="!Grupos[0] || !carrera[0] ? 'Selecciona una #Carrera ' : 'Áreas'"
					:background-color="!Grupos[0] || !carrera[0] ? 'grey lighten-3' : 'light-blue lighten-5'"
				></v-select>
			</div>
			<v-divider class="col-12 p-0 mt-3"></v-divider>
			<div class="col-sm-12 mb-3 mt-4">
				<div class="col-sm-8 pl-0">
					<button type="submit" class="btn btn-md btn-primary btn-block text-light">
						<i class="fas fa-download"></i>
						<span>Descargar</span>
					</button>
				</div>
			</div>
		</form>
	</v-main>
</template>
<script>
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";
export default {
	components: { EstadoFiltro, ResumenExpand, ListItem },
	props: {
		Modulos: Array,
		API_REPORTES: "",
		API_FILTROS: ""
	},
	data() {
		return {
			Carreras: [],
			Grupos: [],
			modulo: "",
			carrera: "",
			grupo: "",
			loadingCarreras: false,
			loadingGrupos: false
		};
	},
	methods: {
		exportUsuariosDW() {
			this.showLoader()
			let UFC = this.$refs.EstadoFiltroComponent;
			let params = {
				modulo: this.modulo,
				carrera: this.carrera,
				grupo: this.grupo,
				UsuariosActivos: UFC.UsuariosActivos,
				UsuariosInactivos: UFC.UsuariosInactivos
			};
			axios
				.post(this.API_REPORTES + "usuarios", params)
				.then((res) => {
					if (!res.data.error) this.$emit("emitir-reporte", res);
					else {
						alert("Se ha encontrado el siguiente error : " + res.data.error);
						this.hideLoader()
					}
				})
				.catch((error) => {
					console.log(error);
					console.log(error.message);
					alert("Se ha encontrado el siguiente error : " + error);
					this.hideLoader()
				});
		},
		moduloChange() {
			this.carrera = [];
			this.Carreras = [];
			this.grupo = [];
			this.Grupos = [];
			if (!this.modulo) return false;
			this.loadingCarreras = true;
			let UFC = this.$refs.EstadoFiltroComponent;
			axios
				.post(this.API_FILTROS + "cambia_modulo_carga_carrera", {
					mod: this.modulo,
					UsuariosActivos: UFC.UsuariosActivos,
					UsuariosInactivos: UFC.UsuariosInactivos,
				})
				.then((res) => {
					console.log(res.data);
					res.data.forEach((el) => {
						console.log(el);
						let NewJSON = {};
						NewJSON.value = el.id;
						NewJSON.text = el.nombre;
						this.Carreras.push(NewJSON);
					});
					this.loadingCarreras = false;
				})
				.catch((err) => {
					console.log(err);
					this.loadingCarreras = false;
				});
		},
		carreraChange() {
			this.grupo = [];
			this.Grupos = [];
			if (!this.modulo) return false;
			this.loadingGrupos = true;
			let JsonCarreras = this.carrera.map((val) => val).join(",");
			let UFC = this.$refs.EstadoFiltroComponent;
			axios
				.post(this.API_FILTROS + "cambia_carrera_carga_grupo", {
					mod: this.modulo,
					carre: JsonCarreras,
					UsuariosActivos: UFC.UsuariosActivos,
					UsuariosInactivos: UFC.UsuariosInactivos,
				})
				.then((res) => {
					console.log(res);
					res.data.forEach((el) => {
						let NewJSON = {};
						NewJSON.text = el.grupo;
						NewJSON.value = el.id;
						this.Grupos.push(NewJSON);
					});
					this.loadingGrupos = false;
				})
				.catch((err) => {
					this.loadingGrupos = false;
					console.log(err);
				});
		}
	}
};
</script>

<style></style>
