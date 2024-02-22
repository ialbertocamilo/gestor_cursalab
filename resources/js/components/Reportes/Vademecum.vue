<template>
    <v-main>
        <!-- Resumen del reporte -->
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el registro de visitas a los documentos.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />

            <!-- this only for FP -->
            <div v-show="workspaceId === 25">
                <list-item
                    titulo="Grupo sistema"
                    subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
                />
                <list-item titulo="Grupo" subtitulo="Grupo al que pertenece el usuario" />
                <list-item titulo="Botica" subtitulo="Botica en la que se ubica el usuario" />
            </div>
            <!-- this only for FP -->

            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />

            <!-- this only for FP -->
            <div v-show="workspaceId === 25">
                <list-item titulo="Carrera (Usuario)" subtitulo="Carrera actual en la que se encuentra" />
            </div>
            <!-- this only for FP -->

            <list-item titulo="Documento" subtitulo="Nombre del documento (SCORM)" />
            <list-item
                titulo="Visitas"
                subtitulo="Cantidad de veces que el usuario visualiza un documento"
            />
            <list-item
                titulo="Última visita"
                subtitulo="Fecha de la última visita realizada al documento"
            />
        </ResumenExpand>
        <!-- Formulario del reporte -->
        <form class="row col-md-8 col-xl-5"
              @submit.prevent="generateReport">
            <!-- Grupos -->
            <div class="col-12">
                <b-form-text text-variant="muted">Documento</b-form-text>
                <!--<v-select
                    attach
                    solo
                    chips
                    clearable
                    multiple
                    hide-details="false"
                    v-model="vademecumSelected"
                    :items="VademecumList"
                    item-value="id"
                    item-text="nombre"
                    label="Selecciona uno de la lista"
                    :background-color="!vademecumSelected ? '' : 'light-blue lighten-5'"
                ></v-select>-->

                <v-select
                    attach
                    solo
                    chips
                    clearable
                    multiple
                    hide-details="false"
                    v-model="vademecumSelected"
                    :items="vademecumList"
                    item-value="id"
                    item-text="name"
                    label="Selecciona uno de la lista"
                    :background-color="!vademecumSelected ? '' : 'light-blue lighten-5'"
                ></v-select>

            </div>

            <div class="col-sm-12 mb-3 mt-4">
                <div class="col-sm-8 pl-0">
                    <button type="submit" class="btn btn-md btn-primary btn-block text-light">
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

export default {
    components: { ResumenExpand, ListItem },
    props: {
        workspaceId: { type: Number, required: true },
        adminId: { type: Number, required: true },
        vademecumList: { type: Array },
        reportsBaseUrl: { type: String , required: true }
    },
    data() {
        return {
            reportType: 'vademecum',
            vademecumSelected: []
        };
    },
    methods: {
        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.ExportarVademecum, type: vue.reportType})
        },
        async ExportarVademecum(reportName) {

            this.$emit('reportStarted', {})
            const filtersDescriptions = {
                'Vademecum' : this.generateNamesArray(this.vademecumList, this.vademecumSelected),
            }

            let urlReport = `${this.reportsBaseUrl}/exportar/${this.reportType}`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        vademecumsSelected: this.vademecumSelected,
                        adminId: this.adminId,
                        reportName,
                        filtersDescriptions,

                    }
                })
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_vademecum");
                }, 500);
            } catch (ex) {
                console.log(ex.message)
            }
        }
    },
    created() {
        // this.vademecumSelected = this.VademecumList.map((el) => el.id);
    }
};
</script>

<style></style>
