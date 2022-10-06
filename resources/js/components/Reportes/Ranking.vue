<template>
    <!-- Resumen del reporte -->
    <v-main>
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Ranking de todos los usuarios de la plataforma ordenados por puntaje obtenido
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />
            <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
            <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            <list-item titulo="Puntaje (P)" subtitulo="Puntaje total del usuario" />
            <list-item titulo="Cantidad completados (CC)" subtitulo="Cursos completados" />
            <list-item titulo="Nota promedio (NP)" subtitulo="Nota promedio de todos los cursos completados por el usuario" />
            <list-item titulo="Intentos (I)" subtitulo="Cantidad total de intentos del usuario" />
            <list-item titulo="Última Evaluación" subtitulo="Fecha de última de evaluación generada por el usuario" />
            <v-divider />
            <list-item titulo="Fórmula para calcular el puntaje" subtitulo=" P = (CC*150 + NP*100) - I*0.5" />
            <list-item titulo="Nota" subtitulo="En caso de empate, la ultima evaluación definará el orden; es decir el usuario que ha resuelto primero su última evaluación tendrá una mejor posición con respecto a los demás usuarios con el mismo puntaje" />
        </ResumenExpand>
        <form @submit.prevent="exportNotasCurso" class="row">
            <!-- Modulo -->
            <div class="col-sm-4 mb-3">
                <b-form-text text-variant="muted">Módulo</b-form-text>
                <select v-model="modulo" class="form-control">
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in modules"
                            :key="index"
                            :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <v-divider class="col-12 mb-0 p-0"></v-divider>
            <!-- Fechas -->
            <div class="col-12 d-flex">
                <!-- Filtros secundarios -->
                <div class="col-8 px-0">
                    <!-- Filtros Checkboxs -->
                <EstadoFiltro
                    ref="EstadoFiltroComponent"
                    @emitir-cambio="" />
                </div>
            </div>
            <v-divider class="col-12 mb-5 p-0"></v-divider>
            <!-- Job positions -->
            <div class="col-lg-6 col-xl-4 mb-3">
                <b-form-text text-variant="muted">Puesto</b-form-text>
                <select v-model="jobPosition" class="form-control"
                        :disabled="!jobPositions[0]">
                    <option value>- Todos -</option>
                    <option v-for="(item, index) in jobPositions"
                            :key="index"
                            :value="item.name">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <v-divider class="col-12 mb-5 p-0"></v-divider>
            <button type="submit" class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2">
                <i class="fas fa-download"></i>
                <span>Descargar</span>
            </button>
        </form>
    </v-main>
</template>
<script>

import ResumenExpand from "./partials/ResumenExpand.vue";
import ListItem from "./partials/ListItem.vue";
import EstadoFiltro from "./partials/EstadoFiltro.vue";

export default {
    components: { EstadoFiltro, ResumenExpand ,ListItem},
    props: {
        workspaceId: 0,
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            jobPositions: [],
            modulo: '',
            jobPosition: ''
        }
    },
    mounted() {
        this.fetchFiltersData()
    },
    methods: {
        async fetchFiltersData () {
            let url = `${this.$props.reportsBaseUrl}/filtros/job-positions/${this.$props.workspaceId}`
            let response = await axios({
                url: url,
                method: 'get'
            })

            this.jobPositions = response.data
        },
        async exportNotasCurso() {

            // show loading spinner

            this.showLoader()

            let UFC = this.$refs.EstadoFiltroComponent;
            //let fechaFiltro = this.$refs.FechasFiltros;

            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/ranking`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        modulos: this.modulo ? [this.modulo] : [],
                        jobPosition: this.jobPosition,
                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos
                    }
                })

                // When there are no results notify
                // user, download report otherwise

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
    }
}
</script>
<style>
.v-label {
    display: contents !important;
}
.v-list-item__subtitle{
    white-space: normal !important;
}
</style>
