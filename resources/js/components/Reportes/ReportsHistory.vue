<template>
    <div>
        <v-row class="pl-4 pr-4 pt-4 pb-3">
            <v-col cols="12" >
                En esta lista encontrarás todos los reportes generados de este mes,
                listos para descargar.
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
                                    Reporte
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
                                {{ item.is_ready ? 'Completado' : 'Pendiente' }}
                            </td>
                            <td class="text-center">
                                <v-icon
                                    @click="download(item.download_url, item.name)"
                                    v-if="item.is_ready"
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

    },
    data () {
        return {
            reports: [ ],
            filtersModalIsOpen: false,
            activeFilters: {}
        }
    },
    mounted: function () {

        this.fetchReports()
    },
    computed: {
        /**
         * Listen store's stare value change
         * @returns {boolean}
         */
        reportIsReady() { return this.$store.state.reportIsReady },
    }
    ,
    watch: {
        /**
         * Watch for changes in reportIsReady property
         */
        reportIsReady(isReady) {
            // Since report is ready,
            if (isReady) {
                this.fetchReports()
            }
        }
    }
    ,
    methods: {
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

                this.reports = response.data.data.data

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
