<template>
    <!-- DEFAULT DIALOG -->
    <default-dialog :options="options"
                   :width="width"
                   :showCardActions="false"
                   :noPaddingCardText="true"
                   @onConfirm="confirmModal"
                   @onCancel="closeModal"
    >
        <template v-slot:content>

            <v-row justify="space-around">
                
                <DefaultErrorsImport :errors="errors" class="mt-3" />

                <v-col cols="12" class="d-flex justify-content-center pb-0">
                    <!-- DEFAULT STEPPER -->
                    <default-stepper :stepsLabel='stepsLabel' @onCancel="closeModal" :modelStepper="modelStepper">
                        <template v-slot:content-step-1>
                            <v-card class="mb-1" elevation="0">
                                    <v-card-text class="pb-0">
                                        <v-row>
                                            <v-col cols="12" md="12" lg="12">
                                                <v-alert type="info" elevation="0" outlined>
                                                    <p class="m-0">
                                                        Descarga la plantilla para rellenarla con la data que deseas subir a la plataforma.
                                                    </p>
                                                </v-alert>
                                            </v-col>
                                            <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                                                <DefaultButton
                                                    label="Descargar plantilla"
                                                    @click="download_plantilla"
                                                    icon="mdi mdi-file-download"
                                                />
                                            </v-col>
                                        </v-row>
                                    </v-card-text>
                                    <v-row>
                                        <v-col cols="12" md="12" lg="12" class="py-0 my-0">
                                            <hr>
                                        </v-col>
                                        <v-col cols="12" md="12" lg="12" class="pb-0">
                                            <DefaultModalActionButton
                                                :cancel-label="'Cancelar'"
                                                :confirm-label="'Continuar'"
                                                @cancel="closeModal"
                                                @confirm="modelStepper++"/>
                                        </v-col>
                                    </v-row>
                                </v-card>
                        </template>
                        <template v-slot:content-step-2>
                             <div class="d-flex justify-center">
                                <v-card class="mb-5" elevation="0">
                                    <v-row>
                                        <v-col cols="12" md="12" lg="12" class="pb-0 mb-0">
                                            <v-alert type="info" elevation="0" outlined>
                                                <p class="m-0">
                                                    Sube el archivo con las columnas indicadas en la plantilla. Asegúrate de que
                                                    todas las casillas contengan la información correcta.
                                                </p>
                                            </v-alert>
                                        </v-col>
                                    </v-row>
                                    <v-row class="d-flex justify-content-center my-2">
                                        <div style="border-style: dashed;border-width: 1px;">
                                            <dropzone
                                                ref="dropzone"
                                                @emitir-archivo="cambio_archivo"
                                            />
                                        </div>
                                    </v-row>
                                    <v-row>
                                        <v-col cols="12" md="12" lg="12" class="d-flex justify-center">
                                            <default-button
                                                text
                                                label="Atras"
                                                @click="modelStepper--"
                                            >
                                            </default-button>
                                            <default-button
                                                label="Subir"
                                                @click="subirArchivo"
                                                :disabled="!archivo"
                                            >
                                            </default-button>
                                        </v-col>
                                    </v-row>
                                </v-card>
                            </div>
                        </template>
                        <template v-slot:content-step-3>
                            <v-card class="mb-5" elevation="0">
                                <v-slide-x-reverse-transition mode="out-in">
                                    <div style="width:40vw;" class="d-flex justify-center flex-row align-center"
                                        v-if="progress_upload === 'cargando'">
                                        <v-progress-circular
                                            class="mr-3"
                                            indeterminate
                                            color="primary"
                                            :size="50"
                                        >
                                        </v-progress-circular>
                                        <em style="font-size: 1.2rem"> Subiendo archivo ...</em>
                                    </div>
                                    <div class="d-flex justify-center flex-row align-center"
                                        v-else-if="progress_upload === 'ok'">
                                        <v-icon style="font-size: 50px !important;" class="mr-3" color="success"
                                                v-text="'mdi-check-outline'"></v-icon>
                                        <em style="font-size: 1.2rem">Los datos del archivo se han subido correctamente.</em>
                                    </div>
                                    <div class="d-flex justify-center flex-row align-center"
                                        v-else-if="progress_upload === 'error'">
                                        <v-icon style="font-size: 50px !important;" class="mr-3" color="red"
                                                v-text="'mdi-close-outline'"></v-icon>
                                        <em style="font-size: 1.2rem" v-text="error_message"></em>
                                    </div>
                                </v-slide-x-reverse-transition>
                                <v-expand-transition  v-if="progress_upload != 'cargando'">
                                    <v-card elevation="0" v-show="errores.length>0">
                                        <v-card-text>
                                            <v-row>
                                                <v-col cols-="12" md="12" lg="12"
                                                    class="d-flex justify-content-center align-center flex-column">
                                                    <em style="color:red">Sin embargo se han encontrado observaciones que puedes descargar en el
                                                        siguiente archivo:</em>
                                                    <v-btn class="my-3" @click="descargarErrores" color="primary">
                                                        <v-icon class="mr-1" style="font-size: 1.5rem !important;">
                                                            mdi mdi-file-download
                                                        </v-icon>
                                                        Descargar archivo
                                                    </v-btn>
                                                    <div class="messages_stepper_upload">
                                                        <ul>
                                                            <li class="m-0">
                                                                La data con observaciones no se subió a la plataforma
                                                            </li>
                                                            <li class="m-0">
                                                                Puedes corregir o actualizar la data del archivo con observaciones y
                                                                subirla realizando otro proceso de subida masiva
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </v-col>
                                            </v-row>
                                        </v-card-text>
                                    </v-card>
                                </v-expand-transition>
                            </v-card>
                            <v-row no-gutters>
                                <v-col cols="12" md="12" lg="12" class="p-0">
                                    <DefaultModalActionButton
                                        :hideConfirmBtn="true"
                                        :cancel-label="'Cerrar'"
                                        @cancel="closeModal"
                                    />
                                </v-col>
                            </v-row>
                        </template>
                    </default-stepper>
                </v-col>
            </v-row>
        </template>
    </default-dialog>
