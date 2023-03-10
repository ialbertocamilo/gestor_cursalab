<template>
    <v-main>
        <!-- Report summary -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga la lista de usuarios que no cuentan con algunos de los
                valores que usan para segmentación en cursos.
            </template>

            <list-item>

            </list-item>
        </ResumenExpand>

        <!-- Report form -->

        <div class="row">
            <div class="col-sm-4 mb-3">
                <DefaultAutocomplete
                    dense
                    v-model="selectedModules"
                    :items="modules"
                    label="Módulo"
                    item-text="name"
                    item-value="id"
                    multiple
                    :showSelectAll="false"
                    placeholder="Seleccione los módulos"
                    :maxValuesSelected="1"
                />
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <v-checkbox
                    v-for="(criterion, index) in criteriaInSegmentation"
                    v-model="selectedCriteria[index]"
                    class="my-0 mb-3"
                    :key="criterion.id"
                    :label="criterion.name"
                    color="primary"
                    hide-details="false"
                />
            </div>
        </div>
        <form class="row col-md-8 col-xl-5"
              @submit.prevent="generateReport">

            <div class="col-sm-12 mb-3 mt-4">
                <div class="col-sm-8 pl-0">
                    <button
                        :disabled="selectedModules.length === 0"
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
import ResumenExpand from "./partials/ResumenExpand.vue";
import ListItem from "./partials/ListItem.vue";

export default {
    components: { ResumenExpand, ListItem },
    props: {
        workspaceId:{ type: Number, required: true },
        adminId:{ type: Number, required: true },
        reportsBaseUrl: { type: String, required: true },
        modules: Array,
    },
    data() {
        return {
            reportType: 'empty_criteria',
            criteriaInSegmentation: [],
            selectedCriteria: [],
            selectedModules: [],
        }
    },
    mounted() {
        this.fetchFilters()
    }
    ,
    methods: {
        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportReport, type: vue.reportType})
        }
        ,
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
                        modules: this.selectedModules,
                        filtersDescriptions: this.generateFiltersDescription(),
                        selectedCriteria: this.getSelectedFilters()
                    }
                })
            } catch (ex) {
                console.log(ex.message)
            }
        }
        ,
        generateFiltersDescription () {
            let descriptions = {}
            for(let i = 0; i < this.criteriaInSegmentation.length; i++) {
                let criterion = this.criteriaInSegmentation[i];
                if (this.selectedCriteria[i]) {
                    descriptions[criterion.name] = this.yesOrNo(true)
                }
            }
            return descriptions
        }
        ,
        getSelectedFilters () {
            let selectedFilters = []
            for (let i = 0; i < this.criteriaInSegmentation.length; i++) {
                let criterion = this.criteriaInSegmentation[i];
                if (this.selectedCriteria[i]) {
                    selectedFilters.push(criterion.id)
                }
            }
            return selectedFilters
        }
        ,
        async fetchFilters () {
            let url = `${this.$props.reportsBaseUrl}/filtros/segmented-criteria/${this.workspaceId}`
            try {
                let response = await axios({
                    url: url,
                    method: 'get'
                })

                this.criteriaInSegmentation = response.data
            } catch (ex) {
                console.log(ex)
            }

        }
    }
};
</script>


<style scoped>

</style>
