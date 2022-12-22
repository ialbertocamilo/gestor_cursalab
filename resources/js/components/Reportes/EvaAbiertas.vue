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
            <div v-show="workspaceId === 25">
                <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            </div>
            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="DNI, Apellidos y Nombres, Género" subtitulo="Datos personales" />
            <div v-show="workspaceId === 25">
                <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
                <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            </div>
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
            <div class="col-12">
                <div class="row px-3">
                   <!-- Modulo -->
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <b-form-text text-variant="muted">Módulo</b-form-text>
                        <select v-model="modulo"
                                class="form-control"
                                @change="fetchFiltersAreaData">
                            <option value>- [Todos] -</option>
                            <option v-for="(item, index) in modules"
                                    :key="index"
                                    :value="item.id">
                                {{ item.name }}
                            </option>
                        </select>
                    </div>
                    <!-- Escuela -->
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <b-form-text text-variant="muted">Escuela</b-form-text>
                        <select
                            v-model="escuela"
                            class="form-control"
                            :disabled="!schools[0]"
                            @change="escuelaChange"
                        >
                            <option value>- [Todos] -</option>
                            <option v-for="(item, index) in schools"
                                    :key="index"
                                    :value="item.id">
                                {{ item.name }}
                            </option>
                        </select>
                    </div>
                    <!-- Curso -->
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <b-form-text text-variant="muted">Curso</b-form-text>
                        <select v-model="curso" class="form-control"
                                :disabled="!courses[0]" @change="cursoChange">
                            <option value>- [Todos] -</option>
                            <option v-for="(item, index) in courses"
                                    :key="index"
                                    :value="item.id">
                                {{ item.name }}
                            </option>
                        </select>
                    </div>
                    <!-- Tema -->
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <b-form-text text-variant="muted">Tema</b-form-text>
                        <select v-model="tema" class="form-control"
                                :disabled="!topics[0]">
                            <option value>- [Todos] -</option>
                            <option v-for="(item, index) in topics"
                                    :key="index"
                                    :value="item.id">
                                {{ item.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <v-divider class="col-12 mb-0 p-0"></v-divider>

            <!-- Filtros secundarios -->
            <!-- Filtros Checkboxs -->
            <div class="col-6 pt-0 px-0">
                <div class="col-12 py-0">
                    <EstadoFiltro ref="EstadoFiltroComponent" @emitir-cambio="" />
                </div>
                <div class="col-12 pl-5 py-0" v-if="workspaceId === 25">
                    <b-form-text text-variant="muted">Áreas</b-form-text>
                    <v-select
                        attach
                        solo
                        chips
                        clearable
                        multiple
                        :show-select-all="false"
                        hide-details="false"
                        v-model="area"
                        :items="areas"
                        item-value="id"
                        item-text="name"
                        label="Selecciona un #Módulo"
                        :disabled="!modulo"
                        :background-color="!area ? '' : 'light-blue lighten-5'">
                    </v-select>
                </div>
            </div>
            <div class="col-6">
                <FechaFiltro ref="FechasFiltros" 
                    label-start="Fecha inicial de última actualización" 
                    label-end="Fecha final de última actualización"
                />
            </div>
            <div class="col-12 py-0">
                <div class="col py-0">
                    <small class="form-text text-muted text-bold">
                        Tipo de Cursos
                    </small>
                    <div class="d-flex mt-2">
                        <v-checkbox
                            class="my-0 mr-2"
                            label="Cursos Libres"
                            color="primary"
                            v-model="tipocurso"
                            hide-details="false"
                        />
                        <div
                            tooltip="Al marcar, el reporte generado sera solo con cursos libres."
                            tooltip-position="top"
                        >
                            <v-icon class="info-icon">mdi-information-outline</v-icon>
                        </div>
                    </div>
                </div>
            </div>
            <v-divider class="col-12 mb-5 p-0"></v-divider>
            <div class="col-12 px-6">
                <button type="submit" class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
                    <i class="fas fa-download"></i>
                    <span>Descargar</span>
                </button>
            </div>
        </form>
    </v-main>
</template>

<script>

import CheckVariantes from "./partials/CheckVariantes.vue";
import FechaFiltro from "./partials/FechaFiltro.vue";
import GruposFiltro from "./partials/GruposFiltro.vue";
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";

export default {
    components: {
        EstadoFiltro,
        ResumenExpand,
        ListItem,
        FechaFiltro,
        GruposFiltro,
        CheckVariantes
    },
    props: {
        workspaceId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            schools: [],
            courses: [],
            topics: [],
            areas: [],

            modulo: "",
            escuela: "",
            curso: "",
            tema: "",
            area: [],
            tipocurso: false
        };
    },
    mounted() {
        this.fetchFiltersData()
    },
    methods: {
        /**
         *
         * @returns {Promise<void>}
         */
        async fetchFiltersData () {

            // Fetch schools

            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}`
            let responseSchools = await axios({
                url: urlSchools,
                method: 'get'
            })

            this.schools = responseSchools.data
        }
        ,
        async exportEvaAbiertas() {

            // show loading spinner

            this.showLoader()

            let UFC = this.$refs.EstadoFiltroComponent;
            let fechaFiltro = this.$refs.FechasFiltros;

            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/evaluaciones_abiertas`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        modulos: this.modulo ? [this.modulo] : [],
                        escuelas: this.escuela ? [this.escuela] : [],
                        cursos: this.curso ? [this.curso] : [],
                        temas: this.tema ? [this.tema] : [],
                        areas: this.area,
                        tipocurso: this.tipocurso,

                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos,

                        start: fechaFiltro.start,
                        end: fechaFiltro.end
                    }
                })

                // When there are no results notify user,
                // download report otherwise

                if (response.data.alert) {
                    this.showAlert(response.data.alert, 'warning')
                } else {
                    // Emit event to parent component
                    this.$emit('emitir-reporte', response)
                }

            } catch (ex) {
                console.log(ex.message)
            }

            // Hide loading spinner

            this.hideLoader()
        },
        /**
         * Fetch courses
         * @returns {Promise<boolean>}
         */
        async escuelaChange() {
            this.curso = null;
            this.tema = null;
            this.courses = [];
            this.topics = [];

            if (!this.escuela) return false;

            this.cursos_libres =false;
            let url = `${this.$props.reportsBaseUrl}/filtros/courses/${this.escuela}`
            let res = await axios({
                url,
                method: 'get'
            });
            this.courses = res.data;
        },
        /**
         * Fetch topics
         * @returns {Promise<boolean>}
         */
        async cursoChange() {
            this.tema = null;
            this.topics = [];
            if (!this.curso) return false;

            let url = `${this.$props.reportsBaseUrl}/filtros/topics/${this.curso}`
            let res = await axios({
                url,
                method: 'get'
            });
            this.topics = res.data;
        },
        async fetchFiltersAreaData() {
            if(!this.modulo) {
                this.areas = [];
                return;
            }

            let url = `${this.$props.reportsBaseUrl}/filtros/sub-workspace/${this.modulo}/criterion-values/grupo`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.areas = response.data
        }
    }
};
</script>

<style></style>
