<template>
    <!-- Resumen del reporte -->
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el reporte detallado de un Beneficio.
            </template>
            <list-item
                titulo="Beneficio"
                subtitulo="Nombre del beneficio"
            />
            <!-- Workspace: Farmacias peruanas -->
            <div v-show="workspaceId === 25">
                <list-item
                    titulo="Tipo de Beneficio"
                    subtitulo="" />
            </div>

            <list-item titulo="Estado" subtitulo="Estado del beneficio" />
            <list-item titulo="Estado" subtitulo="Estado del usuario (Activo - Inactivo)" />
            <list-item
                titulo="Módulo"
                subtitulo="Módulo al que pertenece el usuario" />
            <div v-show="workspaceId === 25">
                <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
                <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            </div>
            <list-item
                titulo="Criterios"
                subtitulo="Listado de criterios del usuario" />
            <list-item titulo="Documento (entrenador)" subtitulo="" />
            <list-item titulo="Nombre (entrenador)" subtitulo="" />
            <list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
            <list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
            <list-item titulo="Curso(s) Asignado" subtitulo="Curso que tiene asignado el usuario y relacionado al benefit" />
            <list-item titulo="Tipo de curs" subtitulo="Curso que tiene asignado el usuario y relacionado al benefit" />
            <list-item titulo="Beneficio" subtitulo="" />
            <list-item titulo="Cumplimiento del Beneficio" subtitulo="Porcentaje de avance del usuario con respecto al benefit." />
            <!-- <list-item titulo="Actividad" subtitulo="" />
            <list-item titulo="A quien califica" subtitulo="" />
            <list-item titulo="Estado" subtitulo="" /> -->
        </ResumenExpand>
        <form @submit.prevent="generateReport" class="row px-4">
            <!-- Benefit -->
            <div class="col-sm-6 mb-3">
                <DefaultAutocomplete
                    dense
                    v-model="benefits"
                    :items="selects.benefits"
                    label="Beneficio"
                    item-text="title"
                    item-value="id"
                    multiple
                    :showSelectAll="false"
                    placeholder="Seleccione los beneficios"
                />
            </div>
            <v-divider class="col-12 mb-0 p-0"></v-divider>
            <div class="col-sm-12 mb-3">
                <div class="col-sm-6 px-0">
                    <button
                        :disabled="benefits.length === 0"
                        type="submit" class="btn btn-md btn-primary btn-block text-light">
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
    components: { ResumenExpand,ListItem },
    props: {
        workspaceId: 0,
        adminId: 0,
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'benefit_report',
            selects:{
                benefits:[]
            },
            benefits: []
            //
            // cursos_libres:false,
        }
    },
    mounted() {
        this.fetchFiltersData();
    },
    methods: {
        /**
         * Fetch reports' filter data
         * @returns {Promise<void>}
         */
        async fetchFiltersData () {
            let vue = this;
            await vue.$http.get("/beneficios/search?all_data=true").then(({data}) => {
                vue.selects.benefits = data.data.data;
                this.hideLoader()
            })
            .catch((err) => {
                console.log(err);
                this.hideLoader()
            });
        },

        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportReport, type: vue.reportType})
        },
        async exportReport(reportName) {
            this.$emit('reportStarted', {})
            const filtersDescriptions = {
                "Beneficio": this.generateNamesArray(this.selects.benefits, this.benefits),
            }

            let url = `${this.$props.reportsBaseUrl}/exportar/${this.reportType}`
            try {

                let response = await axios.post(url, {
                    workspaceId: this.workspaceId,
                    adminId: this.adminId,
                    reportName,
                    filtersDescriptions,
                    benefit: this.benefits,
                });
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_benefit_detallado");
                }, 500);


            } catch (ex) {
                console.log(ex.message)
            }
        }
    }
}
</script>
<style scoped>

</style>
