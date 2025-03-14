<template>
    <v-main>
        <!-- Resumen del reporte -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                En este reporte se obtiene la información base de todos los usuarios matriculados en la plataforma.
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
            <list-item titulo="Documento, Apellidos y Nombres" subtitulo="Datos personales" />

            <div v-show="workspaceId === 25">
                <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
                <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            </div>
            <list-item
                titulo="Estado"
                subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
            />
        </ResumenExpand>

        <!-- Formulario del reporte -->
        <form class="row"
              @submit.prevent="generateReport">
            <div class="col-6 px-6">
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
                    @onBlur="fetchFiltersCareerData"
                    :maxValuesSelected="4"
                />

                <div class="col-12 px-0 pt-3">
                    <div class="col-12 p-0">
                        <EstadoFiltro ref="EstadoFiltroComponent" class="px-0"
                                      @emitir-cambio="" />
                    </div>
                </div>
                <div class="row" v-if="workspaceId === 25">
                    <div class="col-12">
                        <b-form-text text-variant="muted">Carreras</b-form-text>
                        <v-select
                            attach
                            solo
                            chips
                            clearable
                            multiple
                            :show-select-all="false"
                            hide-details="false"
                            v-model="career"
                            :items="careers"
                            item-value="id"
                            item-text="name"
                            label="Selecciona un #Módulo"
                            @change="fetchFiltersAreaData"
                            :disabled="!modulo"
                            :background-color="!career ? '' : 'light-blue lighten-5'">
                        </v-select>
                    </div>
                    <div class="col-12">
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
                            label="Selecciona una #Carrera"
                            :disabled="!career.length"
                            :items="areas"
                            :background-color="!area ? '' : 'light-blue lighten-5'">
                        </v-select>
                    </div>
                </div>
            </div>
            <v-divider class="col-12 p-0 mt-3"></v-divider>
            <div class="col-12">
                <FiltersNotification></FiltersNotification>
            </div>
            <div class="col-sm-12 my-0">
                <div class="col-sm-6 pl-2">
                    <button type="submit"
                            :disabled="modulo.length === 0"
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

import ListItem from "./partials/ListItem.vue"
import ResumenExpand from "./partials/ResumenExpand.vue"
import EstadoFiltro from "./partials/EstadoFiltro.vue"
import FiltersNotification from "../globals/FiltersNotification.vue";

export default {
    components: {FiltersNotification, EstadoFiltro, ResumenExpand, ListItem },
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        reportsBaseUrl: '',
        platform:'',
    },
    data() {
        return {
            reportType: 'usuarios',
            careers:[],
            areas:[],

            career:[],
            area:[],
            modulo: []
        };
    },
    methods: {

        generateReport() {
            const vue = this
            vue.$emit('generateReport', { callback: vue.exportUsuariosDW, type: vue.reportType})
        },
        async exportUsuariosDW(reportName) {

            let UFC = this.$refs.EstadoFiltroComponent;

            this.$emit('reportStarted', {})
            const filtersDescriptions = {
                "Módulos" : this.generateNamesArray(this.modules, this.modulo),
                "Usuarios activos" : this.yesOrNo(UFC.UsuariosActivos),
                "Usuarios inactivos" : this.yesOrNo(UFC.UsuariosInactivos),
                "Carreras" : this.generateNamesArray(this.careers, this.career),
                "Áreas" : this.generateNamesArray(this.areas, this.area),
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
                        filtersDescriptions,
                        reportName,
                        modulos: this.modulo,
                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos,
                        careers: this.career,
                        areas: this.area,
                        platform:this.platform
                    }
                })
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_usuarios");
                }, 500);

            } catch (ex) {
                console.log(ex.message)
            }
        },
        async fetchFiltersCareerData() {
            this.careers = [];
            this.career = [];

            this.areas = [];
            this.area = [];

            if(this.modulo.length === 0) return;

            let url = `${this.$props.reportsBaseUrl}/filtros/sub-workspace/${this.modulo.join()}/criterion-values/career`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.careers = response.data;
        },
        async fetchFiltersAreaData() {
            this.areas = [];
            this.area = [];

            if(this.modulo.length === 0) return;

            let url = `${this.$props.reportsBaseUrl}/filtros/sub-workspace/${this.modulo.join()}/criterion-values/grupo`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.areas = response.data;



        }
    }
};
</script>

<style></style>
