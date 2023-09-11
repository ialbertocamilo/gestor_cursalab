<template>
    <v-main>
        <!-- Resumen del reporte -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                En este reporte se obtiene la información base de todos los usuarios por campaña.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item titulo="Genero" subtitulo="Genero del usuario" />
            <list-item titulo="Documento, Apellidos y Nombres" subtitulo="Datos personales" />
            <list-item
                titulo="Estado"
                subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
            />
            <list-item titulo="Posicion" subtitulo="Posicion al que pertenece el usuario" />
            <list-item titulo="Fecha de ingreso" subtitulo="Fecha de ingreso del usuario" />
            <list-item titulo="Criterio" subtitulo="Criterio de asociación" />
            <list-item titulo="Valor de asociacion" subtitulo="Valor de asociación de la campaña" />
            <list-item titulo="Campaña" subtitulo="Nombre de la campaña" />
            <list-item titulo="Etapa de contenido" subtitulo="Si el usuario realizó contenido" />
            <list-item titulo="Etapa de reconocimiento" subtitulo="Si el usuario realizó postulación" />
            <list-item titulo="Etapa de votacion" subtitulo="Si el usuario realizó votación" />
            <list-item titulo="Porcentaje de etapas" subtitulo="Porcentaje de etapas del usuario" />
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
                    @onBlur="fetchFiltersCampaigns"
                    :maxValuesSelected="4"
                />
                <br>
                <DefaultAutocomplete
                    dense
                    v-model="campaign"
                    :items="campaigns"
                    label="Campañas"
                    item-text="title"
                    item-value="id"
                    multiple
                    :showSelectAll="false"
                    placeholder="Seleccione campañas"
                    :maxValuesSelected="5"
                />

                <div class="col-12 px-0 pt-3">
                    <div class="col-12 p-0">
                        <EstadoFiltro 
                            ref="EstadoFiltroComponent" 
                            title="Estado de campañas" 
                            tooltip_activos="Campañas activas que estan en proceso."
                            tooltip_inactivos="Campañas inactivas que ya fueron realizadas."
                            class="px-0"
                            @emitir-cambio="" 
                        />
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
                            :disabled="modulo.length === 0 || campaign.length === 0"
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
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'votaciones',
            campaigns: [],
            campaign: [],

            modulo: []
        };
    },
    methods: {
        generateReport() {
            const vue = this;
            console.log('generateReport', { callback: vue.exportVotacionesDW, type: vue.reportType});
            vue.$emit('generateReport', { callback: vue.exportVotacionesDW, type: vue.reportType})
        },
        async exportVotacionesDW(reportName) {

            let UFC = this.$refs.EstadoFiltroComponent;

            this.$emit('reportStarted', {})
            const filtersDescriptions = {
                "Módulos" : this.generateNamesArray(this.modules, this.modulo),
                "Campañas activas" : this.yesOrNo(UFC.UsuariosActivos),
                "Campañas inactivas" : this.yesOrNo(UFC.UsuariosInactivos),
                "Campañas" : this.generateNamesArray(this.campaigns, this.campaign),
            }

            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/${this.reportType}`


            console.log('exportVotacionesDW', { 
                    filtersDescriptions, 
                    urlReport,
                    data: {
                            workspaceId: this.workspaceId,
                            adminId: this.adminId,
                            filtersDescriptions,
                            reportName,
                            modulos: this.modulo,
                            CampaignsActivos: UFC.UsuariosActivos,
                            CampaignsInactivos: UFC.UsuariosInactivos,
                            campaigns: this.campaign
                        }
                    } );

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
                        CampaignsActivos: UFC.UsuariosActivos,
                        CampaignsInactivos: UFC.UsuariosInactivos,
                        campaigns: this.campaign
                    }
                })
                const vue = this
                if(response.statusText == "OK"){
                    setTimeout(() => {
                        vue.queryStatus("reportes", "descargar_reporte_votaciones");
                    }, 500);
                }

            } catch (ex) {
                console.log(ex.message)
            }
        },
        async fetchFiltersCampaigns() {
            this.campaigns = [];
            this.campaign = [];

            if(this.modulo.length === 0) return;

            let url = `${this.$props.reportsBaseUrl}/filtros/sub-workspace/${this.modulo.join()}/campaigns`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.campaigns = response.data;
        }
    }
};
</script>

<style></style>
