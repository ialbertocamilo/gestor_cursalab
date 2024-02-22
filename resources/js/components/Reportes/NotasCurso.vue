<template>
    <v-main>
        <!-- Resumen del reporte -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el avance de los cursos segmentados y desarrollados, hasta el momento, por los usuarios.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item
                v-show="workspaceId === 25"
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />
            <!-- this only for FP -->
            <div v-show="workspaceId === 25">
                <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            </div>
            <!-- this only for FP -->

            <list-item v-show="workspaceId === 25" titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="Documento, Apellidos y nombres" subtitulo="Datos personales" />

            <!-- this only for FP -->
            <div v-show="workspaceId === 25">
                <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
                <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            </div>
            <!-- this only for FP -->


            <list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
            <list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
            <list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
            <list-item titulo="Tipo de curso" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
            <list-item titulo="Última sesión" subtitulo="Fecha de la última sesión en la plataforma" />
            <list-item titulo="Visitas por curso" subtitulo="Cantidad de visitas al curso" />
            <list-item
                titulo="Nota promedio"
                subtitulo="El promedio de las notas de los temas evaluables dentro del curso"
            />
            <list-item titulo="Temas asignados" subtitulo="Cantidad de temas asignados al curso" />
            <list-item titulo="Temas completados" subtitulo="Cantidad de temas completados del curso" />
            <list-item
                titulo="Porcentaje"
                subtitulo="Porcentaje del curso (cantidad de temas completados sobre la cantidad de temas asignados)"
            />
            <list-item
                titulo="Resultado"
                subtitulo="Resultado de cada curso, considerando la nota mínima aprobatoria configurada"
            />
            <list-item titulo="Última visita" subtitulo="Fecha de la última visita realizada al curso" />
            <list-item
                titulo="Estado del curso"
                subtitulo="Representa si el curso esta activo/inactivo en la plataforma"
            />
            <list-item titulo="Ciclo del curso" subtitulo="Ciclo/Ciclos al que pertenece el curso" />
        </ResumenExpand>

        <!-- Formulario del reporte -->
        <form @submit.prevent="generateReport" class="row">
            <div class="col-12">
                <div class="row px-3">
                    <!-- Modulo -->
                    <div class="col-sm-6 mb-3">

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
                            @onBlur="fetchFiltersAreaData"
                            :maxValuesSelected="maxValuesSelected.modules"
                            @onChange="moduloChange"
                        />
                    </div>
                    <!-- Escuela -->
                    <div class="col-sm-6 mb-3">
                        <DefaultAutocomplete
                            dense
                            v-model="escuela"
                            :items="filteredSchools"
                            :disabled="!filteredSchools[0]"
                            label="Escuelas"
                            item-text="name"
                            item-value="id"
                            multiple
                            placeholder="Seleccione las escuelas"
                            :maxValuesSelected="maxValuesSelected.schools"
                            :showSelectAll="maxValuesSelected.show_select_all"
                            @onChange="loadCourses"
                        />
                    </div>
                    <!-- Modalidad -->
                    <div class="col-sm-6 mb-3">
                        <DefaultAutocomplete
                            dense
                            v-model="modality"
                            :items="modalities"
                            label="Modalidades"
                            item-text="name"
                            item-value="id"
                            placeholder="Seleccione una modalidad"
                            @onChange="loadCourses"
                        />
                    </div>
                    <!-- Curso -->
                    <div class="col-sm-6 mb-3">
                        <DefaultAutocomplete
                            dense
                            v-model="curso"
                            :items="courses"
                            :disabled="!courses[0]"
                            label="Curso"
                            item-text="name"
                            item-value="id"
                            multiple
                            :showSelectAll="true"
                            placeholder="Seleccione los cursos"
                        />
                    </div>
                </div>
            </div>
            <v-divider class="col-12 mb-0 p-0"></v-divider>
            <!-- Filtros secundarios -->
            <div class="col-12 d-flex pt-0">
                <!-- Filtros Checkboxs -->
                <div class="col-8 pt-0 px-0">
                    <EstadoFiltro ref="EstadoFiltroComponent"
                                  @emitir-cambio="" />

                    <div class="col mb-3 mt-1" v-if="workspaceId === 25">
                        <b-form-text text-variant="muted">Áreas</b-form-text>
                        <v-select
                            attach
                            solo
                            chips
                            clearable
                            multiple
                            :show-select-all="true"
                            hide-details="false"
                            v-model="area"
                            :items="areas"
                            item-value="id"
                            item-text="name"
                            label="Selecciona un #Módulo"
                            :disabled="!modulo"
                            :background-color="!area ? '' : 'light-blue lighten-5'">
                        </v-select>
                    </div>

                    <v-divider class="col-12 p-0 m-0"></v-divider>
                    <div class="col">
                        <small class="form-text text-muted text-bold">
                            Resultado del Curso :
                        </small>
                        <div class="form-row mt-2">
                            <div class="col-6 mr-auto d-flex align-center">
                                <v-checkbox
                                    class="my-0 mr-2"
                                    label="Completados"
                                    color="success"
                                    v-model="aprobados"
                                    hide-details="false"
                                />
                                <div
                                    tooltip="Resultados promedio de temas del curso iguales o superiores a la nota mínima aprobatoria asignada al curso."
                                    tooltip-position="top"
                                >
                                    <v-icon class="info-icon">mdi-information-outline</v-icon>
                                </div>
                            </div>
                            <div class="col-6 mr-auto d-flex align-center">
                                <v-checkbox
                                    class="my-0 mr-2"
                                    label="Encuesta pendiente"
                                    color="primary"
                                    v-model="encuestaPendiente"
                                    hide-details="false"
                                />
                                <div
                                    tooltip="Estado luego de aprobar evaluación del curso pero con encuesta pendiente."
                                    tooltip-position="top"
                                >
                                    <v-icon class="info-icon">mdi-information-outline</v-icon>
                                </div>
                            </div>
                            <div class="col-6 mr-auto d-flex align-center">
                                <v-checkbox
                                    class="my-0 mr-2"
                                    label="En desarrollo"
                                    color="red"
                                    v-model="desarrollo"
                                    hide-details="false"
                                />
                                <div
                                    tooltip="El colaborador aún no obtiene todas las calificaciones de los temas del curso aprobadas. Puede que las tenga desaprobadas pero con intentos restantes disponibles."
                                    tooltip-position="top"
                                >
                                    <v-icon class="info-icon">mdi-information-outline</v-icon>
                                </div>
                            </div>
                            <div class="col-6 mr-auto d-flex align-center">
                                <v-checkbox
                                    class="my-0 mr-2"
                                    label="Desaprobados"
                                    color="warning"
                                    v-model="desaprobados"
                                    hide-details="false"
                                />
                                <div
                                    tooltip="Tras agotar todos los intentos de temas del curso y obtener resultados inferiores a la nota mínima aprobatoria asignada al tema, se considera desaprobación."
                                    tooltip-position="top"
                                >
                                    <v-icon class="info-icon">mdi-information-outline</v-icon>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col">
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
                    <CheckValidar ref="checkValidacion" />
                </div>
                <!-- Fechas -->
                <div class="col-4 ml-auto">
                    <FechaFiltro ref="FechasFiltros"
                                 label-start="Fecha inicial de última actualización"
                                 label-end="Fecha final de última actualización"/>
                </div>
            </div>
            <v-divider class="col-12 mb-5 p-0"></v-divider>
            <div class="col-12">
                <FiltersNotification></FiltersNotification>
            </div>
            <div class="col-12 px-6">
                <button
                    :disabled="modulo.length === 0 || escuela.length === 0"
                    type="submit"
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

