<template>
    <!-- Resumen del reporte -->
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Ranking de todos los usuarios de la plataforma ordenados por puntaje obtenido
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario"/>
            <list-item titulo="Área" subtitulo="Área al que pertenece el usuario"/>
            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario"/>
            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales"/>
            <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra"/>
            <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra"/>
            <list-item titulo="Puntaje (P)" subtitulo="Puntaje total del usuario"/>
            <list-item titulo="Cantidad completados (CC)" subtitulo="Cursos completados"/>
            <list-item titulo="Nota promedio (NP)"
                       subtitulo="Nota promedio de todos los cursos completados por el usuario"/>
            <list-item titulo="Intentos (I)" subtitulo="Cantidad total de intentos del usuario"/>
            <list-item titulo="Última Evaluación" subtitulo="Fecha de última de evaluación generada por el usuario"/>
            <v-divider/>
            <list-item titulo="Fórmula para calcular el puntaje" subtitulo=" P = (CC*150 + NP*100) - I*0.5"/>
            <list-item titulo="Nota"
                       subtitulo="En caso de empate, la ultima evaluación definará el orden; es decir el usuario que ha resuelto primero su última evaluación tendrá una mejor posición con respecto a los demás usuarios con el mismo puntaje"/>
        </ResumenExpand>
        <form @submit.prevent="generateReport" class="row">
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
                    placeholder="Seleccione un módulo"
                    @onBlur="fetchFiltersData"
                    :maxValuesSelected="4"
                />
                <!--                <b-form-text text-variant="muted">Módulo</b-form-text>-->
                <!--                <select v-model="modulo" class="form-control">-->
                <!--                    <option value>- [Todos] -</option>-->
                <!--                    <option v-for="(item, index) in modules"-->
                <!--                            :key="index"-->
                <!--                            :value="item.id">-->
                <!--                        {{ item.name }}-->
                <!--                    </option>-->
                <!--                </select>-->
            </div>
            <v-divider class="col-12 mb-0 p-0"></v-divider>
            <!-- Fechas -->
            <div class="col-12 d-flex">
                <!-- Filtros secundarios -->
                <div class="col-8 px-0">
                    <!-- Filtros Checkboxs -->
                    <EstadoFiltro
                        ref="EstadoFiltroComponent"
                        @emitir-cambio=""/>
                </div>
            </div>
            <v-divider class="col-12 mb-5 p-0"></v-divider>
            <!-- Area y sedes (Farmacias peruanas) -->
            <template v-if="workspaceId == 25">
                <div class="col-lg-6 col-xl-4 mb-3">
                    <DefaultSelect
                        dense
                        multiple
                        :show-select-all="false"
                        v-model="area"
                        :items="areas"
                        label="Área"
                        item-text="name"
                        item-value="id"
                        placeholder="Seleccione una o mas Áreas"
                        @onChange="getSedes"
                        :disabled="!modulo"
                    />
                </div>
                <div class="col-lg-6 col-xl-4 mb-3">
                    <DefaultSelect
                        dense
                        multiple
                        v-model="sede"
                        :items="sedes"
                        label="Sedes"
                        item-text="name"
                        item-value="id"
                        placeholder="Seleccione una o mas Sedes"
                        :disabled="!area || !modulo"
                    />
                </div>
                <v-divider class="col-12 mb-5 p-0"></v-divider>
            </template>
            <div class="col-12">
                <FiltersNotification></FiltersNotification>
            </div>
            <button
                :disabled="modulo.length === 0"
                type="submit"
                class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">

                <i class="fas fa-download"></i>

                <span>Generar reporte</span>

            </button>
        </form>
    </v-main>
</template>
<script>

import ResumenExpand from "./partials/ResumenExpand.vue";
import ListItem from "./partials/ListItem.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";
import FiltersNotification from "../globals/FiltersNotification.vue";

export default {
    components: {FiltersNotification, EstadoFiltro, ResumenExpand, ListItem},
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'ranking',
            areas: [],
            sedes: [],
            modulo: [],
            area: [],
            sede: []
        }
    },
    mounted() {
        // this.fetchFiltersData()
    },
    methods: {
        async fetchFiltersData() {
            // let url = `${this.$props.reportsBaseUrl}/filtros/job-positions/${this.$props.workspaceId}`
            let url = `${this.$props.reportsBaseUrl}/filtros/sub-workspace/${this.modulo}/criterion-values/grupo`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.areas = response.data
        },
        async getSedes() {
            let vue = this;

            if (this.area.length === 0) return;

            let url = `${this.$props.reportsBaseUrl}/filtros/criterion-values/botica`;

            const data = {
                parentsIds: this.area
            };

            vue.$http.post(url, data)
                .then(({data}) => {

                    this.sedes = data

                })
        },
        generateReport() {
            const vue = this
            vue.$emit('generateReport',{callback: vue.exportNotasCurso, type: vue.reportType})
        },
        async exportNotasCurso(reportName) {

            let UFC = this.$refs.EstadoFiltroComponent;

            // Perform request to generate report
            this.$emit('reportStarted')
            const filtersDescriptions = {
                "Módulos": this.generateNamesArray(this.modules, this.modulo),
                "Usuarios activos" : this.yesOrNo(UFC.UsuariosActivos),
                "Usuarios inactivos" : this.yesOrNo(UFC.UsuariosInactivos),
                "Áreas" : this.generateNamesArray(this.areas, this.area),
                "Sedes" : this.generateNamesArray(this.sedes, this.sede)
            }

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/${this.reportType}`
            try {

                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        adminId: this.adminId,
                        reportName: reportName,
                        filtersDescriptions: filtersDescriptions,
                        modulos: this.modulo ? this.modulo : [],
                        areas: this.area,
                        sedes: this.sede,
                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos
                    }
                })

            } catch (ex) {
                console.log(ex.message)
            }
        },
    }
}
</script>
<style>
/*.v-label {*/
/*    display: contents !important;*/
/*}*/
.v-list-item__subtitle {
    white-space: normal !important;
}
</style>
