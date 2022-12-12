<template>
    <v-row>
        <v-col cols="12" md="5" height="200px">
            <v-card-title>Descripción:</v-card-title>
            <v-card-text class="py-0">
                <!-- Se crea o actualiza los datos de usuarios según el valor de la columna de acción. -->
                Se crea o actualiza a los usuarios según los datos indicados en el excel.
            </v-card-text>
            <v-card-title>Puntos a tomar en cuenta:</v-card-title>
            <v-card-text>
                <ul>
                    <li class="mt-2">
                        <b>La cantidad máxima de filas por excel es de 2500.</b>
                    </li>
                    <li class="mt-2">
                        <b>Las columnas número de teléfono y email son opcionales.</b>
                    </li>
                    <li class="mt-2">
                        <b>Colocar en la columna de "Estado" los términos Active o Inactive. (editado)</b>
                    </li>
                    <li class="mt-2">
                        <b>Para los campos de fechas se pueden usar estos dos formatos (yyyy/mm/dd) o (dd/mm/yyyy).</b>
                    </li>
                    <li class="mt-2">
                        Colocar en la columna de "Módulo" los módulos existentes.
                    </li>
                </ul>
            </v-card-text>
        </v-col>
        <v-col cols="12" md="7" class="d-flex flex-column justify-content-center">
            <v-row class="d-flex justify-content-center my-2">
                <vuedropzone @emitir-archivo="cambio_archivo" @emitir-alerta="enviar_alerta"/>
            </v-row>
            <v-row class="d-flex justify-content-center">
                <v-card-actions>
                    <v-btn color="success" @click="enviar_archivo()">Subir</v-btn>
                </v-card-actions>
            </v-row>
        </v-col>
    </v-row>
</template>
<script>
const percentLoader = document.getElementById('percentLoader');
import vuedropzone from "./../dropzone.vue";

export default {
    props: ['q_error', 'number_socket'],
    components: {vuedropzone},
    data() {
        return {
            archivo: null,
        }
    },
    methods: {
        cambio_archivo(res) {
            this.archivo = res;
        },
        async enviar_archivo() {
            let validar_data = this.validar_data();
            if (validar_data) {
                let data = new FormData();
                this.showLoader();
                data.append("file", this.archivo);
                // console.log(data);
                data.append("number_socket", this.number_socket || null);
                percentLoader.innerHTML = ``;
                await axios.post('/masivos/create-update-users', data).then((res) => {
                    const data = res.data.data;
                    if (data.errores.length > 0) {
                        this.$emit("download-excel-observations", {
                                errores: data.errores,
                                headers: data.headers
                            }
                        );
                    }
                    const message = ` <ul>
                            <li>${data.message}</li>
                            <li>Cantidad de usuarios creados/actualizados: ${data.datos_procesados || 0}</li>
                            <li>Cantidad de usuarios con observaciones: ${data.errores.length || 0}</li>
                        </ul>`
                    this.hideLoader();
                    this.enviar_alerta(message);
                }).catch(err => {
                    if (err.response.data.message === 'Has superado el límite de usuarios activos.') {
                        this.mostrarModalLimiteUsuarios(err.response.data.data);
                    }
                    this.hideLoader();
                    this.enviar_alerta(err.response.data.message);
                });
            }
        },
        mostrarModalLimiteUsuarios(data) {
            this.$emit("show-modal-limit-allowed-users", data);
        },
        enviar_alerta(info) {
            this.$emit("emitir-alert", info);
        },
        validar_data() {
            if (!this.archivo) {
                this.enviar_alerta('No ha seleccionado ningún archivo');
                return false;
            }
            return true;
        },
        descargar_reporte() {
            window.open('/masivo/reporte_errores/carreras').attr("href");
        }
    }
}
</script>
<style>
.v-input__slot {
    display: flex;
    align-items: initial !important;
}
</style>