</template>
<script>

import DefaultErrorsImport from "../../components/globals/DefaultErrorsImport";
import dropzone from "../../components/SubidaMasiva/dropzone";
import XLSX from "xlsx";

const fields = ['nombre'];
const file_fields = [];

export default {
    components: {
        dropzone,
        DefaultErrorsImport
    },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            resourceDefault: {
                id: null,
                nombre: '',
            },
            resource: {},
            selects: {
                modules: [],
                destinos: [],
            },
            rules: {
                nombre: this.getRules(['required', 'max:100', 'text']),
            },
            stepsLabel:['Descargar Plantilla','Subir Plantilla','Verificación'],
            modelStepper:1,
            urlPlantillaCalificada: '/templates/Plantilla-Examen-Calificada.xlsx',
            urlPlantillaAbierta: '/templates/Plantilla-Examen-Abierta.xlsx',
            urlSubida: this.options.base_endpoint,
            archivo: null,
            progress_upload: null,
            errores: [],
            abc: [
                "A","B","C","D","E","F","G","H","I","J","K","L","M","N",
                "O","P","Q","R","S","U","V","W","X","Y","Z"
            ],
            errors: [],
            error_message:'Error al subir el archivo',
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.modelStepper=1;
            vue.archivo= null;
            vue.$refs.dropzone.limpiarArchivo()
            vue.errors = []
            // vue.$refs.anuncioForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            // const validateForm = vue.validateForm('anuncioForm')
            // const edit = vue.options.action === 'edit'

            // let base = `${vue.options.base_endpoint}`
            // let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            // let method = edit ? 'PUT' : 'POST';

            // // if (validateForm && validateSelectedModules) {
            // if (validateForm ) {

            //     let formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);

            //     vue.$http.post(url, formData)
            //         .then(({data}) => {
            //             vue.closeModal()
            //             vue.showAlert(data.data.msg)
            //             vue.$emit('onConfirm')
            //             this.hideLoader()
            //         })
            // }

            // this.hideLoader()
        },
        resetSelects() {
        },
        async loadData() {


        },
        loadSelects() {
            let vue = this
        },
        download_plantilla(){

            // Check topic evaluation type

            let vue = this
            let url = this.options.topicUrl

            vue.$http.get(url)
                .then(({data}) => {

                    if (!data.data.tema.evaluation_type) return;

                    if (data.data.tema.evaluation_type.code === 'qualified') {
                        location.href = this.urlPlantillaCalificada
                    } else {
                        location.href = this.urlPlantillaAbierta
                    }
                })
        },
        async subirArchivo() {
            let vue = this
            vue.modelStepper = 3;
            vue.progress_upload = 'cargando'
            let data = new FormData();
            data.append("archivo", vue.archivo);
            vue.errores = [];
            await axios
                .post(vue.urlSubida, data)
                .then((res) => {

                    // setTimeout(function () {
                        // vue.showAlert(res.data.data.message, res.data.data.status)
                        if(res.data.data.message == 'invalid_template'){
                            vue.progress_upload = 'error'
                            vue.error_message = 'El archivo Excel no es válido. Por favor, asegúrese de revisar la plantilla antes de intentar volver a cargarlo.';
                            return ;
                        }
                        if (res.data.data.errors && res.data.data.errors.length > 0) {
                            vue.errors = res.data.data.errors
                            vue.progress_upload = 'error'
                        } else {

                            if (res.data.info) {
                                if (res.data.info.data_no_procesada.length > 0) {
                                    vue.uploadErrors = true;
                                    vue.errores = res.data.info.data_no_procesada
                                    vue.progress_upload = 'error'
                                } else {

                                    vue.progress_upload = 'ok'
                                }
                            } else {
                                
                                vue.progress_upload = 'ok'
                            }
                        }


                    // }, 1500)

                    vue.archivo = null;
                    vue.$refs.dropzone.limpiarArchivo()
                    // setTimeout( () => {
                    //     location.reload();
                    // }, 1000)
                })
                .catch((error) => {

                    vue.progress_upload = 'error'
                    vue.archivo = null;
                    if (error && error.errors)
                        vue.errors = error.errors
                });
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
        descargarErrores() {
            let vue = this
            let headers = ["DNI SUPERVISOR", "ÁREA (ej: Grupo 1)", "MENSAJE"];
            let values = ['dni','grupo','msg'];
            vue.descargarExcelDatosNoProcesados(
                headers,
                values,
                vue.errores,
                "Data no procesada"
            );
        },
        cambio_archivo(res) {
            this.archivo = res;
        },
    }
}
</script>
