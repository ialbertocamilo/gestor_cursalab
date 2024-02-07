<template>
	<v-main>
		<!-- Resumen del reporte -->
		<ResumenExpand titulo="Resumen del reporte">
			<template v-slot:resumen>
				Muestra el avance de los temas <b>no evaluables</b>. No tiene notas o intentos, ya que solo
				se revisan.
			</template>
			<list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
			<list-item
				titulo="Grupo sistema"
				subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
			/>
			<list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
			<list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
			<list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />
			<list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
			<list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
			<list-item
				titulo="Estado"
				subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
			/>
			<list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
			<list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
			<list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
			<list-item titulo="Tema" subtitulo="Tema dentro de cada curso" />
			<list-item titulo="Resultado" subtitulo="Indica si el tema ha sido revisado" />
			<list-item titulo="Cantidad de visitas por tema" subtitulo="Total de visitas por cada tema" />
		</ResumenExpand>
		<!-- Formulario del reporte -->
		<form @submit.prevent="exportNotasTema" class="row formu">
			<div class="col-lg-6 col-xl-4 mb-3">
				<!-- Modulo -->
				<b-form-text text-variant="muted">Módulo</b-form-text>
				<select
					v-model="modulo"
					class="form-control"
					@change="moduloChange"
					:disabled="loadingGrupos || loadingCarreras"
				>
					<option value="">[Todos]</option>
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
					<option value>- Todos -</option>
					<option v-for="(item, index) in Temas" :key="index" :value="item.id">
						{{ item.nombre }}
					</option>
				</select>
			</div>
			<v-divider class="col-12 mb-0 p-0"></v-divider>


			<v-divider class="col-12 mb-0 p-0"></v-divider>
			<!-- Filtros secundarios -->
			<div class="col-12 row justify-content-around">
				<!-- Checkboxs -->
				<div class="col-6">
					<UsuariosFiltro ref="UsuariosFiltroComponent" @emitir-cambio="cargarGrupos" />
					<v-divider></v-divider>
					<!--          Nuevos filtros         -->
					<div class="col-lg-12 col-xl-12 mb-3">
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
					<v-divider></v-divider>
					<CheckTemas />
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
				<!--          Fechas          -->
				<div class="col-6 px-5">
					<div class="max-w-900">
						<div class="mb-5">
							<small class="form-text text-muted text-bold">Fechas inicial :</small>
							<div class="input-group">
								<input
									type="date"
									autocomplete="off"
									class="datepicker form-control"
									v-model="start"
									id="date-start"
								/>
								<b-form-datepicker
									v-model="start"
									button-only
									button-variant="light"
									locale="es-PE"
									aria-controls="date-start"
									today-button
									label-today-button="Hoy"
									reset-button
									label-reset-button="Reiniciar"
									selected-variant="danger"
								></b-form-datepicker>
							</div>
						</div>
						<div>
							<small class="form-text text-muted text-bold">Fecha final :</small>
							<div class="input-group">
								<input
									type="date"
									autocomplete="off"
									class="datepicker form-control"
									v-model="end"
									id="date-end"
								/>
								<b-form-datepicker
									v-model="end"
									button-only
									button-variant="light"
									locale="es-PE"
									aria-controls="date-end"
									today-button
									label-today-button="Hoy"
									reset-button
									label-reset-button="Reiniciar"
									selected-variant="danger"
								></b-form-datepicker>
							</div>
						</div>
					</div>
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
import { mapState } from "vuex";
import CheckTemas from "./partials/CheckTemas.vue";
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import UsuariosFiltro from "./partials/UsuariosFiltro.vue";
export default {
	components: { UsuariosFiltro, ResumenExpand, ListItem, CheckTemas },
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
			modulo: "",
			escuela: "",
			curso: "",
			tema: "",
			//
			Grupos: [],
			Carreras: [],
			Ciclos: [],
			grupo: [],
			carrera: [],
			ciclo: [],
			loadingGrupos: false,
			loadingCarreras: false,
			loadingCiclos: false,
			//
			start: "",
			end: "",
			dateStart: true,
			//
			temasActivos: true,
			temasInactivos: true,
			//
			cursos_libres:false,
		};
	},
	methods: {
		exportNotasTema() {
			$("#pageloader").fadeIn();
			let UFC = this.$refs.UsuariosFiltroComponent;
			let params = {
				cursos_libres : this.cursos_libres,
				//
				modulo: this.modulo,
				escuela: this.escuela,
				curso: this.curso,
				tema: this.tema,
				start: this.start,
				end: this.end,
				//
				grupo: this.grupo,
				carrera: this.carrera,
				ciclo: this.ciclo,
				//
				temasActivos: this.temasActivos,
				temasInactivos: this.temasInactivos,
				UsuariosActivos: UFC.UsuariosActivos,
				UsuariosInactivos: UFC.UsuariosInactivos
			};
			axios
				.post(this.API_REPORTES + "temas_noevaluables", params)
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
			this.Grupos = [];
			this.grupo = [];
			//
			if (!this.modulo) return false;
			var res = await axios.post(this.API_FILTROS + "cambia_modulo_carga_escuela", {
				mod: this.modulo
			});
			this.Escuelas = res.data;
			this.cargarGrupos();
			// this.cargarCarreras();
			// this.cargarCiclos();
		},
		// Carga los cursos
		async escuelaChange() {
			this.curso = "";
			this.tema = "";
			this.Cursos = "";
			this.Temas = "";
			if (!this.escuela) return false;
			this.cursos_libres =false;
			var res = await axios.post(this.API_FILTROS + "cambia_escuela_carga_curso", {
				esc: this.escuela,
				mod: this.modulo
			});
			this.Cursos = res.data;
		},
		// Carga los temas
		async cursoChange() {
			this.tema = "";
			this.Temas = "";
			if (!this.curso) return false;
			var res = await axios.post(this.API_FILTROS + "cambia_curso_carga_tema_noevaluable", {
				cur: this.curso,
				esc: this.escuela
			});
			console.log(res);
			this.Temas = res.data;
		},
		/**
		 *Filtros secundarios
		 */
		cargarGrupos() {
			if(!this.modulo) return false;
			this.Grupos=[];
			this.loadingGrupos = true;
			let params = {};
			this.modulo ? (params.mod = this.modulo) : "";
			let UFC = this.$refs.UsuariosFiltroComponent;
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
		},
		async cargarCarreras() {
			this.Carreras = [];
			this.loadingCarreras = true;
			let params = {};
			this.modulo ? (params.mod = this.modulo) : "";

			let res = await axios.get(this.API_FILTROS + "cargar_carreras", {
				params
			});
			res.data.forEach((el) => {
				let NewJSON = {};
				NewJSON.text = el.titulo;
				NewJSON.value = el.id;
				this.Carreras.push(NewJSON);
			});
			this.loadingCarreras = false;
		},
		async carreraChange() {
			this.ciclo = [];
			this.Ciclos = [];
			if (this.carrera.length == 0) return false;
			this.loadingCiclos = true;
			let JsonCarreras = this.carrera.map((val) => val).join(",");
			axios
				.post("cambia_carrera_carga_ciclo", {
					carre: JsonCarreras
				})
				.then((res) => {
					res.data.forEach((el) => {
						let NewJSON = {};
						NewJSON.text = el.nombre;
						NewJSON.value = el.id;
						this.Ciclos.push(NewJSON);
					});
					this.loadingCiclos = false;
				})
				.catch((err) => {
					this.loadingCiclos = false;
					console.log(err);
				});
		},
		async cargarCiclos() {
			this.Ciclos = [];
			this.loadingCiclos = true;
			let params = {};
			this.modulo ? (params.mod = this.modulo) : "";

			let JsonCarreras = this.carrera.map((val) => val).join(",");
			let res = await axios.get(this.API_FILTROS + "cargar_ciclos", {
				params
			});
			res.data.forEach((el) => {
				let NewJSON = {};
				NewJSON.text = el.titulo;
				NewJSON.value = el.id;
				this.Ciclos.push(NewJSON);
			});
			this.loadingCiclos = false;
		}
	},
	computed: {
		...mapState(["User"])
	}
};
</script>

<style scoped>
.v-label {
	display: contents !important;
}
::-webkit-calendar-picker-indicator {
	color: rgba(0, 0, 0, 0);
	opacity: 0;
}
.max-w-900 {
	max-width: 900px;
}
</style>
