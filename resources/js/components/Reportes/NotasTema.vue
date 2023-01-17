<template>
    <v-main>
        <!-- Resumen del reporte -->

        <ResumenExpand>
            <template v-slot:resumen>
                Descarga el progreso en los temas evaluables y calificados de los usuarios segmentados hasta el momento.
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
            <list-item titulo="Documento, Apellidos y nombres" subtitulo="Datos personales" />

            <div v-show="workspaceId === 25">
                <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
                <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            </div>

            <list-item
                titulo="Estado"
                subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
            />
            <list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
            <list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
            <list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
            <list-item titulo="Tema" subtitulo="Tema dentro de cada curso" />
            <list-item
                titulo="Intentos"
                subtitulo="Cantidad de intentos utlizados en la prueba del tema"
            />
            <list-item titulo="Nota" subtitulo="Nota actual de la evaluación (La nota más alta)" />
            <list-item
                titulo="Resultado"
                subtitulo="Resultado de cada evaluación, considerando la nota mínima aprobatoria configurada"
            />
            <list-item titulo="Reinicio por tema" subtitulo="Cantidad de reinicios realizados por tema" />
            <list-item
                titulo="Última evaluación"
                subtitulo="Fecha de la última evaluación realizada de cada tema"
            />

            <list-item
                titulo="Estado (Tema)"
                subtitulo="Representa si el tema esta activo/inactivo en la plataforma"
            />
        </ResumenExpand>

        <!-- Formulario del reporte -->
        <form @submit.prevent="exportNotasTema" class="row form">
            <div class="col-12">
                <div class="row px-3">
                    <div class="col-lg-6 col-xl-4 mb-3">
                          <DefaultAutocomplete
                            dense
                            v-model="modulo"
                            :items="modules"
                            label="Módulo"
                            item-text="name"
                            item-value="id"
                            multiple
                            :showSelectAll="false"
                            placeholder="Seleccione los módulos"
                            @onBlur="fetchFiltersAreaData"
                            :maxValuesSelected="1"
                        />
                    </div>
                    <!-- Escuela -->
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <DefaultAutocomplete
                            dense
                            v-model="escuela"
                            :items="schools"
                            :disabled="!schools[0]"
                            label="Escuelas"
                            item-text="name"
                            item-value="id"
                            multiple
                            :showSelectAll="false"
                            placeholder="Seleccione las escuelas"
                            @onChange="escuelaChange"
                            :maxValuesSelected="10"
                        />
                    </div>
                    <!-- Curso -->
                    <div class="col-lg-6 col-xl-4 mb-3">
                         <DefaultAutocomplete
                            dense
                            v-model="curso"
                            :items="courses"
                            :disabled="!courses[0]"
                            @onChange="cursoChange"
                            label="Curso"
                            item-text="name"
                            item-value="id"
                            multiple
                            :showSelectAll="false"
                            placeholder="Seleccione los cursos"
                        />
                    </div>
                    <!-- Tema -->
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <DefaultAutocomplete
                            dense
                            v-model="tema"
                            :items="topics"
                            :disabled="!topics[0]"
                            label="Tema"
                            item-text="name"
                            item-value="id"
                            multiple
                            :showSelectAll="false"
                            placeholder="Seleccione los temas"
                        />
                    </div>
                </div>
            </div>

            <v-divider class="col-12 mb-0 p-0"></v-divider>
            <!-- Filtros secundarios -->
            <div class="col-6 pb-0 d-flex">
                <div class="form-row">
                    <div class="col-12 py-0">
                        <EstadoFiltro ref="EstadoFiltroComponent"
                                      @emitir-cambio="" />
                    </div>

                    <div class="col-12 py-0">
                        <EstadoFiltro ref="EstadoFiltroTemasComponent"
                                      title="Estado de temas"
                                      tooltip_activos="Temas activos en el reporte"
                                      tooltip_inactivos="Temas Inactivos en el reporte"
                                      @emitir-cambio="" />
                    </div>

                    <div class="col-12 px-3 py-0" v-if="workspaceId === 25">
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
                <!--CheckValidar ref="checkValidacion" /-->
            </div>

            <!-- Fechas -->
            <div class="col-6">
                <FechaFiltro ref="FechasFiltros"
                    label-start="Fecha inicial de última actualización"
                    label-end="Fecha final de última actualización" />
            </div>

            <div class="col-12 pt-0">
                <div class="col">
                    <small class="text-muted text-bold">Resultado del Tema :</small>
                </div>
                <div class="form-row px-3">
                    <div class="col-3 mr-auto d-flex align-center">
                        <v-checkbox
                            class="my-0 mr-2"
                            label="Revisados"
                            color="success"
                            v-model="revisados"
                            hide-details="false"
                        />
                        <div
                            tooltip="Resultados de temas del curso revisados."
                            tooltip-position="top"
                        >
                            <v-icon class="info-icon">mdi-information-outline</v-icon>
                        </div>
                    </div>
                    <div class="col-3 mr-auto d-flex align-center">
                        <v-checkbox
                            class="my-0 mr-2"
                            label="Aprobados"
                            color="success"
                            v-model="aprobados"
                            hide-details="false"
                        />
                        <div
                            tooltip="Resultados promedio de temas del curso iguales o superiores a la nota mínima aprobatoria asignada al curso."
                            tooltip-position="top"
                        >
                            <v-icon class="info-icon">mdi-information-outline</v-icon>
                        </div>
                    </div>
                    <div class="col-3 mr-auto d-flex align-center">
                        <v-checkbox
                            class="my-0 mr-2"
                            label="Desaprobados"
                            color="primary"
                            v-model="desaprobados"
                            hide-details="false"
                        />
                        <div tooltip="Resultados de temas del curso desaprobados."
                             tooltip-position="top"
                        >
                            <v-icon class="info-icon">mdi-information-outline</v-icon>
                        </div>
                    </div>
                    <div class="col-3 mr-auto d-flex align-center">
                        <v-checkbox
                            class="my-0 mr-2"
                            label="Realizados"
                            color="red"
                            v-model="realizados"
                            hide-details="false"
                        />
                        <div tooltip="Resultados de temas del curso realizados."
                             tooltip-position="top"
                        >
                            <v-icon class="info-icon">mdi-information-outline</v-icon>
                        </div>
                    </div>
                    <div class="col-3 mr-auto d-flex align-center">
                        <v-checkbox
                            class="my-0 mr-2"
                            label="Por iniciar"
                            color="warning"
                            v-model="porIniciar"
                            hide-details="false"
                        />
                        <div  tooltip="Resultados de temas del curso por iniciar."
                              tooltip-position="top"
                        >
                            <v-icon class="info-icon">mdi-information-outline</v-icon>
                        </div>
                    </div>
                </div>
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

            <v-divider class="col-12 mb-4"></v-divider>

          <div class="col-12 px-6">
                <button
                    :disabled="modulo.length === 0 || escuela.length === 0"
                    type="submit"
                    class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
                    <i class="fas fa-download"></i>
                    <span>Descargar</span>
                </button>
            </div>
        </form>
    </v-main>