// console.log(max_values_selected_modules);
// console.log(max_values_selected_schools);
// console.log(currentDomain);
export default {
    components: {FiltersNotification, EstadoFiltro, ResumenExpand, ListItem, CheckValidar, FechaFiltro },
    props: {
        workspaceId: 0,
        adminId: 0,
        modules: Array,
        modalities: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            filteredSchools: [],
            reportType: 'consolidado_cursos',
            schools: [],
            courses: [],
            areas: [],
            //
            modulo: [],
            escuela: [],
            curso: [],
            area: [],
            modality:null,
            //
            aprobados: true,
            tipocurso: false,
            desaprobados: true,
            encuestaPendiente: true,
            desarrollo: true,
            maxValuesSelected:{
                modules:4,
                schools:10,
                show_select_all:false
            }
        };
    },
    methods: {
        async fetchFiltersData () {
            // Fetch schools
            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}/${this.adminId}?grouped=0`
            let responseSchools = await axios({
                url: urlSchools,
                method: 'get'
            })

            this.schools = responseSchools.data
        },

        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportNotasCurso, type: vue.reportType})
        },
        async exportNotasCurso(reportName) {

            let UFC = this.$refs.EstadoFiltroComponent;
            let DATES = this.$refs.FechasFiltros;

            this.$emit('reportStarted')
            const filtersDescriptions = {
                "Módulos": this.generateNamesArray(this.modules, this.modulo),
                "Escuelas": this.generateNamesArray(this.schools, this.escuela),
                "Cursos": this.generateNamesArray(this.courses, this.curso),
                "Usuarios activos" : this.yesOrNo(UFC.UsuariosActivos),
                "Usuarios inactivos" : this.yesOrNo(UFC.UsuariosInactivos),
                'Fecha inicial': DATES.start,
                'Fecha final': DATES.end,
                'Aprobados': this.yesOrNo(this.aprobados),
                'Desaprobados': this.yesOrNo(this.desaprobados),
                'Desarrollo': this.yesOrNo(this.desarrollo),
                'Encuesta pendiente': this.yesOrNo(this.encuestaPendiente),

                "Áreas" : this.generateNamesArray(this.areas, this.area),
                "Cursos libres": this.yesOrNo(this.tipocurso)
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
                        modulos: this.modulo,
                        escuelas: this.escuela,
                        cursos: this.curso,
                        areas: this.area,

                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos,

                        aprobados: this.aprobados,
                        desaprobados: this.desaprobados,
                        desarrollo: this.desarrollo,
                        tipocurso: this.tipocurso,

                        start: DATES.start,
                        end: DATES.end,
                        encuestaPendiente : this.encuestaPendiente
                    }
                })
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_cursos");
                }, 500);

            } catch (ex) {
                console.log(ex.message)
            }
        },
        async fetchFiltersAreaData() {
            if (this.modulo.length === 0) {
                this.areas = [];
                return;
            }

            let url = `${this.$props.reportsBaseUrl}/filtros/sub-workspace/${this.modulo.join()}/criterion-values/grupo`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.areas = response.data
        },
        async moduloChange() {

            let vue = this;

            vue.escuela = [];
            vue.curso = [];
            vue.tema = [];

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
        async loadCourses() {
            this.curso = [];
            this.tema = [];
            this.courses = [];
            this.topics = [];

            if (this.escuela.length === 0) return false;

            this.cursos_libres = false;
            let url = `${this.$props.reportsBaseUrl}/filtros/courses/${this.escuela.join()}/all?modality_id=${this.modality}`
            let res = await axios({
                url,
                method: 'get'
            });
            this.courses = res.data;
        }
    },
    mounted() {
        this.fetchFiltersData();
        const domainsToExcludeConstraint = ['gestiona.potenciandotutalentongr.pe','gestiona.agile.cursalab.io','gestiona.capacitacioncorporativagruposanpablo.com'];
        const currentDomain = new URL(window.location.href).hostname;
        this.modality = this.modalities.find((m) => m.code =='asynchronous').id;
        domainsToExcludeConstraint.forEach(domain => {
            if(domain.includes(currentDomain)){
                this.maxValuesSelected.modules = 0;
                this.maxValuesSelected.schools = 0;
                this.maxValuesSelected.show_select_all = true;
            }
        });
    }
}

</script>

<style>

</style>
