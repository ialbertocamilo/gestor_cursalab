<template>
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
             <template v-slot:resumen>
                 Descarga todas las diplomas de los usuarios.
             </template>
             <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
             <list-item titulo="Apellidos y nombres, DNI" subtitulo="Datos personales" />
             <list-item titulo="Estado(Usuario)" subtitulo="Estado del usuario (Activo - Inactivo)" />
             <list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
             <list-item titulo="ESCUELA" subtitulo="Nombre de la escuela" />
             <list-item titulo="Estado(escuela)" subtitulo="Estado de la escuela (Activo - Inactivo)" />
             <list-item titulo="DENTRO DE CURRÍCULA" subtitulo="Sí el curso esta dentro de la currícula (Sí - No)" />
             <list-item titulo="CURSO" subtitulo="Nombre del curso" />
             <list-item titulo="Estado(curso)" subtitulo="Estado de la curso (Activo - Inactivo)" />
             <list-item titulo="FECHA EN LA QUE OBTUVO EL DIPLOMA" subtitulo="Fecha del diploma (día-mes-año)" />
             <list-item titulo="LINK VER DIPLOMA" subtitulo="Enlace para ver el diploma" />
             <list-item titulo="LINK DESCARGA DIPLOMA" subtitulo="Enlace para descargar el diploma" />
        </ResumenExpand>
         <form @submit.prevent="exportDiplomas" class="row col-12">
                 <!-- Modulo -->
                 <div class="col-md-6 mb-3">
                     <b-form-text text-variant="muted">Módulo</b-form-text>
                     <DefaultSelect
                        v-model="filters.modulo"
                        :items="Modulos"
                        label=""
                        item-text="etapa"
                        item-value="id"
                        dense
                        @onChange="moduloChange"
                        multiple
                    />
                 </div>
                 <!-- Escuela -->
                 <div class="col-md-6 mb-3">
                     <b-form-text text-variant="muted">Escuela</b-form-text>
                     <DefaultAutocomplete
                      :disabled="!Escuelas[0]"
                        v-model="filters.escuela"
                        :items="Escuelas"
                        label=""
                        item-text="nombre"
                        item-value="id"
                        dense
                        @onChange="escuelaChange"
                        multiple
                    />
                 </div>
                 <!-- Curso -->
                 <div class="col-md-6 mb-3">
                     <b-form-text text-variant="muted">Curso</b-form-text>
                     <DefaultAutocomplete
                      :disabled="!Cursos[0]"
                        v-model="filters.curso"
                        :items="Cursos"
                        label=""
                        item-text="nombre"
                        item-value="id"
                        dense
                        multiple
                    />
                 </div>
                <div class="col-md-6 mb-3">
                    <b-form-text text-variant="muted">Fecha de emisión:</b-form-text>
                    <DefaultInputDate
                        clearable
                        dense
                        range
                        :referenceComponent="'modalDateFilter1'"
                        :options="modalDateFilter1"
                        v-model="filters.fecha"
                        label=""
                    />  
                </div>
                <v-row>
                    <div class="col-lg-4 col-md-6">
                        <EstadoFiltro ref="EstadoUsuarioFiltroComponent"/>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <EstadoFiltro 
                            ref="EstadoEscuelaFiltroComponent" 
                            title="Escuelas :" 
                            tooltip_activos='Escuelas con el estado activo'
                            tooltip_inactivos='Escuelas con el estado inactivo'
                            @emitir-cambio="moduloChange" 
                        />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <EstadoFiltro 
                            ref="EstadoCursoFiltroComponent" 
                            title="Cursos :" 
                            tooltip_activos='Curso con el estado activo'
                            tooltip_inactivos='Curso con el estado inactivo'
                            @emitir-cambio="escuelaChange" 
                        />
                    </div>
                </v-row>
                 <v-divider class="col-12 mb-5 p-0"></v-divider>
                 <div class="col-sm-6 mb-3">
                    <div class="col-sm-8 pl-0">
                        <button :disabled="filters.modulo.length==0" type="submit" class="btn btn-md btn-primary btn-block text-light">
                            <i class="fas fa-download"></i>
                            <span>Descargar</span>
                        </button>
                    </div>
                </div>
         </form>
    </v-main>
