<template>
    <v-main>
        <!-- Resumen del reporte -->
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el progreso de tus procesos.
            </template>
        </ResumenExpand>

        <form @submit.prevent="generateReport" class="row">
            <div class="col-lg-6 col-xl-4 mb-3">
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
                    :maxValuesSelected="maxValuesSelected.modules"
                />
            </div>

            <!-- <div class="col-lg-6 col-xl-4 mb-3">
                <DefaultAutocomplete
                    dense
                    v-model="selectedSchools"
                    :items="filteredSchools"
                    :disabled="!filteredSchools[0]"
                    label="Escuelas"
                    item-text="name"
                    item-value="id"
                    multiple
                    placeholder="Seleccione las escuelas"
                    @onChange="schoolChange"
                    :maxValuesSelected="maxValuesSelected.schools"
                    :showSelectAll="maxValuesSelected.show_select_all"
                />
            </div> -->

            <!-- <div class="col-lg-6 col-xl-4 mb-3">
                <DefaultAutocomplete
                    dense
                    v-model="selectedCourses"
                    :items="courses"
                    :disabled="!courses[0]"
                    label="Curso"
                    item-text="name"
                    item-value="id"
                    placeholder="Seleccione los cursos"
                />
            </div> -->

            <!-- <div class="col-lg-6 col-xl-4 mb-3">
                <DefaultAutocomplete
                    dense
                    v-model="format"
                    :items="formats"
                    label="Formato"
                    item-text="description"
                    item-value="id"
                    placeholder="Formato"
                />
            </div> -->

            <div class="row col-sm-12 mb-3 ml-1">
                <button type="submit"
                        :disabled="selectedModules.length === 0"
                        class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
                    <i class="fas fa-download"></i>
                    <span>Generar reporte</span>
                </button>
            </div>
        </form>
    </v-main>
</template>

<script>

import ListItem from "./partials/ListItem.vue"
import ResumenExpand from "./partials/ResumenExpand.vue"
import FiltersNotification from "../globals/FiltersNotification.vue";

export default {
    components: {FiltersNotification, ResumenExpand, ListItem },
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'process_progress',
            format: 'xlsx',
            filteredSchools: [],
            schools: [],
            courses: [],
            selectedModules: [],
            selectedSchools: [],
            selectedCourses: [],
            maxValuesSelected:{
                modules:4,
                schools:10,
                show_select_all:false
            }
        };
    },
    watch: {

    },
    mounted() {
        // this.fetchFiltersData()
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
            let selectedCourses = [this.selectedCourses];

            const filtersDescriptions = {
                'Módulos': this.generateNamesArray(this.modules, this.selectedModules),
                Escuelas: this.generateNamesArray(this.schools, this.selectedSchools),
                Cursos: this.generateNamesArray(this.courses, selectedCourses),
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
                        reportName,
                        filtersDescriptions,
                        modulesIds: this.selectedModules ? this.selectedModules : [],
                        ext: this.format
                    }
                })

                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_process_progress");
                }, 500);

            } catch (ex) {
                console.log(ex.message)
            }
        }
    }
}
</script>
<style lang="scss">
.add-button {
    margin-right: 35px;
}

.group-sheet {
    padding-bottom: 40px;
}
.tabs_title .v-tabs-slider-wrapper {
    display: none !important;
}
.tabs_title .v-tab--active:before,
.tabs_title .v-tab--active:hover:before,
.tabs_title .v-tab:focus:before {
    opacity: .0 !important;
}
.bx_segment .v-window__container .v-window-item {
    margin: 0 30px;
}
.bx_segment .v-window__container .v-window__prev button.v-btn:before,
.bx_segment .v-window__container .v-window__next button.v-btn:before {
    background: none !important;
}
.bx_segment .v-window__container .v-window__prev,
.bx_segment .v-window__container .v-window__next {
    transform: initial !important;
    background: none !important;
}
.bx_segment .v-window__container .v-window__prev i.v-icon,
.bx_segment .v-window__container .v-window__next i.v-icon {
    color: #B9E0E9;
}
.bx_segment .v-window__prev button.v-btn span.v-ripple__container,
.bx_segment .v-window__next button.v-btn span.v-ripple__container {
    opacity: 0;
}
.msg_label {
    font-family: "Nunito", sans-serif;
    font-size: 12px;
    text-align: center;
    border: 1px solid #5458ea;
    display: inline-block;
    padding: 0 10px;
    border-radius: 5px;
    color: #5458ea;
    margin: 0 auto;
}
</style>

