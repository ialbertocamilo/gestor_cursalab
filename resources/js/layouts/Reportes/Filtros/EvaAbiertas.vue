<template>
	<v-main>
		<!-- Resumen del reporte -->
		<ResumenExpand titulo="Resumen del reporte">
			<template v-slot:resumen>
				Descarga el detalle de las evaluaciones abiertas (preguntas para respuesta en texto)
				realizadas por los usuarios.
			</template>
			<list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
			<list-item
				titulo="Grupo sistema"
				subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
			/>
			<list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
			<list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
			<list-item titulo="Documento, Apellidos y Nombres, Género" subtitulo="Datos personales" />
			<list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
			<list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
			<list-item
				titulo="Estado"
				subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
			/>
			<list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
			<list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
			<list-item titulo="Tema" subtitulo="Tema dentro de cada curso" />
			<list-item titulo="Última sesion" subtitulo="Fecha de la última sesión en la plataforma" />
			<list-item titulo="Ciclo del curso" subtitulo="Ciclo/Ciclos al que pertenece el curso" />
			<list-item titulo="Pregunta" subtitulo="Pregunta de la evaluación abierta" />
			<list-item titulo="Respuesta" subtitulo="Respuesta del usuario" />
		</ResumenExpand>
		<!-- Formulario del reporte -->
		<form @submit.prevent="exportEvaAbiertas" class="row">
			<!-- Modulo -->
			<div class="col-lg-6 col-xl-4 mb-3">
				<b-form-text text-variant="muted">Módulo</b-form-text>
				<select v-model="modulo" class="form-control" @change="moduloChange">
					<option value>- [Todos] -</option>
					<option v-for="(item, index) in Modulos" :key="index" :value="item.id">
						{{ item.etapa }}
					</option>
				</select>
			</div>
			<!-- Escuela -->
			<div class="col-lg-6 col-xl-4 mb-3">
				<b-form-text text-variant="muted">Escuela</b-form-text>
				<select
					v-model="escuela"
					class="form-control"
					:disabled="!Escuelas[0]"
					@change="escuelaChange"
				>
					<option value>- [Todos] -</option>
					<option v-for="(item, index) in Escuelas" :key="index" :value="item.id">
						{{ item.nombre }}
					</option>
				</select>
			</div>
			<!-- Curso -->
			<div class="col-lg-6 col-xl-4 mb-3">
				<b-form-text text-variant="muted">Curso</b-form-text>
				<select v-model="curso" class="form-control" :disabled="!Cursos[0]" @change="cursoChange">
					<option value>- [Todos] -</option>
					<option v-for="(item, index) in Cursos" :key="index" :value="item.id">
						{{ item.nombre }}
					</option>
				</select>
			</div>
			<!-- Tema -->
			<div class="col-lg-6 col-xl-4 mb-3">
				<b-form-text text-variant="muted">Tema</b-form-text>
				<select v-model="tema" class="form-control" :disabled="!Temas[0]">
					<option value>- [Todos] -</option>
					<option v-for="(item, index) in Temas" :key="index" :value="item.id">
						{{ item.nombre }}
					</option>
				</select>
			</div>
			<v-divider class="col-12 mb-0 p-0"></v-divider>

			<v-divider class="col-12 m-0 p-0"></v-divider>
			<!-- Filtros secundarios -->
			<div class="col-12 d-flex">
				<!-- Filtros Checkboxs -->
				<div class="col-6 px-0">
					<UsuariosFiltro ref="UsuariosFiltroComponent" @emitir-cambio="cargarGrupos" />
					<v-divider class="col-12 m-0 p-0"></v-divider>
					<!--          Nuevos filtros         -->
					<div class="col-lg-12 col-xl-12 mb-3">
						<!-- <GruposFiltro :modulo="modulo" /> -->
						<small class="form-text text-muted">Áreas {{ ` (${Grupos.length}) ` || "" }}</small>
						<v-select
							attach
							solo
							chips
							clearable
							multiple
							hide-details="false"
							v-model="grupo"
							:menu-props="{ overflowY: true, maxHeight: '250' }"
							:items="Grupos"
							:label="modulo ? 'Selecciona uno o mas Áreas' : 'Selecciona un #Modulo'"
							:loading="loadingGrupos"
							:disabled="!Grupos[0]"
							:background-color="!Grupos[0] ? 'grey lighten-3' : 'light-blue lighten-5'"
						></v-select>
					</div>
					<CheckVariantes
						ref="checkVariantes"
						title="¿Deseas incluir los temas que han sido cambiados de estado “No Evaluable” a “Evaluable” que
			no se incluyen en el reporte normal?"
					/>
					<v-divider class="col-12 p-0 m-0"></v-divider>
					<div class="col">
						<small class="text-muted text-bold">Tipo de Escuelas</small>
						<div class="d-flex align-center p-0 mr-auto mt-2">
							<v-checkbox
								label="Escuelas Libres"
								color="primary"
								class="my-0 mr-4"
								v-model="cursos_libres"
								hide-details="false"
								:disabled="((modulo != '') && (escuela != ''))"
							/>
							<div
								tooltip="Escuelas con cursos que no se cuentan en el progreso"
								tooltip-position="top"
							>
								<v-icon class="info-icon">mdi-information-outline</v-icon>
							</div>
						</div>
					</div>
				</div>
				<div class="col-6">
					<FechaFiltro ref="FechasFiltros" />
				</div>
			</div>
			<v-divider class="col-12 mb-5 p-0"></v-divider>
			<button type="submit" class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
				<i class="fas fa-download"></i>
				<span>Descargar</span>
			</button>
		</form>
	</v-main>
