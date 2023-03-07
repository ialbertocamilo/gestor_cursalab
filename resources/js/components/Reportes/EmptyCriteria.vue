<template>
    <v-main>
        <!-- Report summary -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga la lista de usuarios que no cuentan con algunos de los
                valores que usan para segmentación
            </template>

            <list-item>

            </list-item>
        </ResumenExpand>

        <!-- Report form -->

        <form class="row col-md-8 col-xl-5"
              @submit.prevent="generateReport">

            <div class="col-sm-12 mb-3 mt-4">
                <div class="col-sm-8 pl-0">
                    <button type="submit" class="btn btn-md btn-primary btn-block text-light">
                        <i class="fas fa-download"></i>
                        <span>Generar reporte</span>
                    </button>
                </div>
            </div>
        </form>
    </v-main>
</template>

<script>
import ResumenExpand from "./partials/ResumenExpand.vue";
import ListItem from "./partials/ListItem.vue";

export default {
    components: { ResumenExpand, ListItem },
    props: {
        workspaceId:{ type: Number, required: true },
        adminId:{ type: Number, required: true },
        reportsBaseUrl: { type: String, required: true }
    },
    data() {
        return {
            reportType: 'empty_criteria',
        }
    },
    methods: {
        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportReport, type: vue.reportType})
        },
        async exportReport(reportName) {

            this.$emit('reportStarted', {})

            const url = `${this.reportsBaseUrl}/exportar/${this.reportType}`
            try {
                let response = await axios({
                    url: url,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        adminId: this.adminId,
                        reportName,
                        filtersDescriptions: {}
                    }
                })
            } catch (ex) {
                console.log(ex.message)
            }
        }
    },
    mounted() {

    }
};
</script>


<style scoped>

</style>
