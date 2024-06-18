<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>
            <v-form ref="TemaPreguntaForm">
                <DefaultErrors :errors="errors"/>

                <v-alert
                    border="left"
                    dense
                    transition="fade-transition"
                    :type="alert_frontend.tipo_alert"
                    text
                    v-model="alert_frontend.alert"
                    close-text="Cerrar alerta"
                    class="mx-8 text-left"
                    style="position: fixed;width: 50%;z-index: 27;"
                >
                    {{ alert_frontend.message_alert }}
                </v-alert>

                <v-row justify="space-around" class="mt-3">
                    <v-col cols="12">
                        <!--                        <DefaultRichText-->
                        <!--                            v-model="resource.pregunta"-->
                        <!--                            label="Pregunta"-->
                        <!--                            :show-validate-required="rules.pregunta"-->
                        <!--                            show-required-->
                        <!--                            @setVaidateRequired="rules.pregunta = $event"-->
                        <!--                        />-->
                        <fieldset class="editor">
                            <legend>Pregunta
                                <RequiredFieldSymbol/>
                            </legend>
                            <editor
                                :api-key="$root.getEditorAPIKey()"
                                v-model="resource.pregunta"
                                :init="{
                                    content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                    height: 170,
                                    menubar: false,
                                    language: 'es',
                                    force_br_newlines : true,
                                    force_p_newlines : false,
                                    forced_root_block : '',
                                    plugins: ['lists image preview anchor', 'code', 'paste','link'],
                                    toolbar:
                                        'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                    images_upload_handler: images_upload_handler,
                                }"
                            />
                        </fieldset>
                    </v-col>
                </v-row>
                <v-row v-if="evaluation_type !== 'open'">

                    <v-col cols="12">
                        <!--                        <DefaultRichText-->
                        <!--                            v-model="tempAnswer"-->
                        <!--                            title="Respuesta"-->
                        <!--                        />-->
<!--                        <div class="d-flex justify-content-center mb-2">-->
<!--                            <label class="default-rich-text-title">Respuesta</label>-->
<!--                        </div>-->
                        <fieldset class="editor">
                            <legend>Respuesta</legend>
                            <editor
                                :api-key="$root.getEditorAPIKey()"
                                v-model="tempAnswer"
                                :init="{
                                    content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                                    height: 170,
                                    menubar: false,
                                    language: 'es',
                                    force_br_newlines : true,
                                    force_p_newlines : false,
                                    forced_root_block : '',
                                    plugins: ['lists image preview anchor', 'code', 'paste','link'],
                                    toolbar:
                                        'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code | link',
                                    images_upload_handler: images_upload_handler,
                                }"
                            />
                        </fieldset>
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultButton
                            label="Alternativa"
                            outlined
                            icon="mdi-plus"
                            small-icon
                            @click="addOption"
                        />
                    </v-col>
                    <!--                    <v-alert>-->
                    <!--                        <li v-for="validacion in text_validaciones" v-text="validacion"/>-->
                    <!--                    </v-alert>-->
                    <v-col cols="12">
                        <table class="table table-hover datatable" style="overflow-x: scroll">
                            <thead>
                            <tr class="td-size">
                                <th class="text-left" style="width: 70%">Respuesta</th>
                                <th class="text-center" style="width: 15%">¿Correcta?</th>
                                <th class="text-center" style="width: 15%">Acción</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-if="resource.respuestas && resource.respuestas.length === 0">
                                <td colspan="3" class="text-center"> - No hay respuestas registradas -</td>
                            </tr>
                            <tr v-else
                                class="text-left td-size"
                                v-for="(respuesta, index2) in resource.respuestas"
                                :key="index2"
                            >
                                <td v-html="respuesta.opc"></td>
                                <td class="d-flex justify-center switch-pregunta">
                                    <v-simple-checkbox
                                        color="primary"
                                        @input="changeCorrectAnswer(respuesta.id, respuesta.correcta)"
                                        v-model="respuesta.correcta"
                                    />
                                </td>
                                <td class="text-center">
                                    <v-btn icon small @click="deleteAnswer(respuesta.id)">
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </v-col>
                    <v-col cols="4">
                        <DefaultInput
                                dense
                                type="number"
                                label="Puntaje"
                                placeholder="Ingrese un puntaje"
                                :min="1" :max="base_score"
                                v-model="resource.score"
                                class="mt-2"
                            />

                    </v-col>
                    <v-col cols="4" class="d-flex align-items-center">
                        <div class="">
                            <DefaultToggle v-model="resource.required" active-label="Obligatorio" inactive-label="No obligatorio"/>
                        </div>
                    </v-col>
                    <v-col cols="4" class="d-flex align-items-center">
                        <div class="">
                            <DefaultToggle v-model="resource.active" @onChange="alertStatus"/>
                        </div>
                    </v-col>
                </v-row>
            </v-form>
            <DialogConfirm
                :ref="questionUpdateStatusModal.ref"
                v-model="questionUpdateStatusModal.open"
                :options="questionUpdateStatusModal"
                width="408px"
                title="Cambiar de estado al curso"
                subtitle="¿Está seguro de cambiar de estado al curso?"
                @onConfirm="confirmUpdateStatus"
                @onCancel="questionUpdateStatusModal.open = false"
            />
        </template>
    </DefaultDialog>

