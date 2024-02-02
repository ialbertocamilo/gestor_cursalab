<template>
    <v-main>
        <!-- Report summary -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el historial de cursos de los usuarios de un módulo
            </template>

            <list-item titulo="Nombre completo" subtitulo="Nombre completo del usuario que tiene criterios vacíos" />
            <list-item titulo="Documento de identidad" subtitulo="Documento de identidad del usuario que tiene criterios vacíos" />
            <list-item titulo="Criterios" subtitulo="Criterios usados para segmentación en los que se realizó la búsqueda" />
        </ResumenExpand>

        <!-- Report form -->

        <div class="row">
            <div class="col-sm-4 mb-3">
                <DefaultAutocomplete
                    dense
                    v-model="selectedModules"
                    :items="modules"
                    label="Módulo"
                    :show-select-all="false"
                    item-text="name"
                    item-value="id"
                    multiple
                    :max-values-selected="1"
                    placeholder="Seleccione los módulos"
                />
            </div>
        </div>

        <div class="row">
            <!--
            <div v-for="(criterion, index) in criteriaInSegmentation"
                 class="col-5">
                <v-checkbox
                    v-model="selectedCriteria[index]"
                    class="my-0 mb-1"
                    :key="criterion.id"
                    :label="criterion.name"
                    color="primary"
                    hide-details="false"
                />
            </div>
            -->
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
            reportType: 'users_history',
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

            const filtersDescriptions = {
                "Módulos": this.generateNamesArray(this.modules, this.selectedModules),
            }

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
                        filtersDescriptions
                    }
                })
            } catch (ex) {
                console.log(ex.message)
            }
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
            // let url = `${this.$props.reportsBaseUrl}/filtros/segmented-criteria/${this.workspaceId}`
            // try {
            //     let response = await axios({
            //         url: url,
            //         method: 'get'
            //     })
            //
            //     this.criteriaInSegmentation = response.data
            //     if (this.selectedCriteria.length === 0) {
            //         this.criteriaInSegmentation.forEach(() => {
            //             this.selectedCriteria.push(true);
            //         })
            //     }
            //
            // } catch (ex) {
            //     console.log(ex)
            // }
        }
        ,
        atLeastOneCriteriaIsSelected () {
            if (this.selectedCriteria.length === 0) return false

            return this.selectedCriteria.includes(true)
        }
    }
};
</script>


<style scoped>

</style>
