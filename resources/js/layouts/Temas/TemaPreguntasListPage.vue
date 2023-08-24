<template>
    <section class="section-list ">
       
        <v-card flat class="elevation-0 mb-4">

            <v-card-title>
                <v-row>
                    <v-col cols="6">
                        <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
                    </v-col>
                    <v-col cols="6">
                        <div class="ddd-flex justify-content-end">
                            <v-btn
                                class="mx-1"
                                elevation="0"
                                color="primary"
                                @click="openFormModal(modalCreateQuestionsOptions, null,null, 'Creación de evaluación')"
                            >
                                <img width="22px" 
                                    class="mr-2" 
                                    style="filter: brightness(3);"
                                    src="/img/ia_convert.svg"
                                >
                                Crear con AI
                            </v-btn>
                            <DefaultActivityButton
                                :outlined="true"
                                :label="'Importar evaluación'"
                                @click="openFormModal(modalTemaPreguntasImport,null,null,modalTemaPreguntasImport.title)"
                            />
                            <DefaultModalButton
                                :text="true"
                                @click="openFormModal(modalOptions, null, 'create')"
                                :label="'Agregar pregunta'"/>
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
                        <div class="alert alert-success pa-1 pt-2" role="alert" v-show="validation.status == true">
                          <h6>La evaluación es correcta.</h6>
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
                @onConfirm="closeFormModal(modalCreateQuestionsOptions, dataTable, filters);refreshDefaultTable(dataTable, filters)"
                @onCancel="closeFormModal(modalCreateQuestionsOptions);refreshDefaultTable(dataTable, filters) "
            />
        </v-card>
    </section>
</template>

<script>
import TemaPreguntaFormModal from "./TemaPreguntaFormModal";
import TemaPreguntasImport from "./TemaPreguntasImport";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import LogsModal from "../../components/globals/Logs";
import CreateAIQuestionsModal from "./CreateAIQuestionsModal"
export default {
    components: {TemaPreguntaFormModal, TemaPreguntasImport, DialogConfirm, LogsModal,CreateAIQuestionsModal},
    props: [
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
                base_endpoint: `/questions`,
                confirmLabel: 'Guardar',
                title_modal: 'Creación de evaluación',
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

            delete_model: null
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

        // vue.getSelects();
    },
    methods: {
        reloadPage(){
            setTimeout( () => {
                    location.reload();
            }, 1000)
        },
        updateData(data){

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
        }
    }

}
</script>
