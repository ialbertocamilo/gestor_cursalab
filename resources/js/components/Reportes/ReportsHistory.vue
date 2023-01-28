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

                <v-simple-table class="border">
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
                            <td>{{ item.report_type }}</td>
                            <td class="text-center">
                                <v-icon color="#5457E7">
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
                                    @click="download(item.download_url)"
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
    </div>

</template>

<script>

export default {
    props : {

    },
    data () {
        return {
            reportsBaseUrl: '',
            reports: [ ],
        }
    },
    mounted: function () {

        this.reportsBaseUrl = this.getReportsBaseUrl()
        this.fetchReports()
    },
    methods: {
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
        download(downloadUrl) {
            downloadUrl = `${this.reportsBaseUrl}/${downloadUrl}`
            console.log(downloadUrl)
        }
    }
}

</script>

<style scoped>

thead {
    overflow: hidden !important;
    border-top-left-radius: 10px !important;
    border-top-right-radius: 10px !important;
    background: #5457E7;
}



</style>
