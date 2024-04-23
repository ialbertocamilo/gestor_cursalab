<template>
    <v-row>
        <v-col cols="12" md="5" height="200px" class="mt-100">
            <v-card-text
                v-show="process.id === 1"
                class="py-0">
                <!-- Se crea o actualiza los datos de usuarios según el valor de la columna de acción. -->
                Se crea o actualiza los usuarios según los datos indicados en el Excel.
            </v-card-text>

            <v-card-text
                v-show="process.id === 6"
                class="py-0">
                <!-- Se crea o actualiza los datos de usuarios según el valor de la columna de acción. -->
                Se actualiza los usuarios según los datos indicados en el Excel.
            </v-card-text>

            <v-card-title class="tit">Instructivo:</v-card-title>
            <v-card-text class="instructivo">
                <ul>
                    <li class="mt-2">
                        La máxima cantidad de filas por Excel es de <b>500</b>.
                    </li>
                    <li class="mt-2">
                        Las columnas <b>“Número de Teléfono”</b> y <b>“Email”</b> son opcionales.
                    </li>
                    <li class="mt-2">
                        Colocar en la columna de <b>Estado</b> los términos <b>Active</b> e <b>Inactive</b>.
                    </li>
                    <li class="mt-2">
                        Para los campos de las fechas se puede usar dos formatos: <b>yyyy / mm / dd</b> o <b>dd / mm / yyyy</b>.
                    </li>
                    <li class="mt-2">
                        Colocar en la columna <b>Módulo</b> los módulos existentes.
                    </li>
                </ul>
            </v-card-text>
            <div class="btn_download_template">
                <a :href="url_template" class="btn btn-primary" download><i
                    class="fas fa-download ml-2"></i> Descargar plantilla</a>
            </div>
        </v-col>
        <v-col cols="12" md="2" height="200px" class="d-flex justify-content-center">
            <div class="separador-v"></div>
        </v-col>
        <v-col cols="12" md="5" class="">
            <v-row class="d-flex justify-content-center my-2 drop_mas">
                <vuedropzone
                    :ref="myVueDropzone"
                    @emitir-archivo="cambio_archivo"
                    @emitir-alerta="enviar_alerta"
                    :error_file="error_file"
                    :error_text="error_text"
                    :success_file="success_file"
                    :success_text="success_text"
                />
            </v-row>
            <v-row class="d-flex justify-content-center align-items-start">
                <v-card-actions>
                    <v-btn v-if="error_file||success_file" color="primary" @click="nuevo_archivo()" class="btn_conf">Subir otro archivo</v-btn>
                    <v-btn v-else color="primary" @click="enviar_archivo()" :disabled="!(archivo != null)" class="btn_conf">Confirmar</v-btn>
                </v-card-actions>
            </v-row>
        </v-col>
    </v-row>
</template>
<script>
const percentLoader = document.getElementById('percentLoader');
import vuedropzone from "./../dropzone.vue";

export default {
    props: ['q_error', 'number_socket', 'url_template', 'process'],
    components: {vuedropzone},
    data() {
        return {
            archivo: null,
            error_file: false,
            error_text: '',
            success_file: false,
            success_text: '',
            myVueDropzone: 'myVueDropzone',
        }
    },
    methods: {
        cambio_archivo(res) {
            this.archivo = res;
        },
        nuevo_archivo() {
            let vue = this
            if (vue.$refs.myVueDropzone)
                vue.$refs.myVueDropzone.limpiarArchivo()
            this.archivo = null;
            this.error_file = false;
            this.success_file = false;
        },
        async enviar_archivo() {
            let vue = this;
            let validar_data = this.validar_data();
            if (validar_data) {
                let data = new FormData();
                this.showLoader();
                data.append("file", this.archivo);
                data.append("process", JSON.stringify(this.process)); // == data section-upload ==
                // console.log(data);
                data.append("number_socket", this.number_socket || null);
                percentLoader.innerHTML = ``;

                let url = '/procesos-masivos/create-update-users';
                if (vue.process.id === 6) {
                    url = '/procesos-masivos/update-users';
                }

                await axios.post(url, data).then((res) => {
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
                    vue.queryStatus("subida_masiva", "subir_archivo");
                    vue.success_file = true
                    vue.success_text = message
                    this.hideLoader();
                }).catch(err => {
                    if (err.response.data.message === 'Has superado el límite de usuarios activos.') {
                        this.mostrarModalLimiteUsuarios(err.response.data.data);
                    }
                    vue.error_file = true
                    vue.error_text = err.response.data.message
                    this.hideLoader();
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
<style lang="scss">
.v-input__slot {
    display: flex;
    align-items: initial !important;
}
.separador-v{
    border-left: 1px solid #94DDDB;
}
.tit,
.instructivo .v-card__text ul li,
.v-card__text{
    font-family: "Nunito", sans-serif;
}
.tit {
    font-size: 15px !important;
    font-weight: 700;
}
.instructivo .v-card__text ul li {
    font-size: 14px;
    line-height: 20px;
}
.btn_download_template {
    margin-left: 15px;
}
.btn_download_template .btn-primary {
    background-color: #fff !important;
    color: #5458ea !important;
    border: 1px solid #5458ea !important;
    font-family: "Nunito", sans-serif;
    font-size: 14px;
}
.mt-100{
    margin-top: 100px !important;
}
button.btn_conf {
    min-width: 172px !important;
}
button.btn_conf span.v-btn__content {
    font-family: "Nunito", sans-serif;
    font-size: 14px;
    font-weight: 400;
}
.drop_mas .dropzone {
    min-height: 240px;
    display: grid;
    align-items: center;
}
</style>
