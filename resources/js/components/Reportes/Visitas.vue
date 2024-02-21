<template>
    <v-main>
        <!-- Resumen del reporte -->
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el acumulado de ingresos a cada tema, por parte de los usuarios
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item
                v-show="workspaceId === 25"
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />

            <div v-if="workspaceId === 25">
                <list-item titulo="Grupo" subtitulo="Grupo al que pertenece el usuario" />
                <list-item titulo="Botica" subtitulo="Botica en la que se ubica el usuario" />
            </div>

            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />

            <div v-if="workspaceId === 25">
                <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
                <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            </div>

            <list-item
                titulo="Estado"
                subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
            />
            <list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
            <list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
            <list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
            <list-item titulo="Tema" subtitulo="Tema dentro de cada curso" />
            <list-item titulo="Cantidad de visitas por tema" subtitulo="Total de visitas por cada tema" />
        </ResumenExpand>
        <!-- Formulario del reporte -->
        <form class="row" @submit.prevent="generateReport">
            <!-- Ya que cuando se usa el axios de laravel, por defecto envia el token -->
            <div class="col-12">
                <div class="row px-3">
                    <div class="col-6 mt-2">
                        <DefaultAutocomplete
                            dense
                            v-model="modulo"
                            :items="modules"
                            label="Módulo"
                            item-text="name"
                            item-value="id"
                            multiple
                            :showSelectAll="false"
                            placeholder="Seleccione los módulos"
                            @onBlur="fetchFiltersCareerData"
                            :maxValuesSelected="4"
                            @onChange="moduloChange"
                        />

                    </div>
                    <div class="col-6"></div>

                    <div class="col-6">
                        <b-form-text text-variant="muted">Escuelas</b-form-text>
                        <v-select
                            attach
                            solo
                            chips
                            clearable
                            multiple
                            :show-select-all="false"
                            hide-details="false"
                            v-model="school"
                            :items="filteredSchools"
                            item-value="id"
                            item-text="name"
                            label="Selecciona un #Módulo"
                            @change="fetchFiltersSchoolData"
                            :disabled="modulo.length === 0"
                            :maxValuesSelected="10"
                            :background-color="!school ? '' : 'light-blue lighten-5'">
                        </v-select>
                    </div>

                    <div class="col-6">
                        <b-form-text text-variant="muted">Cursos</b-form-text>
                        <v-select
                            attach
                            solo
                            chips
                            clearable
                            multiple
                            :show-select-all="false"
                            hide-details="false"
                            v-model="course"
                            :items="courses"
                            item-value="id"
                            item-text="name"
                            label="Selecciona una #Escuela"
                            :disabled="!school.length"
                            :background-color="!course ? '' : 'light-blue lighten-5'">
                        </v-select>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6 pl-6">
                <EstadoFiltro ref="EstadoFiltroComponent" class="px-0"
                                  @emitir-cambio="" />

                <div class="row" v-if="workspaceId === 25">
                <!-- <div class="row"> -->
                    <div class="col-12">
                        <b-form-text text-variant="muted">Carreras</b-form-text>
                        <v-select
                            attach
                            solo
                            chips
                            clearable
                            multiple
                            :show-select-all="false"
                            hide-details="false"
                            v-model="career"
                            :items="careers"
                            item-value="id"
                            item-text="name"
                            label="Selecciona un #Módulo"
                            @change="fetchFiltersAreaData"
                            :disabled="!modulo"
                            :background-color="!career ? '' : 'light-blue lighten-5'">
                        </v-select>
                    </div>
                    <div class="col-12">
                        <b-form-text text-variant="muted">Áreas</b-form-text>
                        <v-select
                            attach
                            solo
                            chips
                            clearable
                            multiple
                            :show-select-all="false"
                            hide-details="false"
                            v-model="area"
                            item-value="id"
                            item-text="name"
                            label="Selecciona una #Carrera"
                            :disabled="!career.length"
                            :items="areas"
                            :background-color="!area ? '' : 'light-blue lighten-5'">
                        </v-select>
                    </div>
                </div>

                <div class="col p-0">
                    <small class="form-text text-muted text-bold">
                        Tipo de Cursos
                    </small>
                    <div class="d-flex mt-2">
                        <v-checkbox
                            class="my-0 mr-2"
                            label="Cursos Libres"
                            color="primary"
                            v-model="tipocurso"
                            hide-details="false"
                        />
                        <div
                            tooltip="Al marcar, el reporte generado sera solo con cursos libres."
                            tooltip-position="top"
                        >
                            <v-icon class="info-icon">mdi-information-outline</v-icon>
                        </div>
                    </div>
                </div>
            </div>

            <v-divider class="col-12 p-0 my-2"></v-divider>
            <div class="col-12">
                <FiltersNotification></FiltersNotification>
            </div>
            <div class="col-sm-12 mb-3">
                <div class="col-sm-6 pl-2">
                    <button
                        :disabled="modulo.length === 0"
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

