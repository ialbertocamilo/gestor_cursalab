<template>
    <v-main>
        <!-- Resumen del reporte -->
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga el registro de visitas a los títulos de la Videoteca.
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

            <list-item titulo="Videoteca" subtitulo="Nombre del título en la Videoteca" />
            <list-item
                titulo="Visitas"
                subtitulo="Cantidad de veces que el usuario visualiza un título de la Videoteca"
            />
            <list-item
                titulo="Última visita"
                subtitulo="Fecha de la última visita realizada al título de la Videoteca"
            />
        </ResumenExpand>
        <!-- Formulario del reporte -->
        <form class="row col-md-8 col-xl-5"
              @submit.prevent="generateReport">
            <!-- Grupos
            <div class="col-12">
                <b-form-text text-variant="muted">Videoteca</b-form-text>
                <v-select
                    attach
                    solo
                    chips
                    clearable
                    multiple
                    hide-details="false"
                    v-model="videotecaSelected"
                    :items="modules"
                    item-value="id"
                    item-text="name"
                    label="Selecciona uno de la lista"
                    :background-color="!videotecaSelected ? '' : 'light-blue lighten-5'"
                ></v-select>
            </div>-->

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
        workspaceId:{ type: Number, required: true },
        adminId:{ type: Number, required: true },
        reportsBaseUrl: { type: String, required: true }
    },
    data() {
      return {
          reportType: 'videoteca',
      }
    },
    methods: {
        generateReport() {
            const vue = this
            vue.$emit('generateReport', {callback: vue.ExportarVideoteca, type: vue.reportType})
        },
        async ExportarVideoteca(reportName) {


            this.$emit('reportStarted', {})


            const url = `${this.reportsBaseUrl}/exportar/${this.reportType}`
            try {
                let response = await axios({
                    url: url,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        adminId: this.adminId,
                        reportName,
                        filtersDescriptions: {}
                    }
                })
                const vue = this
                setTimeout(() => {
                    vue.queryStatus("reportes", "descargar_reporte_videoteca");
                }, 500);
            } catch (ex) {
                console.log(ex.message)
            }
        }
    },
    created() {
        //this.videotecaSelected = this.VideotecaList.map((el) => el.id);
        //this.videotecaSelected = this.modules.map((el) => el.id);
    }
};
</script>

<style></style>
