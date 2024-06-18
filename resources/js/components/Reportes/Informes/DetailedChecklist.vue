<template>
    <!-- Resumen del reporte -->
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el informe detallado de actividades en un checklist.
            </template>

            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />
            <list-item titulo="Estado" subtitulo="Estado del usuario (Activo - Inactivo)" />
            <list-item
                titulo="Módulo"
                subtitulo="Módulo al que pertenece el usuario" />
            <div v-show="workspaceId === 25">
                <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
                <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            </div>
            <list-item
                titulo="Criterios"
                subtitulo="Listado de criterios del usuario" />
            <list-item titulo="Documento (entrenador)" subtitulo="" />
            <list-item titulo="Nombre (entrenador)" subtitulo="" />
            <list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
            <list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
            <list-item titulo="Curso(s) Asignado" subtitulo="Curso que tiene asignado el usuario y relacionado al checklist" />
            <list-item titulo="Tipo de curs" subtitulo="Curso que tiene asignado el usuario y relacionado al checklist" />
            <list-item titulo="Checklist" subtitulo="" />
            <list-item titulo="Cumplimiento del Checklist" subtitulo="Porcentaje de avance del usuario con respecto al checklist." />
            <!-- <list-item titulo="Actividad" subtitulo="" />
            <list-item titulo="A quien califica" subtitulo="" />
            <list-item titulo="Estado" subtitulo="" /> -->
        </ResumenExpand>
        <form @submit.prevent="generateReport" class="row px-4">
            <!-- Modulo -->
            <!-- <div class="col-sm-4 mb-3">
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
            </div> -->
            <!-- Escuela -->
            <!-- <div class="col-sm-4 mb-3">
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
                :maxValuesSelected="10"
                />
            </div> -->
            <!-- Curso -->
            <!-- <div class="col-sm-4 mb-3">

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
            </div> -->
            <!-- CheckList -->
            <div class="col-sm-4 mb-3">
                <!-- :disabled="!courses[0]" -->
                <DefaultAutocomplete
                dense
                v-model="checklist"
                :items="Checklist_items"
                label="Checklist"
                item-text="title"
                item-value="id"
                multiple
                :showSelectAll="false"
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
                </div>
                <!--          Fechas          -->
                <div class="col-4 ml-auto">
                    <FechaFiltro ref="FechasFiltros" />
                </div>
            </div>
            <v-divider class="col-12 mb-5 p-0"></v-divider>
            <div class="col-sm-12 mb-3">
                <div class="col-sm-6 px-0">
                    <button
                        :disabled="checklist.length === 0"
                        type="submit" class="btn btn-md btn-primary btn-block text-light">
                        <i class="fas fa-download"></i>
                        <span>Generar reporte</span>
                    </button>
                </div>
            </div>
        </form>
    </v-main>
</template>
<script>

import FechaFiltro from "../partials/FechaFiltro.vue";
import ListItem from "../partials/ListItem.vue";
import ResumenExpand from "../partials/ResumenExpand.vue";
import EstadoFiltro from "../partials/EstadoFiltro.vue";

export default {
    components: { EstadoFiltro, ResumenExpand, ListItem, FechaFiltro },
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'checklist_activities_report',
            schools: [],
            courses: [],
            areas:[],

            Checklist_items: [],

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
        // this.fetchFiltersData();
        this.fetchChecklist();
        this.setDefaultDates();
    },
    methods: {
        /**
         * Fetch reports' filter data
         * @returns {Promise<void>}
         */
         setDefaultDates() {
            const endDate = new Date(); // Fecha actual
            const startDate = new Date();
            let FechaFiltro = this.$refs.FechasFiltros;
            startDate.setMonth(startDate.getMonth() - 1); // Un mes atrás
            // Formatear fechas en YYYY-MM-DD para el datepicker
            FechaFiltro.start = startDate.toISOString().split('T')[0];
            FechaFiltro.end = endDate.toISOString().split('T')[0];
        },
        async fetchFiltersData () {

            // Fetch schools

            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}?grouped=0`
            let responseSchools = await axios({
                url: urlSchools,
                method: 'get'
            })

            this.schools = responseSchools.data
        },

        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportReport, type: vue.reportType})
        },
        async exportReport(reportName) {

            let UFC = this.$refs.EstadoFiltroComponent;
            let FechaFiltro = this.$refs.FechasFiltros;

            console.log(this.Checklist_items, this.checklist)

            this.$emit('reportStarted', {})
            const filtersDescriptions = {
                "Módulos": this.generateNamesArray(this.modules, this.modulo),
                "Escuelas": this.generateNamesArray(this.schools, this.escuela),
                "Cursos": this.generateNamesArray(this.courses, this.curso),
                "Checklist": this.generateNamesArray(this.Checklist_items, this.checklist),
                "Usuarios activos" : this.yesOrNo(UFC.UsuariosActivos),
                "Usuarios inactivos" : this.yesOrNo(UFC.UsuariosInactivos),
                'Fecha inicial': FechaFiltro.start,
                'Fecha final': FechaFiltro.end,
                "Áreas" : this.generateNamesArray(this.areas, this.area)
            }

            let url = `${this.$props.reportsBaseUrl}/exportar/${this.reportType}`
            try {

                let response = await axios.post(url, {
                    workspaceId: this.workspaceId,
                    adminId: this.adminId,
                    reportName,
                    filtersDescriptions,
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
                });
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_checklist_detallado");
                }, 500);


            } catch (ex) {
                console.log(ex.message)
            }
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

            let url = `${this.$props.reportsBaseUrl}/filtros/courses/checklist/${this.$props.workspaceId}`;
            const res = await axios.get(url);
            this.Checklist_items = res.data;
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
