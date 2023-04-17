<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
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
                            @click="downloadTemplate()"/>
                </div>
                <div class="text-center mb-4">
                    <span class="font-weight-black">Sube el archivo</span> con las columnas indicadas en la plantilla. Asegúrate de que todas las casillas
                    contengan la información correcta. <br>
                </div>
                <div class="d-flex justify-center">
                    <vuedropzone
                        :ref="myVueDropzone"
                        @emitir-archivo="changeFile"
                        @emitir-alerta="enviar_alerta"
                        :error_file="dropzone_file.error_file"
                        :error_text="dropzone_file.error_text"
                        :success_file="dropzone_file.success_file"
                        :success_text="dropzone_file.success_text"
                        title=""
                        subtitle='Sube o arrastra el archivo <br><span class="font-weight-black">excel</span> con los datos'
                    />
                </div>
            </v-card-text>
        </template>
    </DefaultDialog>
</template>
<script>
import vuedropzone from "../../SubidaMasiva/dropzone.vue";
export default {
    components:{vuedropzone},
    props: {
        template_url:{
            type: String,
            required: true
        },
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
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.$refs.cuentaZoomForm.resetValidation()
        },
        changeFile(res) {
            this.dropzone_file.file = res;
        },
        enviar_alerta(info) {
            this.$emit("emitir-alert", info);
        },
        nuevo_archivo() {
            let vue = this
            if (vue.$refs.myVueDropzone)
                vue.$refs.myVueDropzone.limpiarArchivo()
            this.dropzone_file.file = null;
            this.dropzone_file.error_file = false;
            this.dropzone_file.success_file = false;
        },
        confirmModal() {
            // let vue = this
            // this.showLoader()

            // const validateForm = vue.validateForm('cuentaZoomForm')
            // const edit = vue.options.action === 'edit'

            // let base = `${vue.options.base_endpoint}`
            // let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            // let method = edit ? 'PUT' : 'POST';

            // // if (validateForm && validateSelectedModules) {
            // if (validateForm ) {

            //     let formData = vue.getMultipartFormData(method, vue.resource, fields);

            //     vue.$http.post(url, formData)
            //         .then(({data}) => {
            //             vue.closeModal()
            //             vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
            //         })
            // }

            // this.hideLoader()
        },
        resetSelects() {
            // Selects independientes
        },
        async loadData(resource) {
        },
        loadSelects() {
        },
        downloadTemplate(){
            let vue = this;
            location.href = vue.template_url;
        }
    }
}
</script>
