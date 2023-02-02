<template>
    <v-main>
        <!-- <div class="col-12">
            <h3>Reporte de los informes subidos por los usuarios</h3>
        </div> -->
        <!-- Resumen del reporte -->
        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                Descarga los archivos o links subidos por los usuarios a la plataforma.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />
            <list-item titulo="Carrera (Usuario)" subtitulo="Carrera actual en la que se encuentra" />
            <list-item titulo="Ciclo actual (Usuario)" subtitulo="Ciclo actual en la que se encuentra" />
            <list-item titulo="Cargo" subtitulo="Cargo que tiene asignado el usuario" />
            <list-item titulo="Link" subtitulo="Link adjuntado por el usuario" />
            <list-item titulo="Archivo" subtitulo="Archivo subido por el usuario" />
            <list-item titulo="Descripción" subtitulo="Descripción adjunta por el usuario" />
            <list-item titulo="Fecha de carga" subtitulo="Fecha en la que se adjunto el archivo" />
        </ResumenExpand>
        <!-- Formulario del reporte -->

        <!-- <div class="col-sm-12 mt-4">

      <button @click="descargarUsuarioUploads" class="btn btn-md btn-primary"><i class="fas fa-download"></i> <span>Descargar</span></button>
    </div> -->
        <v-divider class="col-12 m-0 p-0"></v-divider>
        <div class="col-6">
            <EstadoFiltro ref="EstadoFiltroComponent" />
        </div>
        <div class="px-4">
            <button
                @click="generateReport"
                class="btn btn-md btn-primary btn-block text-light col-5 col-md-4 py-2 mt-5"
            >
                <i class="fas fa-download"></i>
                <span>Generar reporte</span>
            </button>
        </div>
    </v-main>
</template>

<script>
import ListItem from "./partials/ListItem.vue"
import ResumenExpand from "./partials/ResumenExpand.vue"
import EstadoFiltro from "./partials/EstadoFiltro.vue"

export default {
    components: { EstadoFiltro, ResumenExpand, ListItem },
    props: {
        workspaceId: 0,
        adminId: 0,
        reportsBaseUrl: ''
    },
    methods: {
        generateReport() {
            const vue = this
            vue.$emit('generateReport', vue.descargarUsuarioUploads)
        },
        async descargarUsuarioUploads(reportName) {

            let UFC = this.$refs.EstadoFiltroComponent;

            this.$emit('reportStarted', {})
            const filtersDescriptions =  {
                "Usuarios activos" : this.yesOrNo(UFC.UsuariosActivos),
                "Usuarios inactivos" : this.yesOrNo(UFC.UsuariosInactivos),
            }

            // Get bucket base url

            const baseUrl = document.querySelector('meta[name=BUCKET_BASE_URL]')
                                    .getAttribute('content');

            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/user_uploads`
            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        adminId: this.adminId,
                        reportName,
                        filtersDescriptions,
                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos,
                        baseUrl: baseUrl
                    }
                })

            } catch (ex) {
                console.log(ex.message)
            }
        }
    }
};
</script>
<style></style>