</template>
<script>
import ResumenExpand from "./partials/ResumenExpand.vue";
import ListItem from "./partials/ListItem.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";
export default {
	components: { EstadoFiltro, ResumenExpand, ListItem },
	props: {
		Modulos: Array,
		API_FILTROS: "",
		API_REPORTES: ""
	},
	data() {
		return {
			Escuelas: [],
			Cursos: [],
			//
			cursos_libres:false,
            filters:{
                modulo:[],
                escuela:[],
                curso:[],
                fecha:null
            },
            modalDateFilter1: {
                open: false,
            },
		};
	},
	methods: {
		exportDiplomas() {
            let vue = this;
			vue.showLoader()
            let estados_usuario = [];
			const estado_usuario_filtro = vue.$refs.EstadoUsuarioFiltroComponent;
            (estado_usuario_filtro.UsuariosActivos) && estados_usuario.push(1);
            (estado_usuario_filtro.UsuariosInactivos) && estados_usuario.push(0);

            let estados_escuela = [];
			const estado_escuela_filtro = vue.$refs.EstadoEscuelaFiltroComponent;
            (estado_escuela_filtro.UsuariosActivos) && estados_escuela.push(1);
            (estado_escuela_filtro.UsuariosInactivos) && estados_escuela.push(0);

            let estados_curso = [];
			const estado_curso_filtro = vue.$refs.EstadoCursoFiltroComponent;
            (estado_curso_filtro.UsuariosActivos) && estados_curso.push(1);
            (estado_curso_filtro.UsuariosInactivos) && estados_curso.push(0);

			axios
				.post(vue.API_REPORTES + "reporte_diplomas", {
                    modulos_id:vue.filters.modulo,
                    categorias_id:vue.filters.escuela,
                    cursos_id :vue.filters.curso,
                    rango_fecha :vue.filters.fecha,
                    estados_usuario:estados_usuario,
                    estados_escuela:estados_escuela,
                    estados_curso:estados_curso
                })
				.then((res) => {
					if (!res.data.error) vue.$emit("emitir-reporte", res);
					else {
						alert(res.data.error);
						vue.hideLoader()
					}
				})
				.catch((error) => {
					console.log(error);
					console.log(error.message);
					alert("Se ha encontrado el siguiente error : " + error);
					vue.hideLoader()
				});
		},
		async moduloChange() {
            let vue = this;
			vue.filters.escuela = "";
			vue.filters.curso = "";
			vue.Escuelas = [];
			vue.Cursos = [];
			const estado_escuela_filtro = this.$refs.EstadoEscuelaFiltroComponent;
			if (!vue.filters.modulo) return false;
			let res = await axios.post(this.API_FILTROS + "cambia_modulo_multiple_carga_escuela", {
				mod: vue.filters.modulo,
                escuela_active : estado_escuela_filtro.UsuariosActivos,
                escuela_inactive : estado_escuela_filtro.UsuariosInactivos,
			});
			this.Escuelas = res.data;
            if(vue.Escuelas.length == 0){
                alert('No se encontrarón escuelas.');
            }
		},
		async escuelaChange() {
            let vue = this;
			vue.filters.curso = [];
			vue.Cursos = [];
			const estado_curso_filtro = this.$refs.EstadoCursoFiltroComponent;
			if (!vue.filters.escuela) return false;
			let res = await axios.post(vue.API_FILTROS + "cambia_escuela_multiple_carga_curso", {
				esc: vue.filters.escuela,
				mod: vue.filters.modulo,
                curso_active : estado_curso_filtro.UsuariosActivos,
                curso_inactive : estado_curso_filtro.UsuariosInactivos,
			});

			vue.Cursos = res.data;
            if(vue.Cursos.length == 0){
                alert('No se encontrarón cursos.');
            }
		},
	}
};
</script>

<style>
.v-label {
	display: contents !important;
}
</style>