import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";
import FiltersNotification from "../globals/FiltersNotification.vue";

export default {
    components: {FiltersNotification, EstadoFiltro, ResumenExpand, ListItem },
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'visitas',
            careers: [],
            areas: [],
            career: [],
            area: [],

            filteredSchools: [],
            schools: [],
            courses: [],
            school: [],
            course: [],

            modulo: [],
            tipocurso: false,

            loadingCarreras: false,
            loadingGrupos: false
        };
    },
    methods: {
        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportVisitas, type: vue.reportType})
        },
        async exportVisitas(reportName) {
            const vue = this;

            let UFC = vue.$refs.EstadoFiltroComponent;

            this.$emit('reportStarted')
            const filtersDescriptions = {
                "Módulos": this.generateNamesArray(this.modules, this.modulo),
                "Escuelas": this.generateNamesArray(this.schools, this.school),
                "Cursos": this.generateNamesArray(this.courses, this.course),
                "Usuarios activos" : this.yesOrNo(UFC.UsuariosActivos),
                "Usuarios inactivos" : this.yesOrNo(UFC.UsuariosInactivos),
                "Carreras" : this.generateNamesArray(this.careers, this.career),
                "Áreas" : this.generateNamesArray(this.areas, this.area),
                "Cursos libres": this.yesOrNo(this.tipocurso)
            }

            // Perform request to generate report

            let urlReport = `${vue.$props.reportsBaseUrl}/exportar/${this.reportType}`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: vue.workspaceId,
                        adminId: this.adminId,
                        reportName,
                        filtersDescriptions,
                        modulos: vue.modulo,
                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos,
                        tipocurso: vue.tipocurso,

                        careers: vue.career,
                        areas: vue.area,

                        schools: vue.school,
                        courses: vue.course
                    }
                })
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_visitas");
                }, 500);

            } catch (ex) {
                console.log(ex.message)
            }

            vue.hideLoader()
        },
        async fetchFiltersCareerData() {
            const vue = this;

            vue.careers = [];
            vue.career = [];

            vue.areas = [];
            vue.area = [];

            if (vue.modulo.length === 0) return;

            let url = `${vue.$props.reportsBaseUrl}/filtros/sub-workspace/${vue.modulo.join()}/criterion-values/career`
            let response = await axios({
                url: url,
                method: 'get'
            })

            vue.careers = response.data;

        },
        async fetchFiltersAreaData() {
            const vue = this;
            vue.areas = [];
            vue.area = [];

            if (vue.modulo.length === 0) return;

            let urlAreas = `${vue.$props.reportsBaseUrl}/filtros/sub-workspace/${vue.modulo.join()}/criterion-values/grupo`
            let responseAreas = await axios({ url: urlAreas,method: 'get' });
            vue.areas = responseAreas.data;
        },
        async fetchFiltersSchoolData() {
            const vue = this;

            vue.courses = [];
            vue.course = [];

            if (vue.school.length === 0) return;

            let urlCourses = `${vue.$props.reportsBaseUrl}/filtros/schools/courses`;
            let responseCourses = await axios( { url: urlCourses, method: 'post',
                                                 data: { schoolsIds: vue.school  } } );
            vue.courses = responseCourses.data;
        },
        async fetchFiltersData() {
            const vue = this;

            let urlSchools = `${vue.$props.reportsBaseUrl}/filtros/schools/${vue.$props.workspaceId}/${this.adminId}?grouped=0`;
            let responseSchools = await axios({ url: urlSchools, method: 'get'});
            vue.schools = responseSchools.data;
        },
        async moduloChange() {

            let vue = this;

            vue.school = [];
            vue.courses = [];

            let alreadyAdded = []
            vue.filteredSchools = vue.schools.filter(s => {
                if (vue.modulo.includes(s.subworkspace_id) &&
                    !alreadyAdded.includes(s.id)) {
                    alreadyAdded.push(s.id)
                    return true
                } else {
                    return false
                }
            })
        },
    },
    mounted() {
        const vue = this;
        //vue.modulo = vue.modules[0].id; //init module
        vue.fetchFiltersData();
        vue.fetchFiltersCareerData();
    }
};
</script>