</template>

<script>
import CheckTemas from "./partials/CheckTemas.vue";
import CheckValidar from "./partials/CheckValidar.vue";
import CheckVariantes from "./partials/CheckVariantes.vue";
import FechaFiltro from "./partials/FechaFiltro.vue";
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";

export default {
    components: {
        EstadoFiltro,
        ResumenExpand,
        ListItem,
        CheckValidar,
        CheckVariantes,
        CheckTemas,
        FechaFiltro
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
            areas:[],

            modulo: [],
            escuela: [],
            curso: [],
            tema: [],
            area: [],

            //
            revisados: true,
            aprobados: true,
            tipocurso: false,
            desaprobados: true,
            realizados: true,
            porIniciar: true
        };
    },
    mounted() {
        this.fetchFiltersData()
    }
    ,
    methods: {
        /**
         * Fetch schools
         * @returns {Promise<void>}
         */
        async fetchFiltersData () {

            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}`
            let responseSchools = await axios({
                url: urlSchools,
                method: 'get'
            })

            this.schools = responseSchools.data
        }
        ,
        async exportNotasTema() {

            // show loading spinner

            this.showLoader()

            let UFC = this.$refs.EstadoFiltroComponent;
            let TEMAS = this.$refs.EstadoFiltroTemasComponent;
            let DATES = this.$refs.FechasFiltros;

            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/consolidado_temas_v3`
            // let urlReport = `${this.$props.reportsBaseUrl}/exportar/consolidado_temas`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        modulos: this.modulo,
                        escuelas: this.escuela,
                        cursos: this.curso,
                        temas: this.tema,

                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos,
                        activeTopics: TEMAS.UsuariosActivos,
                        inactiveTopics: TEMAS.UsuariosInactivos,

                        tipocurso: this.tipocurso,

                        start: DATES.start,
                        end: DATES.end,
                        areas: this.area,

                        revisados: this.revisados,
                        aprobados: this.aprobados,
                        desaprobados: this.desaprobados,
                        realizados : this.realizados,
                        porIniciar: this.porIniciar
                    }
                })

                // When there are no results notify user,
                // download report otherwise

                if (response.data.alert) {
                    this.showAlert(response.data.alert, 'warning')
                } else {
                    // Emit event to parent component
                    response.data.new_name = this.generateFilename(
                        'Notas Tema',
                        this.generateNamesString(this.modules, this.modulo)
                    )
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
            this.curso = [];
            this.tema = [];
            this.courses = [];
            this.topics = [];

            if (this.escuela.length === 0) return false;

            this.cursos_libres = false;
            let url = `${this.$props.reportsBaseUrl}/filtros/courses/${this.escuela.join()}`
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
            this.tema = [];
            this.topics = [];
            if (this.curso.length === 0) return false;

            let url = `${this.$props.reportsBaseUrl}/filtros/topics/${this.curso.join()}`
            let res = await axios({
                url,
                method: 'get'
            });
            this.topics = res.data;
        },
        async fetchFiltersAreaData() {
            if (this.modulo.length === 0) {
                this.areas = [];
                return;
            }

            let url = `${this.$props.reportsBaseUrl}/filtros/sub-workspace/${this.modulo.join()}/criterion-values/grupo`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.areas = response.data
        }
    }
};
</script>

<style scoped>

::-webkit-calendar-picker-indicator {
    color: rgba(0, 0, 0, 0);
    opacity: 0;
}
.max-w-900 {
    max-width: 900px;
}
</style>