</template>

<script>
// import DefaultRichText from "../../components/globals/DefaultRichText";
import Editor from "@tinymce/tinymce-vue";
import DialogConfirm from "../../components/basicos/DialogConfirm";

const validaciones = [
    'Debe de registrar más de 2 respuestas',
    'Debe marcar una respuesta correcta'
]
export default {
    components: {
        editor: Editor, DialogConfirm
    },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String,
        base_score: {
            type: Number,
            default: 20,
        },
        evaluable: String,
        evaluation_type: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            errors: [],
            resourceDefault: {
                id: null,
                pregunta: "",
                respuestas: [],
                rpta_ok: null,
                active: true,
                required: false,
                score: 1,
                nuevasRptas: "",
                evaluation_type: ''
            },
            resource: {},
            selects: {
                encuestas: []
            },
            rules: {
                pregunta: false,
            },
            tempAnswer: null,
            text_validaciones: validaciones,
            alert_frontend: {
                tipo_alert: "info",
                message_alert: "",
                alert: false,
            },
            questionUpdateStatusModal: {
                open: false,
                title_modal: 'Cambio de estado de una <b>pregunta</b>',
                type_modal: 'delete',
                content_modal: {
                    delete: {
                        title: '¡Estás por desactivar una pregunta!',
                        details: [
                            'Si tu evaluación no tiene suficientes puntos, para completar un examen, se inhabilita.'
                        ],
                    }
                },
            },
        }
    }
    ,
    methods: {
        closeModal() {
            let vue = this;
            vue.errors = []
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            vue.errors = []

            vue.showLoader()
            vue.resource.evaluation_type = vue.evaluation_type;
            const validateForm = vue.validateFieldsForm();

            // console.log(validateForm)
            vue.generarJson();
            if (validateForm) {
                const url = `${vue.options.base_endpoint}/store`
                vue.$http.post(url, vue.resource)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm', data.data)
                        vue.hideLoader()
                    })
                    .catch((error) => {
                        vue.hideLoader()

                        if (error && error.errors)
                            vue.errors = error.errors
                    })
            } else {
                vue.hideLoader()
            }
        },
        generarJson() {
            let vue = this;
            let cadena = "{";
            if (vue.resource.respuestas.length > 0) {
                vue.resource.respuestas.forEach((element) => {
                    if (isNaN(element.opc)) {
                        cadena += '"' + element.id + '":"' + element.opc.replaceAll('"', "'").replace(/(?:\r\n|\r|\n)/g, '<br>') + '",';
                    } else {
                        cadena += '"' + element.id + '":"' + element.opc + '",';
                    }
                });
                cadena = cadena.substring(0, cadena.length - 1);
            }
            cadena += "}";
            vue.resource.nuevasRptas = cadena;
            /*vue.pregunta.nuevasRptas = vue.pregunta.rptas_json;*/
        },
        images_upload_handler(blobInfo, success, failure) {
            console.log(blobInfo.blob());
            let vue = this
            let formdata = new FormData();
            formdata.append("image", blobInfo.blob(), blobInfo.filename());
            formdata.append("model_id", null);

            vue.$http
                .post("/upload-image/evaluaciones", formdata)
                .then((res) => {
                    success(res.data.location);
                })
                .catch((err) => {
                    console.log(err)
                    failure("upload failed!");
                });
        },
        validateFieldsForm() {

            let vue = this

            // Validar pregunta no vacia

            if (!vue.resource.pregunta) {
                // console.log('validacion pregunta no vacia')
                vue.rules.pregunta = true
                return false
            }

            // Validar que exista al menos 2 respuestas, cuando
            // la pregunta no es abierta

            if (vue.evaluation_type !== 'open') {
                if (vue.resource.respuestas.length < 2){
                    // console.log('validacion cant preguntas')
                    return false
                }
            }

            if(vue.resource.score < 0  || (parseInt(vue.resource.score) > parseInt(vue.base_score)) ){
                let message = "El puntaje debe ser mayor a 0 y menor o igual a " + parseInt(vue.base_score)
                vue.errors = [[message]]
                // vue.show_alert_frontend("warning", "El puntaje debe ser mayor o igual a 1, o menor o igual a "+(parseInt(vue.base_score)));
                return false;
            }


            // Validar que exista respuesta correcta
            // console.log('validaciones ok')
            // console.log(vue.resource.rpta_ok)
            return true
            // return vue.resource.rpta_ok;
        },
        resetSelects() {
            let vue = this
            vue.tempAnswer = ''
            vue.rules.pregunta = false
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('TemaPreguntaForm')
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = JSON.parse(JSON.stringify(vue.resourceDefault))
                // vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            if (resource) {
                let url = `${vue.options.base_endpoint}/${resource.id}`
                await vue.$http.get(url)
                    .then(({data}) => {
                        vue.resource = data.data.pregunta
                    })
            }
            return 0;
        },
        loadSelects() {

        },
        changeCorrectAnswer(answer_id, status) {
            let vue = this;
            vue.resource.respuestas.forEach((rpta) => {
                if (rpta.id === answer_id) {
                    rpta.correcta = status;
                    vue.resource.rpta_ok = status ? parseInt(rpta.id) : 0;
                } else {
                    rpta.correcta = false;
                }
            });
        },
        deleteAnswer(respuesta_id) {
            let vue = this;
            let respuesta = vue.resource.respuestas.findIndex((rpta) => rpta.id === respuesta_id);
            vue.resource.respuestas.splice(respuesta, 1);
            vue.resource.respuestas.forEach((rpta, index) => {
                rpta.id = index + 1;
                if (rpta.correcta)
                    this.resource.rpta_ok = rpta.id;
            });
        },
        addOption() {
            let vue = this;
            if (!vue.tempAnswer) return

            let newAnswer = {
                id: vue.resource.respuestas.length + 1,
                opc: vue.tempAnswer,
                correcta: false,
            };
            vue.tempAnswer = "";
            vue.resource.respuestas.push(newAnswer);
        },

        show_alert_frontend(tipo, message) {
            let vue = this;
            vue.alert_frontend.tipo_alert = tipo;
            vue.alert_frontend.message_alert = message;
            vue.alert_frontend.alert = true;
            setTimeout(() => {
                vue.alert_frontend.alert = false;
            }, 10000);
        },

        alertStatus(){
            let vue = this
            vue.questionUpdateStatusModal.open = !vue.resource.active
        },
        async confirmUpdateStatus(){
            let vue = this
            vue.questionUpdateStatusModal.open = false
        }
    }
}
</script>
