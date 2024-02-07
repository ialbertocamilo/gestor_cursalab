<template>
    <!-- Resumen del reporte -->
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Ranking de todos los usuarios de la plataforma ordenados por puntaje obtenido
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />
            <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
            <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            <list-item titulo="Puntaje (P)" subtitulo="Puntaje total del usuario" />
            <list-item titulo="Cantidad completados (CC)" subtitulo="Cursos completados" />
            <list-item titulo="Nota promedio (NP)" subtitulo="Nota promedio de todos los cursos completados por el usuario" />
            <list-item titulo="Intentos (I)" subtitulo="Cantidad total de intentos del usuario" />
            <list-item titulo="Última Evaluación" subtitulo="Fecha de última de evaluación generada por el usuario" />
			<v-divider />
			<list-item titulo="Fórmula para calcular el puntaje" subtitulo=" P = (CC*150 + NP*100) - I*0.5" />
			<list-item titulo="Nota" subtitulo="En caso de empate, la ultima evaluación definará el orden; es decir el usuario que ha resuelto primero su última evaluación tendrá una mejor posición con respecto a los demás usuarios con el mismo puntaje" />
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
			<v-divider class="col-12 mb-0 p-0"></v-divider>
			<!-- Fechas -->
			<div class="col-12 d-flex">
				<!-- Filtros secundarios -->
				<div class="col-8 px-0">
					<!-- Filtros Checkboxs -->
				<UsuariosFiltro ref="UsuariosFiltroComponent" @emitir-cambio="cargarGrupos" />
				</div>
			</div>
			<v-divider class="col-12 mb-0 p-0"></v-divider>
            <!--          Nuevos filtros         -->
			<div class="row col-12 pb-0 pt-0">
				<div class="col-lg-4 col-xl-4 mb-3">
					<small class="form-text text-muted">Área {{ ` (${Grupos.length}) ` || "" }}</small>
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
						:label="modulo ? 'Selecciona una o más Áreas' : 'Selecciona un #Modulo'"
						:loading="loadingGrupos"
						:disabled="!Grupos[0]"
						:background-color="!Grupos[0] ? 'grey lighten-3' : 'light-blue lighten-5'"
                        @change="grupoChange"
					></v-select>
				</div>
                <div class="col-lg-4 col-xl-4 mb-3">
					<small class="form-text text-muted">Sede {{ ` (${Boticas.length}) ` || "" }}</small>
					<v-select
						attach
						solo
						chips
						clearable
						multiple
						hide-details="false"
						v-model="botica"
						:menu-props="{ overflowY: true, maxHeight: '250' }"
						:items="Boticas"
						:label="modulo ? 'Selecciona una o más Sedes' : 'Selecciona un #Área'"
						:loading="loadingBoticas"
						:disabled="!Boticas[0]"
						:background-color="!Boticas[0] ? 'grey lighten-3' : 'light-blue lighten-5'"
					></v-select>
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
import ResumenExpand from "./partials/ResumenExpand.vue";
import ListItem from "./partials/ListItem.vue";
import UsuariosFiltro from "./partials/UsuariosFiltro.vue";
export default {
    components: { UsuariosFiltro, ResumenExpand ,ListItem},
    props: {
        Modulos: Array,
		API_FILTROS: "",
		API_REPORTES: ""
    },
	data() {
		return {
            Escuelas: [],
			Cursos: [],
			Grupos: [],
			grupo: [],
			loadingGrupos: false,
			loadingBoticas: false,
			modulo: "",
            botica:[],
            Boticas:[],
        }
    },
    methods: {
        exportNotasCurso() {
            $("#pageloader").fadeIn();
			let UFC = this.$refs.UsuariosFiltroComponent;
			let params = {
				modulo: this.modulo,
				grupo: this.grupo,
                botica:this.botica,
				UsuariosActivos: UFC.UsuariosActivos,
				UsuariosInactivos: UFC.UsuariosInactivos,
			};
			axios
				.post(this.API_REPORTES + "reporte_ranking", params)
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
			this.Escuelas = "";
			this.Cursos = "";
			this.Grupos = [];
			this.grupo = [];
			this.botica = [];
			this.Boticas = [];
			if (!this.modulo) return false;
			this.cargarGrupos();
		},
		async grupoChange() {
            this.botica = [];
			this.Boticas = [];
			if (this.grupo.length===0) return false;
            let params = {
				grupo: this.grupo,
			};
			axios.post(this.API_FILTROS + "cargar_boticas",params).then((res) => {
				res.data.forEach((el) => {
					let NewJSON = {};
					NewJSON.text = el.nombre;
					NewJSON.value = el.id;
					this.Boticas.push(NewJSON);
				});
                console.log(this.Boticas);
				this.loadingBoticas = false;
			});
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
}
</script>
<style>
.v-label {
	display: contents !important;
}
.v-list-item__subtitle{
	white-space: normal !important;
}
</style>
