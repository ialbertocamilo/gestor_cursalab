<template>
    <v-dialog v-model="value" :width="widthDialog"  @click:outside="closeModal">
        <v-stepper v-model="e1">
            <v-stepper-header>
                <v-stepper-step :complete="e1 > 1" step="1">Paso 1</v-stepper-step>
                <v-divider></v-divider>
                <v-stepper-step :complete="e1 > 2" step="2">Paso 2</v-stepper-step>
                <v-divider></v-divider>
                <v-stepper-step step="3">Paso 3</v-stepper-step>
            </v-stepper-header>
            <v-stepper-items>
                <v-stepper-content step="1" style="padding-bottom: 0 !important;">
                    <v-card class="mb-5" elevation="0">
                        <v-card-text class="pb-0">
                            <v-row>
                                <v-col cols="12" md="12" lg="12">
                                    <v-alert border="top" type="info" elevation="0" outlined>
                                        <li class="m-0">
                                            Descarga la plantilla para rellenarla con la data que deseas subir a la
                                            plataforma.
                                        </li>
                                    </v-alert>
                                </v-col>
                                <v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
                                    <v-btn :ripple="false" @click="descargarPlantilla" color="primary">
                                        <v-icon class="mr-1" style="font-size: 1.5rem !important;">
                                            mdi-file-download
                                        </v-icon>
                                        Descargar plantilla
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </v-card-text>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="py-0 my-0">
                                <hr>
                            </v-col>
                            <v-col cols="12" md="12" lg="12">
                                <v-btn text @click="closeModal"> Cancelar</v-btn>
                                <v-btn color="primary" @click="e1 = 2"> Continuar
                                    <v-icon class="ml-2" style="font-size: 1.5rem !important;">mdi-arrow-right-bold
                                    </v-icon>
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-stepper-content>
                <v-stepper-content step="2" style="padding-bottom: 0 !important;">
                    <v-card class="mb-5" elevation="0">
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="pb-0 mb-0">
                                <v-alert border="top" type="info" elevation="0" outlined>
                                    <li class="m-0">
                                        Sube el archivo con las columnas indicadas en la plantilla. Asegúrate de que
                                        todas las casillas contengan la información correcta.
                                    </li>
                                </v-alert>
                            </v-col>
                        </v-row>
                        <v-row class="d-flex justify-content-center my-2">
                            <dropzone
                                ref="dropzone"
                                @emitir-archivo="cambio_archivo"
                            />
                        </v-row>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="py-0 my-0">
                                <hr>
                            </v-col>
                            <v-col cols="12" md="12" lg="12">
                                <v-btn text @click="closeModal"> Cancelar</v-btn>
                                <v-btn color="primary" @click="subirArchivo" :disabled="!archivo">
                                    Subir
                                    <!--                                    <v-icon class="ml-1" style="font-size: 1.5rem !important;">
                                                                            mdi-upload
                                                                        </v-icon>-->
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-card>

                </v-stepper-content>
                <v-stepper-content step="3" style="padding-bottom: 0 !important;">
                    <v-card class="mb-5" elevation="0">
                        <v-slide-x-reverse-transition mode="out-in">
                            <div class="d-flex justify-center flex-row align-center"
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
                                <em style="font-size: 1.2rem">Error al subir el archivo</em>
                            </div>
                        </v-slide-x-reverse-transition>
                        <v-expand-transition>
                            <v-card elevation="0" v-show="errores.length>0">
                                <v-card-text>
                                    <v-row>
                                        <v-col cols-="12" md="12" lg="12"
                                               class="d-flex justify-content-center align-center flex-column">
                                            <em style="color:red">Sin embargo se han encontrado observaciones que puedes descargar en el
                                                siguiente archivo:</em>
                                            <v-btn class="my-3" @click="descargarErrores" color="primary">
                                                <v-icon class="mr-1" style="font-size: 1.5rem !important;">
                                                    mdi-download
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
                    <v-row>
                        <v-col cols="12" md="12" lg="12" class="py-0 my-0">
                            <hr>
                        </v-col>
                        <v-col cols="12" md="12" lg="12">
                            <v-btn text @click="closeModal()">
                                Cerrar
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-stepper-content>
            </v-stepper-items>
        </v-stepper>
    </v-dialog>
</template>

<script>
import dropzone from './dropzone'
import XLSX from "xlsx";

export default {
    components: {
        dropzone
    },
    props: {
        txtBtn: {
            type: String,
            default: 'Subida masiva',
            required: false
        },
        widthDialog: {
            type: Number,
            default: 900,
            required: false
        },
        value: {
            type: Boolean,
            required: false
        },
        urlPlantilla: {
            type: String,
            required: true,
        },
        urlSubida: {
            type: String,
            required: true,
        },
        indicaciones: {
            type: Array,
            required: false,
        },
        typeForm: {
            type: String,
            default: '',
            required: false,
        }
    },
    data() {
        return {
            e1: 1,
            archivo: null,
            progress_upload: null,
            errores: [],
            abc: [
                "A",
                "B",
                "C",
                "D",
                "E",
                "F",
                "G",
                "H",
                "I",
                "J",
                "K",
                "L",
                "M",
                "N",
                "O",
                "P",
                "Q",
                "R",
                "S",
                "U",
                "V",
                "W",
                "X",
                "Y",
                "Z"
            ]
        }
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.e1 = 1
            vue.archivo = null
            vue.progress_upload = null
            vue.errores = []
            vue.$refs.dropzone.limpiarArchivo()
            vue.$emit("onClose");
        },
        confirm() {
            let vue = this;
            vue.$emit("onConfirm");
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        descargarPlantilla() {
            let vue = this
            location.href = vue.urlPlantilla
        },
        cambio_archivo(res) {
            this.archivo = res;
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
        async subirArchivo() {
            let vue = this
            vue.e1 = 2;
            vue.progress_upload = 'cargando'
            let data = new FormData();
            data.append("archivo", vue.archivo);
            axios
                .post(vue.urlSubida, data)
                .then((res) => {
                    setTimeout(function () {
                        vue.progress_upload = 'ok'
                        if(vue.typeForm === 'asigna_alumnos'){
                            vue.queryStatus("checklist", "asignar_entrenador");
                        }
                        if (res.data.info.data_no_procesada.length > 0) {
                            vue.uploadErrors = true;
                            vue.errores = res.data.info.data_no_procesada
                        }
                    }, 1500)

                    vue.archivo = null;
                    vue.e1 = 3
                })
                .catch((err) => {
                    console.log(err);
                    vue.progress_upload = 'error'
                    vue.archivo = null;
                    vue.e1 = 3
                });
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
        }
    }
}
</script>


<style>
.messages_stepper_upload{

}
</style>
