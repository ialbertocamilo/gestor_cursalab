<template>
    <!-- Resumen del reporte -->
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el reporte detallado de usuarios en un Beneficio.
            </template>
            <list-item
                titulo="Beneficio"
                subtitulo="Nombre del beneficio"
            />
            <list-item titulo="Estado" subtitulo="Estado del beneficio (Activo - Inactivo)" />
            <list-item titulo="Estado actual" subtitulo="Puede ser: Activo, Bloqueado, Finalizado, Liberado" />
            <list-item
                titulo="Inicio de Inscripción"
                subtitulo="Fecha en la que los usuarios van a poder inscribirse al beneficio" />
            <list-item
                titulo="Cierre de Inscripción"
                subtitulo="Fecha en la que se cerrara la inscripción al beneficio" />
            <list-item titulo="Liberación" subtitulo="Fecha en la que inicia el beneficio" />
            <list-item titulo="Promotor" subtitulo="Empresa promotora del beneficio" />
            <list-item titulo="Expositor" subtitulo="Persona encargada del beneficio" />
            <list-item titulo="Cupos" subtitulo="Cantidad máxima de usuarios que puede inscribirse al beneficio" />
            <list-item titulo="Cantidad de segmentados" subtitulo="Cantidad de usuarios segmentados al beneficio" />
            <list-item titulo="Inscritos" subtitulo="Cantidad de usuarios inscritos del beneficio" />
            <list-item titulo="% de Inscritos" subtitulo="Porcentaje de inscritos con respecto a la cantidad total de usuarios segmentados" />
            <list-item titulo="Inscripciones extraordinarias" subtitulo="Cantidad de usuarios inscritos de manera extraordinaria al beneficio" />
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
            reportType: 'user_benefit_report',
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
