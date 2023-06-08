<template>
    <!-- Resumen del reporte -->
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el reporte de los checklist en resumen general.
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
            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />

            <div v-show="workspaceId === 25">
                <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
                <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            </div>
            <list-item titulo="Documento (entrenador)" subtitulo="" />
            <list-item titulo="Nombre (entrenador)" subtitulo="" />
            <!-- <list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
            <list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
            <list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" /> -->
            <list-item titulo="Checklist asignados" subtitulo="" />
            <list-item titulo="Checklist realizados" subtitulo="" />
            <list-item titulo="Avance total" subtitulo="" />
        </ResumenExpand>

        <form @submit.prevent="generateReport" class="row px-3">
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
                    @onBlur="fetchFiltersAreaData"
                    :maxValuesSelected="4"
                />
            </div>

            <v-divider class="col-12 mb-0 p-0"></v-divider>

            <!-- <div class="col-sm-4 mb-3">
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
            <div class="col-sm-4 mb-3">
                <b-form-text text-variant="muted">Curso</b-form-text>
                <select v-model="curso" class="form-control" :disabled="!Cursos[0]">
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in Cursos" :key="index" :value="item.id">
                        {{ item.nombre }}
                    </option>
                </select>
            </div> -->

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
                    <v-divider class="col-12 mb-0 p-0"></v-divider>
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

            <div class="col-12">
                <FiltersNotification></FiltersNotification>
            </div>

            <div class="col-sm-12 mb-3">
                <div class="col-sm-6 px-0">
                    <button
                        :disabled="modulo.length === 0"
                        type="submit"
                        class="btn btn-md btn-primary btn-block text-light">
                        <i class="fas fa-download"></i>
                        <span>Generar reporte</span>
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
import FiltersNotification from "../globals/FiltersNotification.vue";

export default {
    components: {FiltersNotification, EstadoFiltro, ResumenExpand, ListItem,FechaFiltro },
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'checklist_general',
            Escuelas: [],
            Cursos: [],
            //
            Grupos: [],
            grupo: [],
            carrera: [],
            ciclo: [],
            loadingGrupos: false,
            loadingCarreras: false,
            loadingCiclos: false,
            //
            modulo: [],
            areas:[],
            area:[]
            // escuela: "",
            // curso: "",
            //
            // cursos_libres:false,
        }
    },
    methods: {

        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportReport, type: vue.reportType})
        },
        async exportReport(reportName) {

            let UFC = this.$refs.EstadoFiltroComponent;
            let FechaFiltro = this.$refs.FechasFiltros;

            this.$emit('reportStarted', {})
            const filtersDescriptions = {
                "Módulos": this.generateNamesArray(this.modules, this.modulo),
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
                    modulos: this.modulo,
                    UsuariosActivos: UFC.UsuariosActivos,
                    UsuariosInactivos: UFC.UsuariosInactivos,
                    areas: this.area,
                    start: FechaFiltro.start,
                    end: FechaFiltro.end
                })
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_checklist_general");
                }, 500);

            } catch (ex) {
                console.log(ex.message)
            }
        },
        async fetchFiltersAreaData() {
            this.areas = [];
            this.area = [];

            if(this.modulo.length === 0) return;

            let url = `${this.$props.reportsBaseUrl}/filtros/sub-workspace/${this.modulo[0]}/criterion-values/grupo`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.areas = response.data
        }
    }
}
</script>
<style>

</style>
