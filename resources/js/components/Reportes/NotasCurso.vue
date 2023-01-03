<template>
    <v-main>
        <!-- Resumen del reporte -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el avance de los cursos segmentados y desarrollados, hasta el momento, por los usuarios.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />
            <!-- this only for FP -->
            <div v-show="workspaceId === 25">
                <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            </div>
            <!-- this only for FP -->

            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
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
        <form @submit.prevent="exportNotasCurso" class="row">
            <div class="col-12">
                <div class="row px-3">
                    <!-- Modulo -->
                    <div class="col-sm-4 mb-3">

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
                            :maxValuesSelected="4"
                        />
                    </div>
                    <!-- Escuela -->
                    <div class="col-sm-4 mb-3">
                        <DefaultAutocomplete
                            dense
                            v-model="escuela"
                            :items="schools"
                            :disabled="!schools[0]"
                            label="Escuelas"
                            item-text="name"
                            item-value="id"
                            multiple
                            :showSelectAll="false"
                            placeholder="Seleccione las escuelas"
                            @onChange="escuelaChange"
                            :maxValuesSelected="10"
                        />
                    </div>
                    <!-- Curso -->
                    <div class="col-sm-4 mb-3">
                        <DefaultAutocomplete
                            dense
                            v-model="curso"
                            :items="courses"
                            :disabled="!courses[0]"
                            label="Curso"
                            item-text="name"
                            item-value="id"
                            multiple
                            :showSelectAll="false"
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
                            :show-select-all="false"
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
import FiltersNotification from "../globals/FiltersNotification.vue";

export default {
    components: {FiltersNotification, EstadoFiltro, ResumenExpand, ListItem, CheckValidar, FechaFiltro },
    props: {
        workspaceId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            schools: [],
            courses: [],
            areas: [],
            //
            modulo: [],
            escuela: [],
            curso: [],
            area: [],
            //
            aprobados: true,
            tipocurso: false,
            desaprobados: true,
            encuestaPendiente: true,
            desarrollo: true
        };
    },
    methods: {
        async fetchFiltersData () {
            // Fetch schools
            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}`
            let responseSchools = await axios({
                url: urlSchools,
                method: 'get'
            })

            this.schools = responseSchools.data
        },
        async exportNotasCurso() {
            let vue = this;

            // show loading spinner

            this.showLoader()

            let UFC = this.$refs.EstadoFiltroComponent;
            let DATES = this.$refs.FechasFiltros;

            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/consolidado_cursos_v2`
            // let urlReport = `${this.$props.reportsBaseUrl}/exportar/consolidado_cursos`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
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

                // When there are no results notify user,
                // download report otherwise

                if (response.data.alert) {
                    this.showAlert(response.data.alert, 'warning')
                } else {
                    vue.queryStatus("reportes", "descargar_reporte_cursos");
                    // Emit event to parent component
                    response.data.new_name = this.generateFilename(
                        'Notas por curso',
                        this.generateNamesString(this.modules, this.modulo)
                    )
                    response.data.selectedFilters = {
                        "Módulos": this.generateNamesString(this.modules, this.modulo),
                        "Escuelas": this.generateNamesString(this.schools, this.school),
                        "Cursos": this.generateNamesString(this.courses, this.course),
                        "Temas": this.generateNamesString(this.topics, this.tema),
                        "Usuarios activos" : this.yesOrNo(UFC.UsuariosActivos),
                        "Usuarios inactivos" : this.yesOrNo(UFC.UsuariosInactivos),
                        "Temas activos": this.yesOrNo(TEMAS.UsuariosActivos),
                        "Temas inactivos": this.yesOrNo(TEMAS.UsuariosInactivos),
                        'Fecha inicial': DATES.start,
                        'Fecha final': DATES.end,
                        'Aprobados': this.yesOrNo(this.aprobados),
                        'Desaprobados': this.yesOrNo(this.desaprobados),
                        'Desarrollo': this.yesOrNo(this.desarrollo),
                        'Encuesta pendiente': this.yesOrNo(this.encuestaPendiente),

                        "Áreas" : this.generateNamesString(this.areas, this.area),
                        "Cursos libres": this.yesOrNo(this.tipocurso)
                    }
                    this.$emit('emitir-reporte', response)
                }

            } catch (ex) {
                console.log(ex.message)
            }

            // Hide loading spinner

            this.hideLoader()
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
        async escuelaChange() {
            this.curso = [];
            this.tema = [];
            this.courses = [];
            this.topics = [];

            if (this.escuela.length === 0) return false;

            this.cursos_libres = false;
            let url = `${this.$props.reportsBaseUrl}/filtros/courses/${this.escuela.join()}`
            let res = await axios({
                url,
                method: 'get'
            });
            this.courses = res.data;
        }
    },
    mounted() {
        this.fetchFiltersData();
    }
}

</script>

<style>

</style>
