<template>
    <v-main>
        <!-- Resumen del reporte -->

        <ResumenExpand titulo="Resumen del reporte">
            <template v-slot:resumen>
                En este reporte se obtiene la información base de todos los usuarios matriculados en la plataforma.
            </template>
            <list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
            <list-item
                titulo="Grupo sistema"
                subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
            />
            <list-item titulo="Área" subtitulo="Área al que pertenece el usuario" />
            <list-item titulo="Sede" subtitulo="Sede en la que se ubica el usuario" />
            <list-item titulo="Documento, Apellidos y Nombres" subtitulo="Datos personales" />
            <list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
            <list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
            <list-item
                titulo="Estado"
                subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
            />
        </ResumenExpand>

        <!-- Formulario del reporte -->
        <form class="row"
              @submit.prevent="exportUsuariosDW">
            <div class="col-6">
                <b-form-text text-variant="muted">Módulo</b-form-text>
                <select class="form-control"
                        v-model="modulo">
                    <option value>- [Todos] -</option>
                    <option v-for="(item, index) in modules"
                            :key="index"
                            :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>
            <div class="col-8">
                <EstadoFiltro ref="EstadoFiltroComponent"
                              @emitir-cambio="" />
            </div>
            <v-divider class="col-12 p-0 mt-3"></v-divider>
            <div class="col-sm-12 mb-3 mt-4">
                <div class="col-sm-8 pl-0">
                    <button type="submit"
                            class="btn btn-md btn-primary btn-block text-light">
                        <i class="fas fa-download"></i>
                        <span>Descargar</span>
                    </button>
                </div>
            </div>
        </form>
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
        modules: Array,
        reportsBaseUrl: ''
    },
    data() {
        return {

            modulo: ''
        };
    },
    methods: {
        async exportUsuariosDW() {

            // show loading spinner

            this.showLoader()

            let UFC = this.$refs.EstadoFiltroComponent;

            // Perform request to generate report

            let urlReport = `${this.$props.reportsBaseUrl}/exportar/usuarios`

            try {
                let response = await axios({
                    url: urlReport,
                    method: 'post',
                    data: {
                        workspaceId: this.workspaceId,
                        modulos: this.modulo ? [this.modulo] : [],
                        UsuariosActivos: UFC.UsuariosActivos,
                        UsuariosInactivos: UFC.UsuariosInactivos
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
        }
    }
};
</script>

<style></style>
