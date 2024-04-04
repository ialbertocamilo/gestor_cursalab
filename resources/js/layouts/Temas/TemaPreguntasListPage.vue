<template>
    <section class="section-list ">
        <v-card flat class="elevation-0 mb-4">

            <v-card-title>
                <v-row>
                    <v-col :cols="limits_ai_convert.has_permission_to_use_ia_evaluation ? '6' : '9'">
                        <DefaultBreadcrumbs :breadcrumbs="custom_breadcrumbs ? custom_breadcrumbs : breadcrumbs" :class="custom_breadcrumbs ?'breadcrumbs_line' : ''"/>
                    </v-col>
                    <v-col :cols="limits_ai_convert.has_permission_to_use_ia_evaluation ? '6' : '3'">
                        <div class="d-flex justify-content-end">
                            <span v-if="limits_ai_convert.has_permission_to_use_ia_evaluation">
                                <v-badge small class="_badge mr-4" overlap color="#57BFE3">
                                        <template v-slot:badge>
                                            <span v-text="`${limits_ai_convert.ia_evaluations_generated}/${limits_ai_convert.limit_allowed_ia_evaluations}`"></span>
                                        </template>
                                        <v-btn
                                            elevation="0"
                                            color="primary"
                                            @click="openFormModal(modalCreateQuestionsOptions, null,null, 'Creación de evaluación')"
                                            :disabled="
                                                limits_ai_convert.ia_evaluations_generated >= limits_ai_convert.limit_allowed_ia_evaluations
                                                || !limits_ai_convert.is_ready_to_create_AIQuestions"
                                        >
                                            <img width="22px"
                                                v-if="limits_ai_convert.is_ready_to_create_AIQuestions"
                                                class="mr-2"
                                                style="filter: brightness(3);"
                                                src="/img/ia_convert.svg"
                                            >
                                            <img else width="22px"
                                                v-else
                                                class="loader-jarvis img-rotate mr-2"
                                                style="filter: brightness(3);"
                                                src="/img/loader-jarvis.svg"
                                            >
                                            Crear con AI
                                        </v-btn>
                                </v-badge>
                            </span>
                            <DefaultActivityButton
                                :outlined="true"
                                :label="'Importar evaluación'"
                                @click="openFormModal(modalTemaPreguntasImport,null,null,modalTemaPreguntasImport.title)"
                            />
                            <DefaultModalButton
                                :text="true"
                                @click="openFormModal(modalOptions, null, 'create')"
                                :label="'Pregunta'"/>
                            <br>
                            <DefaultModalButton
                                :text="true"
                                icon_name="mdi-download"
                                isIconButton
                                @click="downloadReportQuestions()"
                            />
                        </div>
                    </v-col>
                </v-row>
                <!-- <v-spacer/> -->
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-text>

                <v-row>
                    <div class="col-md-8">
                        <div class="alert --alert-info" style="border-color: #5458ea;" role="alert">
                            <span v-if="evaluation_type == 'qualified'" class="mb-1">
                                - Cuando un tema es "Evaluable" y de tipo "Calificada" se deben agregar opciones a las preguntas.
                            </span>
                            <span v-if="evaluation_type == 'open'" class="mb-1">
                                - Cuando un tema es "Evaluable" y de tipo "Abierta" no se consideran las opciones, solo se muestran las preguntas para que los usuarios respondan con un texto.
                            </span>
                            <span class="mb-1">
                                - Al eliminar todas las preguntas el tema se convierte a un tema no evaluable.<br>
                            </span>
                            <span v-if="evaluation_type == 'qualified'" class="mb-1">
                                - El total de puntos que se puede acumular en las preguntas obligatorias es de <strong>{{ qualification_type_value }}</strong> puntos. [{{ qualification_type_name }}]
                            </span>
                            <span v-if="evaluation_type == 'qualified'" class="mb-1">
                                - El total de puntos solo considera las preguntas activas.
                            </span>
                        </div>
                    </div>

                    <div class="col-md-4 ">
                        <table class="table table-striped" style="background-color: #5458ea; color: white;" v-if="evaluation_type == 'qualified'">
                          <tbody>
                            <tr>
                              <td>Obligatorios: </td>
                              <td>{{ validation.evaluation_data_sum_required }} puntos</td>
                            </tr>
                            <tr>
                              <td>No obligatorios: </td>
                              <td>{{ validation.evaluation_data_sum_not_required }} puntos</td>
                            </tr>
                            <tr>
                              <td>Total: </td>
                              <td>{{ validation.evaluation_data_sum }} puntos</td>
                            </tr>
                          </tbody>
                        </table>
                    </div>
                </v-row>

                <v-row>
                    <v-col cols="4">
                        <DefaultInput
                            learable dense
                            v-model="filters.q"
                            label="Buscar pregunta ..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                        />
                    </v-col>

                    <v-col cols="8" v-if="evaluation_type == 'qualified'" class="text-center">
                        <div class="alert alert-success pa-1 pt-2" role="alert" v-show="validation.status == true && !questionsError.length">
                          <h6>La evaluación es correcta.</h6>
                        </div>

                        <div class="alert alert-danger pa-1 pt-2" role="alert"
                             v-show="questionsError.length">
                            <ul>
                            <li v-for="error in questionsError" class="text-left">
                                {{ error }}
                            </li>
                            </ul>
                        </div>

                        <div class="alert alert-danger pa-1" role="alert" v-show="validation.status == false && validation.missing_score != 0">
                          <h6>Es necesario asignar {{ validation.missing_score }} punto(s) más para completar la evaluación.</h6>
                        </div>
                    </v-col>
                </v-row>

            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @logs=" openFormModal(
                        modalLogsOptions,
                        $event,
                        'logs',
                        `Logs de la pregunta - ${$event.custom_tema_preguntas_pregunta}`
                    )"
                @data-loaded="dataLoaded($event)"
                @edit="openFormModal(modalOptions, $event, 'edit')"
                @delete="deleteTemaPregunta($event);"
            />
            <TemaPreguntaFormModal
                width="60vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="refreshDefaultTable(dataTable, filters); updateData($event);"
                @onCancel="closeFormModal(modalOptions)"
                :evaluable="evaluable"
                :evaluation_type="evaluation_type"
                :base_score="parseInt(qualification_type_value)"
            />
            <TemaPreguntasImport
                width="50vw"
                :ref="modalTemaPreguntasImport.ref"
                :options="modalTemaPreguntasImport"
                @onConfirm="closeFormModal(modalTemaPreguntasImport, dataTable, filters);refreshDefaultTable(dataTable, filters)"
                @onCancel="closeFormModal(modalTemaPreguntasImport);refreshDefaultTable(dataTable, filters) "
            />
            <DialogConfirm
                v-model="modalDeleteOptions.open"
                :options="modalDeleteOptions"
                width="408px"
                title="Eliminar pregunta"
                subtitle="¿Está seguro de eliminar la pregunta?"
                @onConfirm="confirmDelete"
                @onCancel="modalDeleteOptions.open = false"
            />

             <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\Question"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />
            <CreateAIQuestionsModal
                width="80vw"
                :ref="modalCreateQuestionsOptions.ref"
                :options="modalCreateQuestionsOptions"
                @onConfirm="
                    closeFormModal(modalCreateQuestionsOptions, dataTable, filters);
                    refreshDefaultTable(dataTable, filters),
                    openConfirmCreateQuestion(),updateData($event)"
                @onCancel="closeFormModal(modalCreateQuestionsOptions) "
                :number_socket="number_socket"
            />
            <DefaultAlertDialog
                :options="modalInfoCreateQuestion"
                :hideCancelBtn="modalInfoCreateQuestion.hideCancelBtn"
                :confirmLabel="modalInfoCreateQuestion.confirmLabel"
                :showCloseButton = "false"
                width="40vw"
                :ref="modalInfoCreateQuestion.ref"
                @onCancel="closeFormModal(modalInfoCreateQuestion)"
                @onConfirm="closeFormModal(modalInfoCreateQuestion)"
            >
                <template v-slot:content>
                    <div style="border-radius: 10px;" class="d-flex flex-column align-items-center justify-content-center elevation-2">
                        <div class="my-8">
                            <img src="/img/check_confirm.svg">
                        </div>
                        <div>
                            <p style="color:#57BFE3">¡Excelente tu evaluación se creo correctamente!</p>
                        </div>
                    </div>
                    <p class="mt-4">
                        <strong>Recuerda cuentas con {{ limits_ai_convert.limit_allowed_ia_evaluations - limits_ai_convert.ia_evaluations_generated }} evaluaciones por realizar con AI.</strong>
                    </p>
                </template>
            </DefaultAlertDialog>
        </v-card>
    </section>
