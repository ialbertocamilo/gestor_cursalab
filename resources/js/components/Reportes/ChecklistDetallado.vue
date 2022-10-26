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
        <form @submit.prevent="exportReport" class="row">
            <!-- Modulo -->
            <div class="col-sm-4 mb-3">
                <b-form-text text-variant="muted">Módulo</b-form-text>
                <select class="form-control"
                        v-model="modulo">
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in modules"
                            :key="index"
                            :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <!-- Escuela -->
            <div class="col-sm-4 mb-3">
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
            <div class="col-sm-4 mb-3">
                <b-form-text text-variant="muted">Curso</b-form-text>
                <select v-model="curso"
                        class="form-control"
                        :disabled="!courses[0]"
                        @change="cursoChange()">
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in courses"
                            :key="index"
                            :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <!-- CheckList -->
            <div class="col-sm-4 mb-3">
                <b-form-text text-variant="muted">Checklist</b-form-text>
                <select v-model="checklist"
                        :disabled="!courses[0]"
                        class="form-control">
                    <option value="">- Selecciona un checklist -</option>
                    <option v-for="(item, index) in Checklist"
                            :key="index"
                            :value="item.id">
                        {{ item.title }}
                    </option>
                </select>
            </div>
            <v-divider class="col-12 mb-0 p-0"></v-divider>
            <!-- Fechas -->
            <div class="col-12 d-flex">
                <!-- Filtros secundarios -->
                <div class="col-8 px-0">
                    <!-- Filtros Checkboxs -->
                    <EstadoFiltro ref="EstadoFiltroComponent"/>

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
        workspaceId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            schools: [],
            courses: [],

            Checklist: [],


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
                modulos: this.modulo ? [+this.modulo] : [],
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
        async cursoChange() {

            this.checklist = "";
            if (!this.curso) {
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

            let url = `${this.$props.reportsBaseUrl}/filtros/courses/checklist/${this.curso}`
            const res = await axios.get(url);
            this.Checklist = res.data;
        }
    }
}
</script>
<style>
.v-label {
    display: contents !important;
}
</style>
