<template>
    <v-main>
        <!-- Resumen del reporte -->

        <ResumenExpand>
            <template v-slot:resumen>
                Descarga el progreso de los usuarios solo en los <b>temas evaluables y calificados</b>
                desarrollados hasta el momento.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />
            <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="Documento, Apellidos y nombres" subtitulo="Datos personales" />
            <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
            <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            <list-item
                titulo="Estado"
                subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
            />
            <list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
            <list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
            <list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
            <list-item titulo="Tema" subtitulo="Tema dentro de cada curso" />
            <list-item
                titulo="Intentos"
                subtitulo="Cantidad de intentos utlizados en la prueba del tema"
            />
            <list-item titulo="Nota" subtitulo="Nota actual de la evaluación (La nota más alta)" />
            <list-item
                titulo="Resultado"
                subtitulo="Resultado de cada evaluación, considerando la nota mínima aprobatoria configurada"
            />
            <list-item titulo="Reinicio por tema" subtitulo="Cantidad de reinicios realizados por tema" />
            <list-item
                titulo="Última evaluación"
                subtitulo="Fecha de la última evaluación realizada de cada tema"
            />

            <list-item
                titulo="Estado (Tema)"
                subtitulo="Representa si el tema esta activo/inactivo en la plataforma"
            />
        </ResumenExpand>

        <!-- Formulario del reporte -->
        <form @submit.prevent="exportNotasTema" class="row formu">
            <div class="col-lg-6 col-xl-4 mb-3">
                <!-- Modulo -->
                <b-form-text text-variant="muted">Módulo</b-form-text>
                <select
                    v-model="modulo"
                    class="form-control"
                >
                    <option value="">[Todos]</option>
                    <option v-for="(item, index) in modules"
                            :key="index"
                            :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <!-- Escuela -->
            <div class="col-lg-6 col-xl-4 mb-3">
                <b-form-text text-variant="muted">Escuela</b-form-text>
                <select
                    v-model="escuela"
                    class="form-control"
                    :disabled="!schools[0]"
                    @change="escuelaChange"
                >
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in schools"
                            :key="index" :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <!-- Curso -->
            <div class="col-lg-6 col-xl-4 mb-3">
                <b-form-text text-variant="muted">Curso</b-form-text>
                <select v-model="curso" class="form-control"
                        :disabled="!courses[0]"
                        @change="cursoChange">
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in courses"
                            :key="index"
                            :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <!-- Tema -->
            <div class="col-lg-6 col-xl-4 mb-3">
                <b-form-text text-variant="muted">Tema</b-form-text>
                <select v-model="tema" class="form-control"
                        :disabled="!topics[0]">
                    <option value>- Todos -</option>
                    <option v-for="(item, index) in topics"
                            :key="index" :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <v-divider class="col-12 mb-0 p-0"></v-divider>
            <!-- Filtros secundarios -->
            <div class="col-6 d-flex">

                <EstadoFiltro ref="EstadoFiltroComponent"
                              @emitir-cambio="" />

                <CheckValidar ref="checkValidacion" />
            </div>

            <!--          Fechas          -->
            <div class="col-6">
<!--                <FechaFiltro ref="FechasFiltros" />-->
            </div>

            <div class="col-12">
                <small class="text-muted text-bold">Resultado del Tema :</small>
                <div class="d-flex mt-2">
                    <div class="col-3 p-0 mr-auto d-flex align-center">
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
                    <div class="col-3 p-0 mr-auto d-flex align-center">
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
                    <div class="col-3 p-0 mr-auto d-flex align-center">
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
                    <div class="col-3 p-0 mr-auto d-flex align-center">
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
            <v-divider class="col-12 p-0 m-0 mb-4"></v-divider>

            <button type="submit"
                    class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
                <i class="fas fa-download"></i>
                <span>Descargar</span>
            </button>
        </form>
    </v-main>
</template>

<script>
import CheckTemas from "./partials/CheckTemas.vue";
import CheckValidar from "./partials/CheckValidar.vue";
import CheckVariantes from "./partials/CheckVariantes.vue";
import FechaFiltro from "./partials/FechaFiltro.vue";
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";

export default {
    components: {
        EstadoFiltro,
        ResumenExpand,
        ListItem,
        CheckValidar,
        CheckVariantes,
        CheckTemas,
        FechaFiltro
    },
    props: {
        workspaceId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            schools: [],
            courses: [],
            topics: [],

            modulo: "",
            escuela: "",
            curso: "",
            tema: "",

            //
            aprobados: true,
            desaprobados: true,
            encuestaPendiente: true,
            desarrollo: true
        };
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

            let urlSchools = `${this.$props.reportsBaseUrl}/filtros/schools/${this.$props.workspaceId}`
            let responseSchools = await axios({
                url: urlSchools,
                method: 'get'
            })

            this.schools = responseSchools.data
        }
        ,
        async exportNotasTema() {

            // show loading spinner

            this.showLoader()

            let UFC = this.$refs.EstadoFiltroComponent;
            //let fechaFiltro = this.$refs.FechasFiltros;

            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/consolidado_temas`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        modulos: this.modulo ? [this.modulo] : [],
                        escuelas: this.escuela ? [this.escuela] : [],
                        cursos: this.curso ? [this.curso] : [],
                        temas: this.tema ? [this.tema] : [],

                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos,

                        aprobados: this.aprobados,
                        desaprobados: this.desaprobados,
                        desarrollo: this.desarrollo,
                        encuestaPendiente : this.encuestaPendiente
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
        },
        /**
         * Fetch courses
         * @returns {Promise<boolean>}
         */
        async escuelaChange() {
            this.curso = null;
            this.tema = null;
            this.courses = [];
            this.topics = [];

            if (!this.escuela) return false;

            this.cursos_libres =false;
            let url = `${this.$props.reportsBaseUrl}/filtros/courses/${this.escuela}`
            let res = await axios({
                url,
                method: 'get'
            });
            this.courses = res.data;
        },
        /**
         * Fetch topics
         * @returns {Promise<boolean>}
         */
        async cursoChange() {
            this.tema = null;
            this.topics = [];
            if (!this.curso) return false;

            let url = `${this.$props.reportsBaseUrl}/filtros/topics/${this.curso}`
            let res = await axios({
                url,
                method: 'get'
            });
            this.topics = res.data;
        },

    }
};
</script>

<style scoped>
.v-label {
    display: contents !important;
}
::-webkit-calendar-picker-indicator {
    color: rgba(0, 0, 0, 0);
    opacity: 0;
}
.max-w-900 {
    max-width: 900px;
}
</style>
