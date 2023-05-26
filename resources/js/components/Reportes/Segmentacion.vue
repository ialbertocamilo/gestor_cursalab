<template>
    <v-main>
        <!-- Resumen del reporte -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga la segmentación de un curso.
            </template>
            <list-item titulo="Email, Documento, Apellidos y nombres" subtitulo="Datos personales" />
            <list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
            <list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
            <list-item
                titulo="PROMEDIO"
                subtitulo="El promedio de las notas de los temas evaluables dentro del curso"
            />
            <list-item
                titulo="AVANCE(%)"
                subtitulo="Porcentaje del curso (cantidad de temas completados sobre la cantidad de temas asignados)"
            />
            <list-item
                titulo="RESULTADO CURSO"
                subtitulo="Resultado de cada curso, considerando la nota mínima aprobatoria configurada"
            />
        </ResumenExpand>

        <!-- Formulario del reporte -->
        <form @submit.prevent="generateReport" class="row">
            <!-- Escuela -->
            <div class="col-sm-6 mb-3">

                <DefaultAutocomplete
                    :disabled="!schools[0]"
                    v-model="filters.school"
                    :items="schools"
                    label="Escuela"
                    item-text="name"
                    item-value="id"
                    dense
                    multiple
                    @onChange="schoolsChange"
                    placeholder="Seleccione las escuelas"
                    :maxValuesSelected="10"
                    :showSelectAll="false"
                />
            </div>
            <!-- Curso -->
            <div class="col-sm-6 mb-3">

                <DefaultAutocomplete
                    :disabled="!courses[0]"
                    v-model="filters.course"
                    :items="courses"
                    label="Curso"
                    item-text="name"
                    item-value="id"
                    dense
                    placeholder="Seleccione los cursos"
                    :showSelectAll="false"

                />
            </div>
            <div class="col-12">
                <FiltersNotification></FiltersNotification>
            </div>
            <div class="row col-sm-12 mb-3 ml-1">
                <button type="submit"
                        :disabled="filters.school.length === 0"
                        class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
                    <i class="fas fa-download"></i>
                    <span>Generar reporte</span>
                </button>
            </div>
        </form>
    </v-main>
</template>

<script>

import CheckValidar from "./partials/CheckValidar.vue"
import FechaFiltro from "./partials/FechaFiltro.vue"
import ListItem from "./partials/ListItem.vue"
import ResumenExpand from "./partials/ResumenExpand.vue"
import EstadoFiltro from "./partials/EstadoFiltro.vue"
import FiltersNotification from "../globals/FiltersNotification.vue";

export default {
    components: {FiltersNotification, EstadoFiltro, ResumenExpand, ListItem, CheckValidar, FechaFiltro },
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'segmentation',
            schools: [],
            courses: [],
            //
            filters:{
                school: [],
                course: [],
            },
        };
    }
    ,
    mounted() {
        this.fetchFiltersData()
    }
    ,
    methods: {
        /**
         *
         * @returns {Promise<void>}
         */
        async fetchFiltersData () {

            // Fetch schools

            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}?grouped=0`
            let responseSchools = await axios({
                url: urlSchools,
                method: 'get'
            })

            this.schools = responseSchools.data

        }
        ,
        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportSegmentacion, type: vue.reportType})
        },
        async exportSegmentacion(reportName) {

            this.$emit('reportStarted', {})
            const filtersDescriptions = {
                "Escuelas": this.generateNamesArray(this.schools, this.filters.school),
                "Cursos": this.generateNamesArray(this.courses, [this.filters.course]),
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
                        modulos: this.modulo ? [this.modulo] : [],
                        escuelas: this.filters.school,
                        cursos: this.filters.course,
                    }
                })
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_segmentacion");
                }, 500);

            } catch (ex) {
                console.log(ex.message)
            }
        }
        ,
        /**
         * Fetch courses
         * @returns {Promise<boolean>}
         */
         schoolsChange() {
            const vue = this;
            //clean data
            vue.filters.course = [];
            vue.courses = [];

            //check schoolId
            if(!vue.filters.school.length) return;

            const reqPayload = {
                schoolIds: vue.filters.school,
                active:1,
                inactive:0
            };

            axios.post(`${vue.reportsBaseUrl}/filtros/school/courses/states`, reqPayload).then((res) => {

                const { data } = res;
                vue.courses = data;

            }, (err) => console.log(err));
        },
    }
}

</script>

<style>

</style>
