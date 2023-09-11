<template>
    <DefaultDialog
        width="30vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <v-row>
                <v-col cols="12">
                    <div class="d-flex flex-column">

                        <div class="d-flex justify-content-between mb-4 align-items-center">
                            <div>
                                <p class="mb-1 font-weight-bold">
                                    General
                                </p>
                                <p class="mb-0">
                                    Muestra el estado de los participantes de la campaña.                       
                                </p>
                            </div>
                            <div>
                                <v-btn
                                    color="primary"
                                    size="lg"
                                    text
                                    @click="generateReportClick"
                                    >
                                    <v-icon>
                                        mdi-file-excel
                                    </v-icon>
                                </v-btn>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-4 align-items-center">
                            <div>
                                <p class="mb-1 font-weight-bold">
                                    Postulantes
                                </p>
                                <p class="mb-0">
                                    Muestra el estado de los participantes de la campaña.                       
                                </p>
                            </div>
                            <div>
                                <v-btn
                                    color="primary"
                                    size="lg"
                                    text
                                    @click="openLink(`/votaciones/report/${options.resource.campaign_id}/postulates`)"
                                    >
                                    <v-icon>
                                        mdi-file-excel
                                    </v-icon>
                                </v-btn>
                            </div>
                        </div>

                        <div 
                            v-show="options.resource.stage_votation != null" 
                            class="d-flex justify-content-between mb-4 align-items-center">
                            <div>
                                <p class="mb-1 font-weight-bold">
                                    Votantes
                                </p>
                                <p class="mb-0">
                                    Reporte de candidatos y sus votos.
                                </p>
                            </div>
                            <div>
                                <v-btn
                                    color="primary"
                                    size="lg"
                                    text
                                    @click="openLink(`/votaciones/report/${options.resource.campaign_id}/candidates`)"
                                    >
                                    <v-icon>
                                        mdi-file-excel
                                    </v-icon>
                                </v-btn>
                            </div>
                        </div>
                    </div>
                </v-col>

                <DefaultReportModal
                    :isOpen="openReport"
                    :report="report"
                    @onCancel="openReport = false"
                    @onConfirm="openReport = false"
                />
            </v-row>

        </template>
    </DefaultDialog>
</template>

<script>

import DefaultReportModal from '../Default/DefaultReportModal.vue';

export default {
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    components: { DefaultReportModal },
    data() {
        return {
            resource: null,

            reportData: null,
            openReport: false,
            report:{}
        }
    },
    methods: {
        onCancel() {
            let vue = this;
            vue.$emit('onCancel')
        },
        resetValidation() {

        },
        onConfirm() {
            let vue = this
            vue.$emit('onCancel');
        },
        generateReportClick() {
            const vue = this;
            const campaign = vue.options.resource.campaign; // campaña
            vue.report = { callback: vue.exportVotacionesDW, reportName: campaign.title };
            vue.openReport = true;
        },
        async exportVotacionesDW(reportName) {
            const vue = this;
            const campaign = vue.options.resource.campaign; // campaña
            const reportData = vue.reportData;

            const filtersDescriptions = {
                "Módulos" : vue.generateNamesArray(reportData.modules, campaign.modulos),
                "Campañas activas" : 'Sí',
                "Campañas inactivas" : 'Sí',
                "Campañas" : [ campaign.title ],
            }

            // Perform request to generate report

            let urlReport = `${reportData.reportsBaseUrl}/exportar/votaciones`

            try {
                let response = await axios({
                    url: urlReport, 
                    method: 'post',
                    data: {
                        workspaceId: reportData.workspaceId,
                        adminId: reportData.adminId,

                        filtersDescriptions,
                        reportName,
                        modulos: campaign.modulos,
                        CampaignsActivos: true,
                        CampaignsInactivos: true,
                        campaigns: [ campaign.id ]
                    }
                })

                if(response.statusText == "OK"){
                    setTimeout(() => {
                        vue.queryStatus("reportes", "descargar_reporte_votaciones");
                    }, 500);
                }

            } catch (ex) {
                console.log(ex.message)
            }
        },
        loadData(resource) {
        },
        loadSelects() {

        }
    },
    async mounted() {
        const vue = this
        vue.reportData = await vue.fetchDataReport(); // reportData
    }
}
</script>