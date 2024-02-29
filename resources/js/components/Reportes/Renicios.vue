<template>
    <v-main>
        <!-- Resumen del reporte -->
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el registro de reinicios de intentos de evaluación realizados por los
                administradores a los usuarios.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item titulo="Documento, Apellidos y nombres" subtitulo="Datos personales" />
            <list-item titulo="Carrera (Usuario)" subtitulo="Carrera actual en la que se encuentra" />
            <list-item titulo="Ciclo actual (Usuario)" subtitulo="Ciclo actual en la que se encuentra" />
            <list-item titulo="Tipo reinicio" subtitulo="Tipo de reinicio realizado (al tema o curso)" />
            <list-item titulo="Curso" subtitulo="Nombre del curso" />
            <list-item titulo="Tema" subtitulo="Nombre del tema" />
            <list-item
                titulo="Reinicios"
                subtitulo="Cantidad de reinicios realizados (Por tipo de reinicio)"
            />
            <list-item titulo="Fecha" subtitulo="Fecha en la que se realizó el reinicio" />
            <list-item
                titulo="Administrador responsable"
                subtitulo="Administrador que realizó el reinicio"
            />
        </ResumenExpand>
        <!-- Formulario del reporte -->
        <form @submit.prevent="generateReport"
              class="row col-xl-10 col-sm-12">
            <!-- Admins -->
            <div class="col-sm-6 mb-2">
                  <DefaultAutocomplete
                    dense
                    v-model="admin"
                    :items="admins"
                    label="Administrador"
                    item-text="name"
                    item-value="id"
                    multiple
                    :showSelectAll="false"
                    placeholder="Seleccione los administradores"
                    @onChange=""
                    :maxValuesSelected="5"
                />
            </div>
            <div class="col-6"></div>
           <div class="col-sm-6 mb-2">
                <small class="form-text text-muted">Tipo</small>
                <select v-model="tipo" disabled readonly class="form-control">
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in tipos"
                            :key="index"
                            :value="item.id">
                        {{ item.name }}
                    </option>
                </select>

            </div>
            <div class="col-sm-6 mb-2">
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
            <div class="col-sm-6 mb-2">
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

            <div class="col-sm-12 mb-2 mt-4">
                <div class="col-sm-4 pl-0 mt-5">
                    <button type="submit" class="btn btn-md btn-primary btn-block">
                        <i class="fas fa-download"></i> <span>Generar reporte</span>
                    </button>
                </div>
            </div>
        </form>
    </v-main>
</template>

<script>

import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";

export default {
    components: { ResumenExpand, ListItem },
    props: {
        workspaceId: 0,
        adminId: 0,
        admins: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {
            reportType: 'reinicios',
            tipos: [ {id:'por_tema', name:'Reinicios por temas'},
                     {id:'por_curso', name:'Reinicios por cursos'},
                     {id:'total', name:'Reinicios totales'} ],
            admin: [],
            tipo: "por_tema",
            start: "",
            end: ""
            // API_URL: process.env.MIX_API_REPORTES,
        };
    },
    methods: {
        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.exportRenicios, type: vue.reportType})
        },
        async exportRenicios(reportName) {

            let UFC = this.$refs.EstadoFiltroComponent;

            this.$emit('reportStarted', {})
            const filtersDescriptions = {
                "Administradores": this.generateNamesArray(this.admins, this.admin),
                'Fecha inicial': this.start,
                'Fecha final': this.end
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
                        admin: this.admin,
                        tipo: this.tipo,
                        start: this.start,
                        end: this.end
                    }
                })
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_reinicios");
                }, 500);

            } catch (ex) {
                console.log(ex.message)
            }
        }
    }
};
</script>

<style></style>
