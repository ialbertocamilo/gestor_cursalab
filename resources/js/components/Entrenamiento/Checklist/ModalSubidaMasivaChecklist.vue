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
</template>
<script>
import XLSX from "xlsx";
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
            },
            errores: []
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.nuevo_archivo()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.$refs.cuentaZoomForm.resetValidation()
        },
        changeFile(res) {
            this.dropzone_file.file = res;
        },
        changeFileComplete(res) {
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
            vue.dropzone_file.hasObservation = false;
            vue.options.confirmLabel = "Confirmar"
            vue.errores = []
        },
        async confirmModal() {
            let vue = this
            if(vue.errores.length > 0 || vue.options.confirmLabel == 'Subir otro archivo')
                vue.nuevo_archivo()

            if(vue.dropzone_file.file != null) {
                vue.showLoader()
                vue.progress_upload = 'cargando'
                let data = new FormData();
                data.append("archivo", vue.dropzone_file.file);
                await axios
                    .post('/entrenamiento/checklists/import', data)
                    .then((res) => {
                        vue.dropzone_file.success_file = true;
                        if (res.data.info.data_no_procesada.length > 0) {
                            vue.dropzone_file.hasObservation = true;
                            vue.errores = res.data.info.data_no_procesada
                            vue.options.confirmLabel = "Subir otro archivo"
                        }
                        vue.options.confirmLabel = "Subir otro archivo"
                        vue.archivo = null;
                        vue.hideLoader()
                        vue.$emit('refreshTable')
                    })
                    .catch((err) => {
                        vue.hideLoader();
                        console.log(err);
                    });
            }
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
        },
        descargarErrores() {
            let vue = this
            let headers = ["DNI ALUMNO", "MENSAJE", "NOMBRES Y APELLIDOS"];
            let values = ["dni", "msg", "nombre"];
            vue.descargarExcelDatosNoProcesados(
                headers,
                values,
                vue.errores,
                "Data no procesada"
            );
        },
        descargarExcelDatosNoProcesados(headers, values, array, filename) {
            let data = XLSX.utils.json_to_sheet(array, {
                header: values
            });
            //Llenar cabeceras
            headers.forEach((element, index) => {
                const indice = `${this.abc[index]}1`;
                data[`${indice}`].v = element;
            });
            // Parsear la columna de dni 'A' a String 's'
            for (const indice in data) {
                if (indice.includes('A')) data[indice].t = 's'
            }
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, data, filename);
            XLSX.writeFile(workbook, `${filename}.xlsx`);
        },
    }
}
</script>
