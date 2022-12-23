<template>
    <!-- Resumen del reporte -->
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el reporte de los checklist detallada por cada actividad.
            </template>
            <list-item
                titulo="Módulo"
                subtitulo="Módulo al que pertenece el usuario" />
            <list-item
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />
            <!-- Workspace: Farmacias peruanas -->
            <div v-show="workspaceId === 25">
                <list-item
                    titulo="Área"
                    subtitulo="Área al que pertenece el usuario" />
            </div>

            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="DNI, Apellidos y nombres, Género" subtitulo="Datos personales" />

            <div v-show="workspaceId === 25">
                <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
                <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            </div>

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
        <form @submit.prevent="exportReport" class="row px-4">
            <!-- Modulo -->
            <div class="col-sm-4 mb-3">
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
                @onChange="fetchFiltersAreaData"
                :maxValuesSelected="5"
                />
            </div>
            <!-- Escuela -->
            <div class="col-sm-4 mb-3">
                <DefaultAutocomplete
                dense
                v-model="escuela"
                :items="schools"
                label="Escuela"
                item-text="name"
                item-value="id"
                multiple
                :disabled="!schools[0]"
                :showSelectAll="false"
                placeholder="Seleccione las escuelas"
                @onChange="escuelaChange"
                :maxValuesSelected="5"
                />
            </div>
            <!-- Curso -->
            <div class="col-sm-4 mb-3">

                <DefaultAutocomplete
                dense
                v-model="curso"
                :items="courses"
                label="Curso"
                item-text="name"
                item-value="id"
                multiple
                :disabled="!courses[0]"
                :showSelectAll="false"
                placeholder="Seleccione los cursos"
                @onChange="cursoChange"
                />
            </div>
            <!-- CheckList -->
            <div class="col-sm-4 mb-3">
                <DefaultAutocomplete
                dense
                v-model="checklist"
                :items="Checklist"
                :disabled="!courses[0]"
                label="Checklist"
                item-text="title"
                item-value="id"
                multiple
                placeholder="Seleccione los checklists"
                />
            </div>
            <v-divider class="col-12 mb-0 p-0"></v-divider>
            <!-- Fechas -->
            <div class="col-12 d-flex">
                <!-- Filtros secundarios -->
                <div class="col-8 px-0">
                    <!-- Filtros Checkboxs -->
                    <EstadoFiltro ref="EstadoFiltroComponent" class="px-0"/>

                    <!--          Nuevos filtros         -->
                    <!--
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
                    -->
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

                   <div class="col-12 px-0" v-if="workspaceId === 25">
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
                            item-value="id"
                            item-text="name"
                            label="Selecciona una #Módulo"
                            :disabled="!modulo"
                            :items="areas"
                            :background-color="!area ? '' : 'light-blue lighten-5'">
                        </v-select>
                    </div>
                </div>
                <!--          Fechas          -->
                <div class="col-4 ml-auto">
                    <FechaFiltro ref="FechasFiltros" />
                </div>
            </div>
            <v-divider class="col-12 mb-5 p-0"></v-divider>
            <div class="col-sm-12 mb-3">
                <div class="col-sm-6 px-0">
                    <button :disabled="!checklist" type="submit" class="btn btn-md btn-primary btn-block text-light">
                        <i class="fas fa-download"></i>
                        <span>Descargar</span>
                    </button>
                </div>
            </div>
        </form>
    </v-main>
</template>
<script>

import FechaFiltro from "./partials/FechaFiltro.vue";
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";

export default {
    components: { EstadoFiltro, ResumenExpand, ListItem, FechaFiltro },
    props: {
        workspaceId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            schools: [],
            courses: [],
            areas:[],

            Checklist: [],

            loadingGrupos: false,
            loadingCarreras: false,
            loadingCiclos: false,
            //
            area:[],
            modulo: [],
            escuela: [],
            curso: [],
            checklist: []
            //
            // cursos_libres:false,
        }
    },
    mounted() {
        this.fetchFiltersData()
    },
    methods: {
        /**
         * Fetch reports' filter data
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
        },
        async exportReport() {

            this.showLoader()

            let UFC = this.$refs.EstadoFiltroComponent;
            let FechaFiltro = this.$refs.FechasFiltros;
            let params = {
                workspaceId: this.workspaceId,
                // cursos_libres : this.cursos_libres,
                //
                modulos: this.modulo,
                escuela: this.escuela,
                curso: this.curso,
                areas: this.area,
                //
                grupo: this.grupo,
                checklist: this.checklist,
                //
                UsuariosActivos: UFC.UsuariosActivos,
                UsuariosInactivos: UFC.UsuariosInactivos,
                start: FechaFiltro.start,
                end: FechaFiltro.end
            }

            let url = `${this.$props.reportsBaseUrl}/exportar/checklist_detallado`

            try {
                let response = await axios.post(url, params);

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
        async cursoChange() {

            this.checklist = [];
            if (this.curso.length === 0) {
                await this.escuelaChange();
                return false;
            }

            await this.fetchChecklist();
        },
        /**
         * Fetch checklists from specific course
         * @returns {Promise<void>}
         */
        async fetchChecklist(){

            let url = `${this.$props.reportsBaseUrl}/filtros/courses/checklist/${this.curso.join()}`
            const res = await axios.get(url);
            this.Checklist = res.data;
        },
        async fetchFiltersAreaData() {
            this.areas = [];
            this.area = [];

            if (this.modulo.length === 0) return;

            let url = `${this.$props.reportsBaseUrl}/filtros/sub-workspace/${this.modulo.join()}/criterion-values/grupo`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.areas = response.data
        }
    }
}
</script>
<style scoped>

</style>
