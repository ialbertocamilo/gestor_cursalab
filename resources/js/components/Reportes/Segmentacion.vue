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
        <form @submit.prevent="exportSegmentacion" class="row">
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
                    :maxValuesSelected="5"
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
            <div class="row col-sm-12 mb-3 ml-1">
                <button type="submit"
                        class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
                    <i class="fas fa-download"></i>
                    <span>Descargar</span>
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

export default {
    components: { EstadoFiltro, ResumenExpand, ListItem, CheckValidar, FechaFiltro },
    props: {
        workspaceId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
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

            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}`
            let responseSchools = await axios({
                url: urlSchools,
                method: 'get'
            })

            this.schools = responseSchools.data

        }
        ,
        async exportSegmentacion() {

            // show loading spinner

            this.showLoader()
            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/segmentation`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        modulos: this.modulo ? [this.modulo] : [],
                        escuelas: this.filters.school,
                        cursos: this.filters.course,
                    }
                })
                // When there are no results notify user,
                // download report otherwise
                if (response.data.alert) {
                    this.showAlert(response.data.alert, 'warning')
                } else {
                    // Emit event to parent component
                    this.$emit('emitir-reporte', response)
                }

            } catch (ex) {
                console.log(ex.message)
            }
            // Hide loading spinner
            this.hideLoader()
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
