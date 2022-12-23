<template>
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
             <template v-slot:resumen>
                 Descarga todas las diplomas de los usuarios.
             </template>
             <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
             <list-item titulo="Apellidos y nombres, DNI" subtitulo="Datos personales" />
             <list-item titulo="Estado(Usuario)" subtitulo="Estado del usuario (Activo - Inactivo)" />

             <!--list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" /-->

             <list-item titulo="Escuela" subtitulo="Nombre de la escuela" />
             <list-item titulo="Estado(escuela)" subtitulo="Estado de la escuela (Activo - Inactivo)" />
             <!--list-item titulo="DENTRO DE CURRÍCULA" subtitulo="Sí el curso esta dentro de la currícula (Sí - No)" /-->

             <list-item titulo="Tipo de curso" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
             <list-item titulo="Curso" subtitulo="Nombre del curso" />
             <list-item titulo="Estado(curso)" subtitulo="Estado de la curso (Activo - Inactivo)" />

             <list-item titulo="Fecha en la que obtuvo el diploma" subtitulo="Fecha del diploma (día-mes-año)" />
             <list-item titulo="Fecha de aceptación del usuario" subtitulo="Fecha del diploma (día-mes-año)" />
             <list-item titulo="Link ver diploma" subtitulo="Enlace para ver el diploma" />
             <list-item titulo="Link descarga diploma" subtitulo="Enlace para descargar el diploma" />
        </ResumenExpand>
        <form @submit.prevent="_exportDiplomas" class="row col-12">
        <!-- Modulo -->
            <div class="col-md-6 mb-3">
                <b-form-text text-variant="muted">Módulo</b-form-text>

                <!--v-select
                    attach
                    solo
                    chips
                    clearable
                    multiple
                    hide-details="false"
                    v-model="filters.module"
                    :items="modules"
                    item-value="id"
                    item-text="name"
                    label="- [Todos] -"
                    :background-color="!filters.module ? '' : 'light-blue lighten-5'"
                ></v-select-->

                <DefaultAutocomplete
                    v-model="filters.module"
                    :items="modules"
                    label=""
                    item-text="name"
                    item-value="id"
                    dense
                    multiple
                />

                <!--DefaultSelect
                    v-model="filters.modulo"
                    :items="modules"
                    label=""
                    item-text="name"
                    item-value="id"
                    dense
                    multiple
                    @onChange="moduloChange"
                /-->
            </div>
            <!-- Escuela -->
            <div class="col-md-6 mb-3">
                <b-form-text text-variant="muted">Escuela</b-form-text>


                 <!--DefaultAutocomplete
                    :disabled="!Escuelas[0]"
                    v-model="filters.escuela"
                    :items="Escuelas"
                    label="Seleccione una escuela"
                    item-text="nombre"
                    item-value="id"
                    dense
                    @onChange="escuelaChange"
                    multiple
                /-->
                <DefaultAutocomplete
                    :disabled="!schools[0]"
                    v-model="filters.school"
                    :items="schools"
                    label=""
                    item-text="name"
                    item-value="id"
                    dense
                    multiple
                    @onChange="schoolsChange"
                />

                <!--select
                    v-model="filters.school"
                    class="form-control"
                    :disabled="!schools[0]"
                    @change="schoolsChange"
                >
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in schools"
                            :key="index"
                            :value="item.id">
                        {{ item.name }}
                    </option>
                </select-->
                <!--DefaultAutocomplete
                    :disabled="!Escuelas[0]"
                    v-model="filters.escuela"
                    :items="Escuelas"
                    label="Seleccione una escuela"
                    item-text="nombre"
                    item-value="id"
                    dense
                    @onChange="escuelaChange"
                    multiple
                /-->
            </div>

            <!-- Curso -->
            <div class="col-md-6 mb-3">
                <b-form-text text-variant="muted">Curso</b-form-text>

                <DefaultAutocomplete
                    :disabled="!courses[0]"
                    v-model="filters.course"
                    :items="courses"
                    label=""
                    item-text="name"
                    item-value="id"
                    dense
                    multiple
                />

                <!--select
                    v-model="filters.course"
                    class="form-control"
                    :disabled="!courses[0]"
                >
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in courses"
                            :key="index"
                            :value="item.id">
                        {{ item.name }}
                    </option>
                </select-->

                <!--DefaultAutocomplete
                    :disabled="!Cursos[0]"
                    v-model="filters.curso"
                    :items="Cursos"
                    label="Seleccione un curso"
                    item-text="nombre"
                    item-value="id"
                    dense
                    multiple
                /-->
            </div>
            <div class="col-md-6 mb-3">
                <!--DefaultInputDate
                    clearable
                    dense
                    range
                    :referenceComponent="'modalDateFilter1'"
                    :options="modalDateFilter1"
                    v-model="filters.fecha"
                    label="seleccione una fecha"
                /-->

                <b-form-text text-variant="muted">Fecha de emisión:</b-form-text>
                 <DefaultInputDate
                    clearable
                    dense
                    range
                    :referenceComponent="'modalDateFilter1'"
                    :options="modalDateFilter1"
                    v-model="filters.date"
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
                        @emitir-cambio="schoolsInit"
                    />
                </div>
                <div class="col-lg-4 col-md-6">
                    <EstadoFiltro
                        ref="EstadoCursoFiltroComponent"
                        title="Cursos :"
                        tooltip_activos='Curso con el estado activo'
                        tooltip_inactivos='Curso con el estado inactivo'
                        @emitir-cambio="schoolsChange"
                    />
                </div>
            </v-row>
             <v-divider class="col-12 mb-5 p-0"></v-divider>
             <div class="col-sm-6 mb-3">
                <div class="col-sm-8 pl-0">
                    <button :disabled="!(filters.module.length)" type="submit" class="btn btn-md btn-primary btn-block text-light">
                        <i class="fas fa-download"></i>
                        <span>Descargar </span>
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
        modules:{ type: Array, required: true },
        workspaceId:{ type: Number, required: true },
        reportsBaseUrl:{ type: String, required: true }
	},
	data() {
		return {
			schools: [],
			courses: [],

			cursos_libres:false,
            filters:{
                module: [],
                school: [],
                course: [],
                date: []

                //fecha: null
                // modulo: [],
                // escuela:'',
                // curso: [],
            },
            modalDateFilter1: {
                open: false,
            },
		};
	},
	methods: {
		exportDiplomas() {
            const vue = this;
			vue.showLoader();

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
                .post(`${vue.reportsBaseUrl}/reporte_diplomas`, {
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
        _exportDiplomas() {
            const vue = this;
            vue.showLoader();

            const pushDataStates = (states) => {
                let stackTemporal = [];
                const { UsuariosActivos, UsuariosInactivos } = states;

                UsuariosActivos && stackTemporal.push(1);
                UsuariosInactivos && stackTemporal.push(0);

                return stackTemporal;
            };

            const estados_usuario = pushDataStates(vue.$refs.EstadoUsuarioFiltroComponent);
            const estados_escuela = pushDataStates(vue.$refs.EstadoEscuelaFiltroComponent);
            const estados_curso = pushDataStates(vue.$refs.EstadoCursoFiltroComponent);

            const reqPayload = {
                //data
                data : {
                    workspaceId: vue.workspaceId,
                    modules: vue.filters.module,
                    school: vue.filters.school,
                    course: vue.filters.course,
                    date: vue.filters.date,
                },
                //states
                states: {
                    estados_usuario,
                    estados_escuela,
                    estados_curso
                }
            };

            axios.post(`${vue.reportsBaseUrl}/exportar/diplomas`, reqPayload)
                .then((res) => {
                    //console.log(res);
                    if (res.data.alert) {
                        this.showAlert(res.data.alert, 'warning');
                    } else {
                        this.$emit("emitir-reporte", res);
                    }
                    this.hideLoader();

                }, (err) => {
                    console.log(err);
                    console.log(err.message);
                    alert("Se ha encontrado el siguiente err : " + err);

                    vue.hideLoader();
                })
        },
        schoolsInit() {
            const vue = this;
            const estado_escuela_filtro = vue.$refs.EstadoEscuelaFiltroComponent;

            // clean data
            vue.filters.course = [];
            vue.filters.school = [];
            vue.courses = [];
            vue.schools = [];

            const reqPayload = {
                workspaceId: vue.workspaceId,
                active : estado_escuela_filtro.UsuariosActivos, // by 1
                inactive : estado_escuela_filtro.UsuariosInactivos // by 0
            };

            axios.post(`${vue.reportsBaseUrl}/filtros/schools/states`, reqPayload).then((res) => {
                const { data } = res;
                vue.schools = data;

            },(err) => console.log(err) );
        },
        schoolsChange() {
            const vue = this;
            const estado_curso_filtro = vue.$refs.EstadoCursoFiltroComponent;

            //clean data
            vue.filters.course = [];
            vue.courses = [];

            //check schoolId
            if(!vue.filters.school.length) return;


            const reqPayload = {
                schoolIds: vue.filters.school,
                active: estado_curso_filtro.UsuariosActivos, // by 1
                inactive:  estado_curso_filtro.UsuariosInactivos // by 0
            };

            axios.post(`${vue.reportsBaseUrl}/filtros/school/courses/states`, reqPayload).then((res) => {

                const { data } = res;
                vue.courses = data;

            }, (err) => console.log(err));
        },
		async moduloChange() {
            let vue = this;

			vue.filters.escuela = "";
			vue.filters.curso = "";
			vue.Escuelas = [];
			vue.Cursos = [];

            const estado_escuela_filtro = this.$refs.EstadoEscuelaFiltroComponent;
			if (!vue.filters.modulo) return false;

            /*let res = await axios.post(`${vue.reportsBaseUrl}filtros/cambia_modulo_multiple_carga_escuela`, {
                mod: vue.filters.modulo,
                escuela_active : estado_escuela_filtro.UsuariosActivos,
                escuela_inactive : estado_escuela_filtro.UsuariosInactivos,
            });*/

            const requestPayload = {
                modulo: vue.filters.modulo,
                workspaceId: vue.workspaceId,
                escuela_active : estado_escuela_filtro.UsuariosActivos,
                escuela_inactive : estado_escuela_filtro.UsuariosInactivos,
            };
            console.log(requestPayload);

			/*let res = await axios.post(`${vue.reportsBaseUrl}/filtros/cambia_modulo_multiple_carga_escuela`, requestPayload);

            console.log(res);

            this.Escuelas = res.data;

            if(vue.Escuelas.length == 0){
                alert('No se encontrarón escuelas.');
            }*/
		},
		async escuelaChange() {
            let vue = this;
			vue.filters.curso = [];
			vue.Cursos = [];

			const estado_curso_filtro = this.$refs.EstadoCursoFiltroComponent;
			if (!vue.filters.escuela) return false;

            let res = await axios.post(`${vue.reportsBaseUrl}/cambia_escuela_multiple_carga_curso`, {
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
	},
    mounted() {
        const vue = this;
        console.log('mounted Diplomas')
        vue.schoolsInit();// schools by workpaceId
    }
};
</script>

<style scoped>

</style>
