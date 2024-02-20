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
                v-show="workspaceId === 25"
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />
            <div v-show="workspaceId === 25">
                <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            </div>

            <list-item v-show="workspaceId === 25"
                       titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />

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
            <list-item titulo="Resultado" subtitulo="Indica si el tema ha sido revisado" />
            <list-item titulo="Cantidad de visitas por tema" subtitulo="Total de visitas por cada tema" />
        </ResumenExpand>
        <!-- Formulario del reporte -->
        <form @submit.prevent="generateReport" class="row formu">
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
                            @onChange="moduloChange"
                            :maxValuesSelected="4"
                        />
                    </div>
                    <!-- Escuela -->
                    <div class="col-lg-6 col-xl-4 mb-3">
                         <DefaultAutocomplete
                            dense
                            v-model="escuela"
                            :items="filteredSchools"
                            :disabled="!filteredSchools[0]"
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
            <div class="col-12 row justify-content-around">
                <!-- Checkboxs -->
                <div class="col-6 pt-0">
                    <div class="form-row">
                        <div class="col-12 py-0">
                            <EstadoFiltro ref="EstadoFiltroComponent" @emitir-cambio="" />
                        </div>
                        <div class="col-12 py-0">
                            <EstadoFiltro ref="EstadoFiltroTemasComponent"
                                          title="Estado de temas"
                                          tooltip_activos="Temas activos en el reporte"
                                          tooltip_inactivos="Temas Inactivos en el reporte"
                                          @emitir-cambio="" />
                        </div>
                        <div class="col-12 pl-5 pr-0 py-0" v-if="workspaceId === 25">
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
                </div>
                <!-- Fechas -->
                <div class="col-6 pl-6">
                    <FechaFiltro ref="FechasFiltros"
                        label-start="Fecha inicial de última actualización"
                        label-end="Fecha final de última actualización"
                        />
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
            <v-divider class="col-12 mb-5 p-0"></v-divider>

            <div class="col-12">
                <FiltersNotification></FiltersNotification>
            </div>

            <div class="col-12 px-6">
                <button
                    :disabled="modulo.length === 0 || escuela.length === 0"
                    type="submit"
                    class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
                    <i class="fas fa-download"></i>
                    <span>Generar reporte</span>
                </button>
            </div>
        </form>
    </v-main>
</template>

<script>
import { mapState } from "vuex";
import CheckTemas from "./partials/CheckTemas.vue";
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";
import FechaFiltro from "./partials/FechaFiltro";
import FiltersNotification from "../globals/FiltersNotification.vue";
export default {
    components: {FiltersNotification, EstadoFiltro, FechaFiltro, ResumenExpand, ListItem, CheckTemas },
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            filteredSchools: [],
            reportType: 'temas_no_evaluables',
            schools: [],
            courses: [],
            topics: [],
            areas: [],

            modulo: [],
            escuela: [],
            curso: [],
            tema: [],
            area: [],

            loadingGrupos: false,
            loadingCarreras: false,
            loadingCiclos: false,
            //
            start: "",
            end: "",
            dateStart: true,
            tipocurso: false,
            //
            temasActivos: true,
            temasInactivos: true,
            //
            cursos_libres:false,
        };
    },
    mounted() {
        this.fetchFiltersData()
    }
    ,
    methods: {
        /**
         *
         * @returns {Promise<void>}
         */
        async fetchFiltersData () {

            // Fetch schools

            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}/${this.adminId}?grouped=0`
            let responseSchools = await axios({
                url: urlSchools,
                method: 'get'
            })

            this.schools = responseSchools.data
        },
        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportNotasTema, type: vue.reportType})
        },
        async exportNotasTema(reportName) {

            let userStatusFilter = this.$refs.EstadoFiltroComponent;
            let topicStatusFilter = this.$refs.EstadoFiltroTemasComponent;
            let fechaFiltro = this.$refs.FechasFiltros;

            this.$emit('reportStarted')
            const filtersDescriptions = {
                "Módulos": this.generateNamesArray(this.modules, this.modulo),
                "Escuelas": this.generateNamesArray(this.schools, this.escuela),
                "Cursos": this.generateNamesArray(this.courses, this.curso),
                "Temas": this.generateNamesArray(this.topics, this.tema),

                "Usuarios activos" : this.yesOrNo(userStatusFilter.UsuariosActivos),
                "Usuarios inactivos" : this.yesOrNo(userStatusFilter.UsuariosInactivos),
                "Temas activos": this.yesOrNo(topicStatusFilter.UsuariosActivos),
                "Temas inactivos": this.yesOrNo(topicStatusFilter.UsuariosInactivos),
                'Fecha inicial': this.start,
                'Fecha final': this.end,
                "Áreas" : this.generateNamesArray(this.areas, this.area),
                "Cursos libres": this.yesOrNo(this.tipocurso)
            }

            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/${this.reportType}`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        adminId: this.adminId,
                        reportName,
                        filtersDescriptions,
                        modulos: this.modulo,
                        escuelas: this.escuela,
                        cursos: this.curso,
                        temas: this.tema,
                        areas: this.area,
                        tipocurso: this.tipocurso,

                        UsuariosActivos: userStatusFilter.UsuariosActivos,
                        UsuariosInactivos: userStatusFilter.UsuariosInactivos,

                        start: fechaFiltro.start,
                        end: fechaFiltro.end,
                        activeTopics: topicStatusFilter.UsuariosActivos,
                        inactiveTopics: topicStatusFilter.UsuariosInactivos
                    }
                })
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_temas_no_eval");
                }, 500);

            } catch (ex) {
                console.log(ex.message)
            }
        },
        async moduloChange() {

            let vue = this;

            vue.escuela = [];
            vue.curso = [];
            vue.tema = [];

            let alreadyAdded = []
            vue.filteredSchools = vue.schools.filter(s => {

                if (vue.modulo.includes(s.subworkspace_id) &&
                    !alreadyAdded.includes(s.id)) {
                    alreadyAdded.push(s.id)
                    return true
                } else {
                    return false
                }
            })
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

            this.cursos_libres =false;
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
    },
    computed: {
        ...mapState(["User"])
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
