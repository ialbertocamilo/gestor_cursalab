<template>
    <v-main>
        <!-- Resumen del reporte -->
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el registro de reuniones y datos de sus asistentes.
            </template>
            <h5 class="ml-3">Datos generales</h5>
            <div class="ml-4">
                <list-item titulo="Título" subtitulo="Título de la reunión"/>
                <list-item titulo="Fechas de programación del evento"
                           subtitulo="Fecha de inicio y Fecha de fin programados"/>
                <list-item titulo="Duración" subtitulo="Duración estimada de la reunión en minutos"/>
                <list-item titulo="Horarios de asistencia"
                           subtitulo="Fecha de toma de asistencias (Primera, segunda y tercera)"/>
                <list-item titulo="Duración real" subtitulo="Duración real de la reunión en minutos"/>
                <list-item titulo="Fecha de inicio y fin real" subtitulo=""/>
                <list-item titulo="Anfitrión" subtitulo="Datos del anfitrión de la reunión"/>
                <list-item titulo="Tipo de Reunión" subtitulo=""/>
                <list-item titulo="Estado de la reunión"
                           subtitulo="Estado actual de la reunión \n (Agendada, En Trasncurso, Finalizada, Cancelada)"/>
                <list-item
                    titulo="Cantidad de Invitados"
                    subtitulo=""
                />
                <list-item
                    titulo="Cantidad de Asistentes"
                    subtitulo="Cantidad de Usuarios que entraron a la reunión"
                />
                <list-item titulo="Porcentaje de asistentes" subtitulo=""/>
            </div>

            <h5 class="ml-3">Detalle de asistentes</h5>
            <div class="ml-4">
                <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario"/>
                <list-item titulo="Documento, Apellidos y nombres" subtitulo="Datos personales"/>
                <list-item titulo="Carrera (Usuario)" subtitulo="Carrera actual en la que se encuentra"/>
                <list-item titulo="Ciclo actual (Usuario)" subtitulo="Ciclo actual en la que se encuentra"/>
                <list-item titulo="Tipo reinicio" subtitulo="Tipo de reinicio realizado (al tema o curso)"/>
                <list-item titulo="Curso" subtitulo="Nombre del curso"/>
                <list-item titulo="Tema" subtitulo="Nombre del tema"/>
                <list-item
                    titulo="Reinicios"
                    subtitulo="Cantidad de reinicios realizados (Por tipo de reinicio)"
                />
                <list-item titulo="Fecha" subtitulo="Fecha en la que se realizó el reinicio"/>
                <list-item
                    titulo="Administrador responsable"
                    subtitulo="Administrador que realizó el reinicio"
                />
            </div>

        </ResumenExpand>
        <!-- Formulario del reporte -->
        <form @submit.prevent="exportMeetings">
            <div class="row col-12">
                <div class="col-6">
                    <b-form-text text-variant="muted">Fecha inicial</b-form-text>
                    <div class="input-group">
                        <b-form-datepicker
                            v-model="start"
                            button-only
                            button-variant="light"
                            locale="es-PE"
                            aria-controls="date-start"
                            today-button
                            label-today-button="Hoy"
                            reset-button
                            label-reset-button="Reiniciar"
                            selected-variant="danger"
                        ></b-form-datepicker>
                        <input
                            type="date"
                            autocomplete="off"
                            class="datepicker form-control hasDatepicker"
                            v-model="start"
                        />
                    </div>

                </div>
                <div class="col-6">
                    <b-form-text text-variant="muted">Fecha final</b-form-text>
                    <div class="input-group">
                        <b-form-datepicker
                            v-model="end"
                            button-only
                            button-variant="light"
                            locale="es-PE"
                            aria-controls="date-start"
                            today-button
                            label-today-button="Hoy"
                            reset-button
                            label-reset-button="Reiniciar"
                            selected-variant="danger"
                        ></b-form-datepicker>
                        <input
                            type="date"
                            autocomplete="off"
                            class="datepicker form-control hasDatepicker"
                            v-model="end"
                        />
                    </div>

                </div>
            </div>
            <div class="col-6 filter-container">
                <button type="submit" class="btn btn-md btn-primary btn-block text-light">
                    <i class="fas fa-download"></i>
                    <span>Descargar</span>
                </button>
            </div>
        </form>
    </v-main>
</template>

<script>
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";

export default {
    components: {ResumenExpand, ListItem},
    props: {
        Admins: Array,
        API_REPORTES: ""
    },
    data() {
        return {
            Tipos: [],
            admin: "",
            tipo: "",
            start: "",
            end: ""
            // API_URL: process.env.MIX_API_REPORTES,
        };
    },
    methods: {
        exportMeetings() {
            let vue = this
            vue.queryStatus("reportes", "descargar_reporte_meetings");

            const start = this.start
            const end = this.end

            let res = {};
            res.data = {}
            res.data.excludeBaseUrl = true
            res.data.ruta_descarga = `/aulas-virtuales/export-general-report?starts_at=${start}&finishes_at=${end}`
            res.data.new_name = this.generateFilename(
                'Meetings',
                ''
            )
            res.data.selectedFilters = {
                'Fecha inicial': this.start,
                'Fecha final': this.end
            }
            this.$emit('emitir-reporte', res)
        },
    }
};
</script>

<style></style>
