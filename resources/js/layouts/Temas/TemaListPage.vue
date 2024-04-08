<template>
    <section class="section-list ">
        <!-- <DefaultFilter v-model="open_advanced_filter"
                       @filter="advanced_filter(dataTable, filters, 1)"
        >
            <template v-slot:content>
                <v-row justify="center">

                </v-row>
            </template>
        </DefaultFilter> -->
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
                <v-spacer/>
                <DefaultButton
                    v-if="showPreviewButton"
                    outlined
                    label="Previsualización"
                    @click="openFormModal(modalPreviewMediaTopicsOptions, { resource_id:course_id,type:'course'}, 'list', `Listado de multimedias del curso: ${course_name}`)"
                />
                <DefaultModalButton
                    :label="'Crear tema'"
                    @click="openFormModal(modalTopicOptions, null, 'create', `Crear tema | Curso: ${course_name}`)"
                />
                     <!-- v-if="$root.isSuperUser" -->
                <!-- <DefaultModalButton
                    @click="openCRUDPage(`/${ruta}cursos/${course_id}/temas/create`)"
                    :label="'Tema'"
                /> -->
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="4">
                        <DefaultInput
                            learable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
                    <v-spacer></v-spacer>
                    <div class="d-flex align-items-center ml-4" v-if="course_offline.is_offline">
                        <DefaultInfoTooltip 
                            left
                            icon="mdi-database"
                            :color="course_offline.has_space ? '#2A3649' : '#FF4242'"
                            text="Espacio dedicado a contenido sin conexión en la plataforma." 
                        />
                        <!-- <v-icon color="#2A3649">mdi-database</v-icon> #FF4242 #2A3649-->
                        <p 
                            class="my-0 mx-2" 
                            :style="`${course_offline.has_space ? 'color: #2A3649' : 'color:#FF4242'};text-transform: uppercase;`"
                        >
                            {{ course_offline.sum_size_topics.size+' '+ course_offline.sum_size_topics.size_unit }} / {{ course_offline.limit }}
                        </p>
                    </div>
                </v-row>
            </v-card-text>
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @logs="
                    openFormModal(
                        modalLogsOptions,
                        $event,
                        'logs',
                        `Logs del tema - ${$event.nombre}`
                    )
                "
                
                @delete="deleteTema($event)"
                @status="updateTopicStatus($event)"
                @edit="openFormModal(modalTopicOptions, $event, 'edit', `Editar tema - ${$event.nombre} | Curso: ${course_name}`)"
                @data-loaded="enablePreviewbutton()"
                @preview_medias="openFormModal(modalPreviewMediaTopicsOptions,{
                    resource_id:$event.id,type:'topic',route:`/${ruta}cursos/${course_id}/temas/${$event.id}/medias`
                }, 'list', `Listado`)"
                @download_report_assistance="download_report_assistance"
            />

            <DialogConfirm
                v-model="deleteConfirmationDialog.open"
                width="408px"
                title="Eliminar Tema"
                subtitle="¿Está seguro de eliminar el tema?"
                @onConfirm="confirmDelete"
                @onCancel="deleteConfirmationDialog.open = false"
            />

            <TopicValidationsDelete
                width="50vw"
                :ref="topicValidationModal.ref"
                :options="topicValidationModal"
                @onCancel="closeFormModal(topicValidationModal); closeFormModal(deleteConfirmationDialog)"
                @onConfirm="confirmValidationModal(topicValidationModal,  null, confirmDelete(false))"
                :resource="{}"
            />

            <DialogConfirm
                :ref="topicUpdateStatusModal.ref"
                v-model="topicUpdateStatusModal.open"
                :options="topicUpdateStatusModal"
                width="408px"
                title="Cambiar de estado al curso"
                subtitle="¿Está seguro de cambiar de estado al curso?"
                @onConfirm="confirmUpdateStatus"
                @onCancel="topicUpdateStatusModal.open = false"
            />
            <TopicValidationsUpdateStatus
                width="50vw"
                :ref="topicValidationModalUpdateStatus.ref"
                :options="topicValidationModalUpdateStatus"
                @onCancel="closeFormModal(topicValidationModalUpdateStatus);  closeFormModal(deleteConfirmationDialog)"
                @onConfirm="confirmValidationModal(topicValidationModalUpdateStatus,   null , confirmUpdateStatus(false))"
                :resource="{}"
            />
            <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\Topic"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />
            <TopicFormModal
                width="70vw"
                :ref="modalTopicOptions.ref"
                :options="modalTopicOptions"
                :school_id="school_id"
                :course_id="course_id"
                @onConfirm="closeFormModal(modalTopicOptions, dataTable, filters),getListInfo()"
                @onCancel="closeFormModal(modalTopicOptions)"
            />
            <PreviewMediaTopicsModal
                width="450px"
                :ref="modalPreviewMediaTopicsOptions.ref"
                :options="modalPreviewMediaTopicsOptions"
                @onConfirm="closeFormModal(modalPreviewMediaTopicsOptions)"
                @onCancel="closeFormModal(modalPreviewMediaTopicsOptions)"
            />
            <CursosEncuestaModal
                width="50vw"
                :ref="modalCursoEncuesta.ref"
                :options="modalCursoEncuesta"
                @onCancel="closeFormModal(modalCursoEncuesta)"
                @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
            />
        </v-card>
    </section>
