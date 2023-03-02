<template>
    <div>
        <v-row class="pl-4 pr-4 pt-4 pb-3">
            <v-col cols="12" >
                En esta lista encontrarás todos los reportes generados de este mes,
                listos para descargar.
                <v-btn
                    v-if="isSuperUser"
                    color="primary"
                    class="text-white m-3"
                    @click="restartQueue">
                    Reiniciar cola de reportes
                </v-btn>
            </v-col>
        </v-row>
        <v-row class="pr-4 pl-4 pb-4">
            <v-col cols="12">

                <v-simple-table class="reports-history">
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th class="text-left text-white">
                                    Fecha
                                </th>
                                <th class="text-left text-white">
                                    Tipo
                                </th>
                                <th class="text-left text-white">
                                    Nombre
                                </th>
                                <th class="text-center text-white">
                                    Filtros usados
                                </th>
                                <th class="text-left text-white">
                                    Admin
                                </th>
                                <th class="text-left text-white">
                                    Estado
                                </th>
                                <th class="text-center text-white">
                                    Archivo
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr
                            v-for="item in reports"
                            :key="item.id"
                        >
                            <td>{{ item.created_at }}</td>
                            <td>{{ item.report_type }}</td>
                            <td>{{ item.name }}</td>
                            <td class="text-center">

                                <v-icon
                                    v-if="objectIsEmpty(item.filters_descriptions)"
                                    color="#C2C2C2">
                                    mdi-filter-menu
                                </v-icon>

                                <v-icon
                                    v-if="!objectIsEmpty(item.filters_descriptions)"
                                    @click="showModal(item.filters_descriptions)"
                                    color="#5457E7">
                                    mdi-filter-menu
                                </v-icon>
                            </td>
                            <td>
                                {{ item.admin.name + ' ' + (item.admin.lastname || '') }}
                            </td>
                            <td>
                                {{ getReportStatus(item) }}
                            </td>
                            <td class="text-center">
                                <v-icon
                                    @click="download(item.download_url, item.name)"
                                    v-if="item.is_ready && item.download_url"
                                    color="#5457E7">
                                    mdi-download
                                </v-icon>

                                <v-icon
                                    v-if="!item.is_ready"
                                    color="silver">
                                    mdi-file-clock-outline
                                </v-icon>
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>

            </v-col>
        </v-row>

        <ReportFiltersModal
            :isOpen="filtersModalIsOpen"
            :filters="activeFilters"
            @cancel="filtersModalIsOpen = false"></ReportFiltersModal>
    </div>

</template>

<script>

import ReportFiltersModal from "./ReportFiltersModal.vue";

export default {
    components: {ReportFiltersModal},
    props : {
        adminId : {
            type: Number,
            default: 0
        },
        isSuperUser: {
            tyle: Boolean,
            default: false
        },
        reportsBaseUrl: '',
        workspaceId: 0
    },
    data () {
        return {
            reports: [ ],
            filtersModalIsOpen: false,
            activeFilters: {}
        }
    },
    mounted: function () {

        const vue = this
        this.fetchReports()

        let socket = window.io(this.getReportsBaseUrl());
        socket.on('report-finished', (e) => {
            vue.fetchReports()
        })

        socket.on('report-started', (e) => {
            vue.fetchReports()
        })
    },
    methods: {
        notifyUser (message) {
            this.$toast.warning({
                component: Vue.component('comp', {
                    template: `<div>${message}</div>`
                })
            });
        },
        async restartQueue () {
            const url = `${this.reportsBaseUrl}/reports/queue/started/${this.workspaceId}`
            try {
                let response = await axios({
                    url,
                    method: 'post',
                    data: {
                        adminId: this.adminId
                    }
                })

                if (response.data.started) {
                    this.notifyUser('La cola de reportes se ha reiniciado')
                } else {
                    this.notifyUser('No hay reportes pendientes para reiniciar')
                }
                await this.fetchReports()

            } catch (ex) {
                console.log(ex)
                await this.fetchReports()
            }
        },
        /**
         * Fetch genrated reports list
         * @returns {Promise<void>}
         */
        fetchReports: async function () {

            try {

                let response = await axios({
                    url: `/reports/queue`,
                    method: 'get'
                })

                this.reports = response.data.data

            } catch (ex) {
                console.log(ex)
            }
        },
        showModal(filters) {
            this.activeFilters = filters
            this.filtersModalIsOpen = true
        },
        objectIsEmpty (obj) {
            return JSON.stringify(obj) === '{}'
        }
        ,
        download(url, name) {
            this.$root.downloadReport(url, name)
        },
        getReportStatus(report) {

            let status = ''

            if (report.failed) {
                status = 'No procesado'
            } else if (report.is_ready && report.download_url) {
                status = 'Completado'
            } else if (report.is_ready && !report.download_url) {
                status = 'Sin resultados'
            } else if (report.is_processing) {
                status = 'Procesando'
            } else {
                status = 'Pendiente'
            }

            return status
        }
    }
}

</script>

<style>

.reports-history table {
    overflow: hidden !important;
    border-top-left-radius: 10px !important;
    border-top-right-radius: 10px !important;
}


.reports-history table thead {
    background: #5457E7;
}


</style>
