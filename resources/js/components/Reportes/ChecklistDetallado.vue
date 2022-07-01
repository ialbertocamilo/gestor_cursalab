<template>
    <!-- Resumen del reporte -->
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el reporte de los checklist detallada por cada actividad.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />
            <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="DNI, Apellidos y nombres, Género" subtitulo="Datos personales" />
            <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
            <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
			<list-item titulo="DNI (entrenador)" subtitulo="" />
			<list-item titulo="Nombre (entrenador)" subtitulo="" />
            <list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
            <list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
			<list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
			<list-item titulo="Checklist" subtitulo="" />
			<list-item titulo="Avance de Checklist" subtitulo="" />
			<list-item titulo="Actividad" subtitulo="" />
			<list-item titulo="A quien califica" subtitulo="" />
			<list-item titulo="Estado" subtitulo="" />
        </ResumenExpand>
        <form @submit.prevent="exportNotasCurso" class="row">
			<!-- Modulo -->
			<div class="col-sm-4 mb-3">
				<b-form-text text-variant="muted">Módulo</b-form-text>
				<select v-model="modulo" class="form-control" @change="moduloChange">
					<option value>- [Todos] -</option>
					<option v-for="(item, index) in Modulos" :key="index" :value="item.id">
						{{ item.etapa }}
					</option>
				</select>
			</div>
			<!-- Escuela -->
			<div class="col-sm-4 mb-3">
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
			<div class="col-sm-4 mb-3">
				<b-form-text text-variant="muted">Curso</b-form-text>
				<select v-model="curso" class="form-control" :disabled="!Cursos[0]"  @change="cursoChange()">
					<option value>- [Todos] -</option>
					<option v-for="(item, index) in Cursos" :key="index" :value="item.id">
						{{ item.nombre }}
					</option>
				</select>
			</div>
			<!-- CheckList -->
			<div class="col-sm-4 mb-3">
				<b-form-text text-variant="muted">Checklist</b-form-text>
				<select v-model="checklist" class="form-control">
					<option value="">- Selecciona un checklist -</option>
					<option v-for="(item, index) in Checklist" :key="index" :value="item.id">
						{{ item.titulo }}
					</option>
				</select>
			</div>
			<v-divider class="col-12 mb-0 p-0"></v-divider>
			<!-- Fechas -->
			<div class="col-12 d-flex">
				<!-- Filtros secundarios -->
				<div class="col-8 px-0">
					<!-- Filtros Checkboxs -->
					<EstadoFiltro ref="EstadoFiltroComponent" @emitir-cambio="cargarGrupos" />
					<v-divider class="col-12 mb-0 p-0"></v-divider>
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
					<!-- <div class="col-12">
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
					</div> -->
				</div>
				<!--          Fechas          -->
				<div class="col-4 ml-auto">
					<FechaFiltro ref="FechasFiltros" />
				</div>
			</div>
			<v-divider class="col-12 mb-5 p-0"></v-divider>
			<button :disabled="!checklist" type="submit" class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
				<i class="fas fa-download"></i>
				<span>Descargar</span>
			</button>
		</form>
    </v-main>
</template>
<script>
import FechaFiltro from "./partials/FechaFiltro.vue";
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";
export default {
    components: { EstadoFiltro, ResumenExpand, ListItem,FechaFiltro },
    props: {
        Modulos: Array,
		API_FILTROS: "",
		API_REPORTES: ""
    },
	data() {
		return {
            Escuelas: [],
			Cursos: [],
			Checklist: [],
			//
			Grupos: [],
			grupo: [],
			carrera: [],
			ciclo: [],
			loadingGrupos: false,
			loadingCarreras: false,
			loadingCiclos: false,
            //
			modulo: "",
			escuela: "",
			curso: "",
			checklist:"",
			//
			// cursos_libres:false,
        }
    },
    methods: {
        exportNotasCurso() {
            this.showLoader()
			let UFC = this.$refs.EstadoFiltroComponent;
			let FechaFiltro = this.$refs.FechasFiltros;
			let params = {
				// cursos_libres : this.cursos_libres,
				//
				modulo: this.modulo,
				escuela: this.escuela,
				curso: this.curso,
				//
				grupo: this.grupo,
				checklist:this.checklist,
				//
				UsuariosActivos: UFC.UsuariosActivos,
				UsuariosInactivos: UFC.UsuariosInactivos,
				start: FechaFiltro.start,
				end: FechaFiltro.end,
			};
			axios
				.post(this.API_REPORTES + "reporte_checklist_detallado", params)
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
        async moduloChange() {
			this.escuela = "";
			this.curso = "";
			this.Escuelas = "";
			this.Cursos = "";
			this.Checklist = [];
			this.checklist = "";
			this.Grupos = [];
			this.grupo = [];
			if (!this.modulo) {
				await this.cambia_curso_carga_checklist();
				return false;
			};
			var res = await axios.post(this.API_FILTROS + "cambia_modulo_carga_escuela_checklist", {
				mod: this.modulo
			});
			this.Escuelas = res.data.categorias;
			this.Checklist = res.data.checklist;
			await this.cargarGrupos();
		},
		async escuelaChange() {
			this.curso = "";
			this.Cursos = "";
			this.Checklist = [];
			this.checklist = "";
			if (!this.escuela) {
				await this.moduloChange();
				return false;
			}
			this.cursos_libres =false;
			var res = await axios.post(this.API_FILTROS + "cambia_escuela_carga_curso_checklist", {
				esc: this.escuela,
				mod: this.modulo
			});
			this.Cursos = res.data.cursos;
			this.Checklist = res.data.checklist;
		},
		async cursoChange() {
			this.checklist = "";
			if(!this.curso){
				await this.escuelaChange();
				return false;
			}
			this.cambia_curso_carga_checklist();
		},
		async cambia_curso_carga_checklist(){
			const res = await axios.post(this.API_FILTROS + "cambia_curso_carga_checklist", {
				curso_id: this.curso,
			});
			this.Checklist = res.data.checklist;
		},
		async cargarGrupos() {
			if(!this.modulo) return false;
			this.loadingGrupos = true;
			let params = {};
			this.modulo ? (params.mod = this.modulo) : "";
			let UFC = this.$refs.EstadoFiltroComponent;
			this.Grupos = [];
			params.UsuariosActivos = UFC.UsuariosActivos;
			params.UsuariosInactivos = UFC.UsuariosInactivos;
			await axios.post(this.API_FILTROS + "cargar_grupos", { params }).then((res) => {
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
}
</script>
<style>
.v-label {
	display: contents !important;
}
</style>