</template>

<script>
const number_socket = Math.floor(Math.random(6)*1000000);
import TemaPreguntaFormModal from "./TemaPreguntaFormModal";
import TemaPreguntasImport from "./TemaPreguntasImport";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import LogsModal from "../../components/globals/Logs";
import CreateAIQuestionsModal from "./CreateAIQuestionsModal"
export default {
    components: {TemaPreguntaFormModal, TemaPreguntasImport, DialogConfirm, LogsModal,CreateAIQuestionsModal},
    props: [
        'workspace_id',
        'modulo_id',
        'modulo_name',
        'categoria_id',
        'categoria_name',
        'curso_id',
        'curso_name',
        'tema_id',
        'tema_name',
        'status',
        'missing_score',
        'evaluable',
        'qualification_type',
        'qualification_type_value',
        'qualification_type_name',
        'evaluation_type',
        'evaluation_data_sum',
        'evaluation_data_sum_required',
        'evaluation_data_sum_not_required',
        'custom_breadcrumbs'
    ],
    data() {
        let vue = this
        return {
            validation: {
                status: vue.status,
                missing_score: vue.missing_score,
                evaluation_data_sum: vue.evaluation_data_sum,
                evaluation_data_sum_required: vue.evaluation_data_sum_required,
                evaluation_data_sum_not_required: vue.evaluation_data_sum_not_required,
            },
            breadcrumbs: [
                {
                    title: 'Escuelas',
                    text: `${this.categoria_name}`,
                    disabled: false,
                    href: `/escuelas`
                },
                {
                    title: 'Cursos',
                    text: `${this.curso_name}`,
                    disabled: false,
                    href: `/escuelas/${this.categoria_id}/cursos`
                },
                {
                    title: 'Temas',
                    text: `${this.tema_name}`,
                    disabled: false,
                    href: `/escuelas/${this.categoria_id}/cursos/${this.curso_id}/temas`
                },
                {title: 'Evaluaciones', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas/search`,
                ref: 'temaPreguntasTable',
                headers: [
                    {text: "Pregunta", value: "custom_tema_preguntas_pregunta", align: 'start', sortable: false},
                    {text: "Tipo", value: "tipo_pregunta", align: 'center', sortable: false},
                    {text: "Obligatorio", value: "required", align: 'center', sortable: false},
                    {text: "Puntaje", value: "score", align: 'center', sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
                    },
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                    {
                        text: "Logs",
                        icon: "mdi mdi-database",
                        type: "action",
                        show_condition: "is_super_user",
                        method_name: "logs"
                    }
                ],
            },
            selects: {
                modules: []
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            filters: {
                q: '',
            },
            modalOptions: {
                ref: 'TemaPreguntasModal',
                open: false,
                base_endpoint: `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas`,
                resource: 'Pregunta',
                confirmLabel: 'Guardar',
                showCloseIcon: true,
            },
            modalDeleteOptions: {
                open: false,
                title_modal: 'Eliminación de una <b>pregunta</b>',
                type_modal: 'delete',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar una pregunta!',
                        details: [
                            'Si tu evaluación no tiene suficientes puntos, para completar un examen, se inhabilita.'
                        ],
                    }
                },
            },
            modalCreateQuestionsOptions:{
                ref: 'CreateQuestions',
                open: false,
                base_endpoint: `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas`,
                confirmLabel: 'Guardar',
                title_modal: `Creación de evaluación con AI (${this.tema_name})`,
                topic_id: this.tema_id,
                workspace_id: this.workspace_id,
                hideCancelBtn: true,
                hideConfirmBtn: true,
            },
            modalTemaPreguntasImport: {
                ref: 'TemaPreguntasImport',
                open: false,
                base_endpoint: `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas/import`,
                resource: '',
                confirmLabel: 'Guardar',
                title: 'Importar Evaluación',
                hideCancelBtn: true,
                hideConfirmBtn: true,
                topicUrl: `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/search/${vue.tema_id}`,
            },
            modalInfoCreateQuestion:{
                open: false,
                ref:'ModalInfoCreateQuestion',
                title: 'Evaluación creada',
                hideCancelBtn:true,
                confirmLabel:'Entendido'
            },
            delete_model: null,
            number_socket:number_socket,
            limits_ai_convert:{
                ia_evaluations_generated:0,
                limit_allowed_ia_evaluations:0
            },
            questionsError : []
        }
    },
    mounted() {
        let vue = this

        if (vue.status == false && vue.missing_score != 0) {

            let msg = document.createElement("div");
            let pts = vue.missing_score;
            msg.innerHTML=`<ul class="mx-2"><li class="text-justify mt-2">Es necesario agregar ${pts} punto(s) para que esta evaluación se encuentre disponible en la aplicación.</li></ul>
            `;
            swal({
              title:'Información',
              icon:'info',
              content: msg,
            })
        }
        window.Echo.channel(`questions-ia-generated.${number_socket}`).listen('QuestionIaGeneratedEvent', result => {
            // try {
            //     console.log(result);
            //     this.questions.push(result.data.mensaje);
            //     console.log(this.number_socket);
            // } catch (error) {
            //     console.error('Error al procesar los datos:', error);
            // }
        });
        window.Echo.connector.pusher.connection.bind('message', (payload) => {
            if(payload.channel == `questions-ia-generated.${number_socket}`){
                if(payload.data.question){
                    vue.$refs[vue.modalCreateQuestionsOptions.ref].setQuestionFromFatherComponent(payload.data.question);
                }
            }
        });
        vue.loadLimitsGenerateIaDescriptions();
        // vue.getSelects();
    },
    methods: {
        reloadPage(){
            setTimeout( () => {
                    location.reload();
            }, 1000)
        },
        updateData(data){
            console.log(data);
            let vue = this

            vue.validation.status = data.status
            vue.validation.missing_score = data.data.score_missing
            vue.validation.evaluation_data_sum = data.data.sum
            vue.validation.evaluation_data_sum_required = data.data.sum_required
            vue.validation.evaluation_data_sum_not_required = data.data.sum_not_required

            if(vue.validation.status){
                vue.queryStatus("tema", "crear_evaluacion");
            }
        },
        getSelects() {
            // let vue = this
            // const url = `/escuelas/get-selects`
            // vue.$http.get(url)
            //     .then(({data}) => {
            //         vue.selects.modules = data.data.modules
            //         vue.modalOptions.selects.modules = data.data.modules
            //     })
        },
        activity() {
            console.log('activity')
        },
        deleteTemaPregunta(usuario) {
            let vue = this
            vue.delete_model = usuario
            vue.modalDeleteOptions.open = true
        },
        confirmDelete() {
            let vue = this
            let url = `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas/${vue.delete_model.id}`

            vue.$http.delete(url)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
                    vue.refreshDefaultTable(vue.dataTable, vue.filters)
                    vue.delete_model = null
                    vue.modalDeleteOptions.open = false

                    // vue.updateData(data)
                })
        },
        openConfirmCreateQuestion(){
            let vue = this;
            vue.limits_ai_convert.ia_evaluations_generated = vue.limits_ai_convert.ia_evaluations_generated + 1;
            vue.openSimpleModal(vue.modalInfoCreateQuestion);
        },
        async loadLimitsGenerateIaDescriptions(){
            this.showLoader();
            await axios.get(`/jarvis/limits?type=evaluations&topic_id=${this.tema_id}`).then(({data})=>{
                this.hideLoader();
                this.limits_ai_convert = data.data;
            })
        },
        dataLoaded(data) {

            this.questionsError = [];

            const rows = data.data.data;
            rows.forEach(r => {
                if (!r.answers_are_valid) {
                    this.questionsError.push(`No hay definida una respuesta correcta: ${r.custom_tema_preguntas_pregunta} .`)
                }
            });
        },
        async downloadReportQuestions(){
            let vue = this;
            vue.showLoader();
            let url = `/escuelas/${vue.categoria_id}/cursos/${vue.curso_id}/temas/${vue.tema_id}/preguntas/download`
            await axios.get(url).then(({data})=>{
                let questions = data.data.questions;
                let headers = data.data.excel_headers;
                let filename = data.data.filename;
                this.descargarExcelwithValuesInArray({
                    headers:headers,
                    values:questions,
                    confirm:true,
                    filename:filename,
                    filesheet:'Examen'
                });
                vue.hideLoader();

            })
        }
    }

}
</script>
<style>
.img-rotate {
  animation: rotacion 4s linear infinite;
}

@keyframes rotacion {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