</template>

<script>
import CheckVariantes from "./partials/CheckVariantes.vue";
import FechaFiltro from "./partials/FechaFiltro.vue";
import GruposFiltro from "./partials/GruposFiltro.vue";
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import UsuariosFiltro from "./partials/UsuariosFiltro.vue";
export default {
	components: {
		UsuariosFiltro,
		ResumenExpand,
		ListItem,
		FechaFiltro,
		GruposFiltro,
		CheckVariantes
	},
	props: {
		Modulos: Array,
		API_FILTROS: "",
		API_REPORTES: ""
	},
	data() {
		return {
			Escuelas: [],
			Cursos: [],
			Temas: [],
			Grupos: [],
			grupo: [],

			loadingGrupos: false,
			loadingCarreras: false,
			loadingCiclos: false,

			modulo: "",
			escuela: "",
			curso: "",
			tema: "",
			//
			cursos_libres:false,
		};
	},
	methods: {
		exportEvaAbiertas() {
			$("#pageloader").fadeIn();
			let UFC = this.$refs.UsuariosFiltroComponent;
			let FechaFiltro = this.$refs.FechasFiltros;
			let params = {
				cursos_libres : this.cursos_libres,
				modulo: this.modulo,
				escuela: this.escuela,
				curso: this.curso,
				tema: this.tema,
				grupo: this.grupo,
				UsuariosActivos: UFC.UsuariosActivos,
				UsuariosInactivos: UFC.UsuariosInactivos,
				start: FechaFiltro.start,
				end: FechaFiltro.end,
				variantes: this.$refs.checkVariantes.variantes
			};
			axios
				.post(this.API_REPORTES + "evaluaciones_abiertas", params)
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
		},
		async moduloChange() {
			this.escuela = "";
			this.curso = "";
			this.tema = "";
			this.Escuelas = "";
			this.Cursos = "";
			this.Temas = "";

			this.grupo = [];
			this.Grupos = [];
			if (!this.modulo) return false;
			var res = await axios.post(this.API_FILTROS + "cambia_modulo_carga_escuela", {
				mod: this.modulo
			});
			this.Escuelas = res.data;
			this.cargarGrupos();
		},
		async escuelaChange() {
			this.curso = "";
			this.tema = "";
			this.Cursos = "";
			this.Temas = "";
			if (!this.escuela) return false;
			this.cursos_libres =false;
			var res = await axios.post(this.API_FILTROS + "cambia_escuela_carga_curso_evaabierta", {
				esc: this.escuela,
				mod: this.modulo
			});
			this.Cursos = res.data;
		},
		async cursoChange() {
			this.tema = "";
			this.Temas = [];
			if (!this.curso) return false;
			var res = await axios.post(this.API_FILTROS + "cambia_curso_carga_tema_evaabierta", {
				cur: this.curso,
				esc: this.escuela
			});
			this.Temas = res.data;
		},
		cargarGrupos() {
			if(!this.modulo) return false;
			this.loadingGrupos = true;
			let params = {};
			this.modulo ? (params.mod = this.modulo) : "";
			let UFC = this.$refs.UsuariosFiltroComponent;
			this.Grupos = [];
			params.UsuariosActivos = UFC.UsuariosActivos;
			params.UsuariosInactivos = UFC.UsuariosInactivos;
			axios.post(this.API_FILTROS + "cargar_grupos", { params }).then((res) => {
				res.data.forEach((el) => {
					let NewJSON = {};
					NewJSON.text = el.valor;
					NewJSON.value = el.id;
					this.Grupos.push(NewJSON);
				});
				this.loadingGrupos = false;
			});
		}
	}
};
</script>

<style></style>
