<template>
    <div>
        <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                <v-card-text>
                    <div class="text-center">
                        <span class="font-weight-black">Descarga</span> la plantilla para rellenarla con la data que deseas subir a la plataforma
                    </div>
                <div class="text-center my-4">
                    <DefaultButton
                            label="Descargar plantilla"
                            icon="mdi-download"
                            :outlined="true"
                            @click="openLink(vue.template_url)"/>
                </div>
                <div class="text-center mb-4">
                    <span class="font-weight-black">Sube el archivo</span> con las columnas indicadas en la plantilla. Asegúrate de que todas las casillas
                    contengan la información correcta. <br>
                </div>
                <div class="d-flex justify-center">
                    <vuedropzone
                        :ref="myVueDropzone"
                        @emitir-archivo="changeFile"
                        @emitir-archivo-completo="changeFileComplete"
                        @emitir-alerta="enviar_alerta"
                        @emitir-download-file="descargarErrores()"
                        :error_file="dropzone_file.error_file"
                        :error_text="dropzone_file.error_text"
                        :success_file="dropzone_file.success_file"
                        :success_text="dropzone_file.success_text"
                        :hasObservation = "dropzone_file.hasObservation"
                        title=""
                        subtitle='Sube o arrastra el archivo <br><span class="font-weight-black">excel</span> con los datos'
                    />
                </div>
            </v-card-text>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
import vuedropzone from "../../components/SubidaMasiva/dropzone.vue";

export default {
    components:{vuedropzone},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            myVueDropzone: 'myVueDropzone',
            dropzone_file:{
                file:null,
                error_file:false,
                success_file:false
            },
            errores: [],
            template_url:'/templates/Plantilla Checklist.xlsx'
        };
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.$emit('onCancel');
        }
        ,
        resetValidation() {
            let vue = this
        }
        ,
        async confirmModal() {
            let vue = this;
            if(vue.errores.length > 0 || vue.options.confirmLabel == 'Subir otro archivo')
                vue.nuevo_archivo()

            if(vue.dropzone_file.file != null) {
                vue.showLoader()
                let data = new FormData();
                data.append("archivo", vue.dropzone_file.file);
                await axios
                    .post(`${vue.options.base_endpoint}/activity/upload-massive`, data)
                    .then(({data}) => {
                        vue.hideLoader();
                        vue.$emit('activities',data.data.activities);
                    })
                    .catch((err) => {
                        vue.hideLoader();
                        console.log(err);
                    });
            }else{
                vue.showAlert('Es necesario subir un archivo','warning');
            }
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {
        },
        async loadSelects() {
            let vue = this;
        },
        descargarErrores() {
            let vue = this
            // let headers = ["DNI ALUMNO", "MENSAJE", "NOMBRES Y APELLIDOS"];
            // let values = ["dni", "msg", "nombre"];
            // vue.descargarExcelDatosNoProcesados(
            //     headers,
            //     values,
            //     vue.errores,
            //     "Data no procesada"
            // );
        },
        enviar_alerta(info) {
            this.$emit("emitir-alert", info);
        },
        changeFile(res) {
            this.dropzone_file.file = res;
        },
        changeFileComplete(res) {
            this.dropzone_file.file = res;
        },
    }
}
</script>