</template>

<script>
import DialogConfirm from "../../components/basicos/DialogConfirm";
import TemaValidacionesModal from "./TemaValidacionesModal";
import TopicFormModal from "./TopicFormModal";
import LogsModal from "../../components/globals/Logs";
import PreviewMediaTopicsModal from "./PreviewMediaTopicsModal";
import CursosEncuestaModal from "../Cursos/CursosEncuestaModal";

export default {
    components: {
    DialogConfirm,
    LogsModal,
    TopicFormModal,
    'TopicValidationsDelete': TemaValidacionesModal,
    'TopicValidationsUpdateStatus': TemaValidacionesModal,
    PreviewMediaTopicsModal,
    CursosEncuestaModal
},
    props: ['school_id', 'school_name', 'course_id', 'course_name', 'ruta'],
    data() {
        let vue = this
        return {
            tooltip:false,
            breadcrumbs: [
                {title: 'Escuelas', text: `${this.school_name}`, disabled: false, href: `/escuelas`},
                {
                    title: 'Cursos',
                    text: `${this.course_name}`,
                    disabled: false,
                    href: `/escuelas/${this.school_id}/cursos`
                },
                {title: 'Temas', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: `/${vue.ruta}cursos/${vue.course_id}/temas/search`,
                ref: 'cursosTable',
                headers: [
                    {text: "Orden", value: "position", align: 'center', model: 'Topic', sortable: false},
                    // {text: "Portada", value: "image", align: 'center', sortable: false},
                    {text: "Nombre", value: "nombre_and_requisito", sortable: false},
                    // {text: "Nombre", value: "nombre", sortable: false},
                    // {text: "Evaluable", value: "assessable", align: 'center', sortable: false},
                    // {text: "Tipo de evaluación", value: "tipo_evaluacion", sortable: false},
                    {text: "Tipo de evaluación", value: "tema_evaluacion", sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Evaluación",
                        icon: 'fas fa-tasks',
                        show_condition: 'es_evaluable',
                        type: 'route',
                        count: 'preguntas_count',
                        route: 'evaluacion_route'
                    },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit',
                    },
                        // show_condition: "is_cursalab_super_user"
                    // {
                    //     text: "Editar",
                    //     icon: 'mdi mdi-pencil',
                    //     type: 'route',
                    //     route: 'edit_route'
                    // },
                    {
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                ],
                more_actions: [
                    // {
                    //     text: "Encuesta",
                    //     icon: 'mdi mdi-poll',
                    //     type: 'action',
                    //     count: 'encuesta_count',
                    //     method_name: 'encuesta',
                    //     show_condition: 'is_poll_available'
                    // },
                    {
                        text: "Reporte de asistencia",
                        icon: 'mdi mdi-file-chart',
                        type: 'action',
                        method_name: 'download_report_assistance',
                        show_condition: "is_session_in_person",
                    },
                    {
                        text: "Previsualización",
                        icon: 'mdi-cellphone',
                        type: 'action',
                        method_name: 'preview_medias',
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
                ]
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
                module: null,
                category: null,
                curso: null
            },
            delete_model: null,
            update_model: null,

            deleteConfirmationDialog: {
                open: false,
            },

            topicValidationModal: {
                ref: 'TopicListValidationModal',
                open: false,
            },

            topicUpdateStatusModal: {
                ref: 'CourseUpdateStatusModal',
                title: 'Actualizar Curso',
                contentText: '¿Desea actualizar este registro?',
                open: false,
                endpoint: '',
                title_modal: 'Cambio de estado de un <b>tema</b>',
                type_modal: 'status',
                status_item_modal: null,
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un tema!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios no podrán acceder al tema.',
                            'No podrás ver el tema como opción para descargar reportes.',
                            'El detalle del tema inactivo aparecerá en “Notas de usuario”.',
                            'Si es el único tema, se desactivará el curso.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un tema!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios ahora podrán acceder al tema.',
                            'Podrás ver el tema como opción para descargar reportes.',
                            'Si es el único tema, se activará también el curso.'
                        ]
                    }
                },
            },
            topicValidationModalUpdateStatus: {
                ref: 'TopicListValidationModalUpdateStatus',
                open: false,
            },

            topicValidationModalDefault: {
                ref: 'TopicListValidationModalDefault',
                action: null,
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'TemasValidaciones',
            },
            modalPreviewMediaTopicsOptions:{
                ref: 'PreviewMediaTopics',
                action: null,
                open: false,
                base_endpoint: '',
                hideConfirmBtn: true,
                hideCancelBtn: true,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'Topic',
            },
            modalTopicOptions: {
                ref: 'TopicFormModal',
                open: false,
                base_endpoint: '/temas',
                confirmLabel: 'Guardar',
                resource: 'tema',
                title: '',
                action: null,
                persistent: true,
            },
            modalCursoEncuesta: {
                ref: 'CursoEncuestaModal',
                open: false,
                base_endpoint: `/escuelas/${this.school_id}/cursos/${this.course_id}/temas`,
            },
            showPreviewButton:false,
            course_offline:{
                is_offline:false,
                sum_size_topics:{
                    size:0,
                    size_unit:'0 MB',
                },
                limit:'1 GB',
                space:true,
            }
        }
    },
    mounted() {
        let vue = this
        vue.getListInfo();

        // vue.filters.module = vue.modulo_id

        // vue.filters.category = vue.school_id
        // vue.filters.curso = vue.course_id
    },
    methods: {
        async getListInfo() {
            let vue = this
            const url = `${vue.modalCursoEncuesta.base_endpoint}/get-selects`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.course_offline = data.data.course_offline
                })
        },
        activity() {
            console.log('activity')
        },
        deleteTema(tema) {
            let vue = this
            vue.delete_model = tema
            vue.deleteConfirmationDialog.open = true
        },
        async confirmDelete(validateForm = true) {
            let vue = this
            vue.showLoader()
            vue.deleteConfirmationDialog.open = false

            if (validateForm)
                vue.topicValidationModal.action = null;

            if (vue.topicValidationModal.action === 'validations-after-update') {
                vue.hideLoader();
                vue.topicValidationModal.open = false;
                return;
            }

            let url = `/escuelas/${vue.school_id}/cursos/${vue.course_id}/temas/${vue.delete_model.id}`
            const bodyData = {validateForm}

            await vue.$http.post(url, bodyData)
                .then(async ({data}) => {
                    vue.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0
                    if (has_info_messages)
                        await vue.handleValidationsAfterUpdate(data.data, vue.topicValidationModal, vue.topicValidationModalDefault);
                    else {
                        vue.showAlert(data.data.msg);
                        vue.topicValidationModal.open = false;
                    }

                    vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                })
                .catch(async error => {
                    await vue.handleValidationsBeforeUpdate(error, vue.topicValidationModal, vue.topicValidationModalDefault);
                    vue.loadingActionBtn = false
                })
            vue.getListInfo();
        },

        updateTopicStatus(course) {
            let vue = this
            vue.update_model = course
            vue.topicUpdateStatusModal.open = true
            vue.topicUpdateStatusModal.status_item_modal = Boolean(vue.update_model.active);
        },
        async confirmUpdateStatus(validateForm = true) {
            let vue = this
            vue.topicUpdateStatusModal.open = false
            vue.showLoader()

            if (validateForm)
                vue.topicValidationModalUpdateStatus.action = null;

            if (vue.topicValidationModalUpdateStatus.action === 'validations-after-update') {
                vue.hideLoader();
                vue.topicValidationModalUpdateStatus.open = false;
                return;
            }

            let url = `/escuelas/${vue.school_id}/cursos/${vue.course_id}/temas/${vue.update_model.id}/status`;
            const bodyData = {validateForm}

            await vue.$http.put(url, bodyData)
                .then(async ({data}) => {
                    vue.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0;
                    console.log("has_info_messages :: ", has_info_messages)

                    if (has_info_messages)
                        await vue.handleValidationsAfterUpdate(data.data, vue.topicValidationModalUpdateStatus, vue.topicValidationModalDefault);
                    else {
                        vue.showAlert(data.data.msg)
                        vue.topicValidationModalUpdateStatus.open = false;
                    }

                    vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                })
                .catch(error => {
                    vue.handleValidationsBeforeUpdate(error, vue.topicValidationModalUpdateStatus, vue.topicValidationModalDefault);
                    vue.loadingActionBtn = false
                })
            vue.getListInfo();
        },
        enablePreviewbutton(){
            let vue = this;
            vue.showPreviewButton = vue.$refs[vue.dataTable.ref].rows.length;
        },
        download_report_assistance(topic){
            let vue =this;
            let url = `/escuelas/${vue.school_id}/cursos/${vue.course_id}/temas/${topic.id}/download-report-assistance`;
            window.open(url).attr("href");
        }
    }
}
</script>
