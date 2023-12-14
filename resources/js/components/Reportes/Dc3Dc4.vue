<template>
    <v-main>
        <!-- Resumen del reporte -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Genera el certificado DC3 y DC4 relacionado a tus colaboradores sobre los cursos completados.
            </template>
            <!-- <list-item titulo="Email, Documento, Apellidos y nombres" subtitulo="Datos personales" />
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
            /> -->
        </ResumenExpand>

        <!-- Formulario del reporte -->
        <form @submit.prevent="generateReport" class="row">

            <div class="col-sm-6 mb-3">
                <DefaultAutocomplete
                    v-model="filters.type_report"
                    :items="types_report"
                    label="Tipo de reporte"
                    item-text="name"
                    item-value="code"
                    dense
                />
            </div>
            <!-- <div class="col-sm-6 mb-3 d-flex align-items-center" v-if="filters.type_report == 'dc3_report'">
                <v-checkbox
                    class="my-0 mr-2"
                    label="Por Curso"
                    color="primary"
                    v-model="type_dc3_report"
                    value="by_course"
                    hide-details="false"
                />
                <v-checkbox
                    class="my-0 mr-2"
                    label="Por colaborador"
                    color="primary"
                    v-model="type_dc3_report"
                    value="by_user"
                    hide-details="false"
                />
            </div> -->
            <!-- Escuela -->
            <div class="col-sm-6 mb-3" v-if="filters.type_report == 'dc3_report' && type_dc3_report == 'by_course'">

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
            <div class="col-sm-6 mb-3" v-if="filters.type_report == 'dc3_report' && type_dc3_report == 'by_course'">

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
            <!-- Por usuario -->
            <div class="col-sm-12 mb-3" v-if="filters.type_report == 'dc3_report' && type_dc3_report == 'by_user'">
                <SearchByDocument
                    ref="SearchByDocument"
                    :segment="[]"
                    :current-clean="segment_by_document_clean"
                    @addUser="addUser"
                    @deleteUser="deleteUser"
                    @addUserAll="addUserAll"
                    @deleteUserAll="deleteUserAll"
                />
            </div>
            <!-- <div class="col-12">
                <FiltersNotification></FiltersNotification>
            </div> -->
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
import SearchByDocument from '../Segment/SearchByDocument'

export default {
    components: {FiltersNotification, EstadoFiltro, ResumenExpand, ListItem, CheckValidar, FechaFiltro,SearchByDocument },
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'dc3-report',
            schools: [],
            courses: [],
            //
            filters:{
                school: [],
                course: [],
                type_report:null
            },
            types_report:[
                {code:'dc3_report',name:'Reporte DC3'},
                {code:'dc4_report',name:'Reporte DC4'},
            ],
            type_dc3_report:'by_course',
            search: null,
            list_segments_document:{},
            segment_by_document_clean: false,
        };
    },
    watch: {
        search(filter_text) {
            let vue = this;

            if (filter_text === null) return vue.filter_result = [];

            if (filter_text.length <= 2) return vue.filter_result = [];

            const { docState, docData } = vue.checkIfExistUser(filter_text);
            if (docState) return vue.filter_result = [];

            vue.autocomplete_loading = true;

            clearTimeout(this.debounce);

            this.debounce = setTimeout(() => {
                let data = { filter_text: filter_text,
                             omit_documents: docData };
                const url = `/segments/search-users`;

                vue.$http.post(url, data)
                    .then(({data}) => {
                        const users = (data.data.users) ? data.data.users :  data.data;
                        vue.filter_result = users;
                        vue.autocomplete_loading = false;
                    })
                    .catch(err => {
                        vue.autocomplete_loading = false;
                    })

            }, 1600);
    }
    },
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

            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}/${this.adminId}?grouped=0`
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
                        ext:'zip'
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
                inactive:0,
                can_create_certificate_dc3_dc4:true
            };

            axios.post(`${vue.reportsBaseUrl}/filtros/school/courses/states`, reqPayload).then((res) => {

                const { data } = res;
                vue.courses = data;

            }, (err) => console.log(err));
        },
        addUserAll(user) {
            let vue = this;
            if(vue.list_segments_document === undefined || vue.list_segments_document === null) {
                vue.list_segments_document = {'segmentation_by_document': []};
            }
            const already_added = vue.list_segments_document.segmentation_by_document.filter(el => el.document == user.document).length > 0;

            if (!already_added) {
                vue.list_segments_document.segmentation_by_document.push(user)
            }
        },
        deleteUserAll() {
            let vue = this;
            if(vue.list_segments_document === undefined || vue.list_segments_document === null) {
                vue.list_segments_document = {'segmentation_by_document': []};
            }
            vue.list_segments_document.segmentation_by_document = [];
        },
        addUser(user) {
            let vue = this;
            if(vue.list_segments_document === undefined || vue.list_segments_document === null) {
                vue.list_segments_document = {'segmentation_by_document': []};
            }
            const already_added = vue.list_segments_document.segmentation_by_document.filter(el => el.document == user.document).length > 0;

            if (!already_added) {

                vue.list_segments_document.segmentation_by_document.push(user)

                vue.$refs["SearchByDocument"].addOrRemoveFromFilterResult(user, 'remove');
            }
        },
        deleteUser(user) {
            let vue = this;

            const index = vue.list_segments_document.segmentation_by_document.findIndex(el => el.document == user.document);

            if (index !== -1) {

                vue.list_segments_document.segmentation_by_document.splice(index, 1);

                // vue.$refs["SearchByDocument"].addOrRemoveFromFilterResult(user);
            }
        },
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

