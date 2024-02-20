<template>
    <section class="section-list ">
        <DefaultFilter v-model="open_advanced_filter"
                       @filter="advanced_filter(dataTable, filters, 1)"
        >
            <template v-slot:content>
                <v-row justify="center">

                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
                <v-spacer/>

                <DefaultModalButton
                    :label="'Crear curso'"
                     @click="openFormModal(modalCourseModality, null, null,'Selecciona qué modalidad de curso deseas crear')"
                />
                     <!-- v-if="$root.isSuperUser" -->


               <!--  <DefaultModalButton
                    :label="'Curso'"
                    @click="openCRUDPage(`/${ruta}cursos/create`)"/> -->
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
                            append-icon="mdi-magnify"
                        />
                    </v-col>

                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.types"
                            v-model="filters.type"
                            label="Tipo de curso"
                            @onChange="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
                            item-text="name"
                        />
                    </v-col>

                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.statuses"
                            v-model="filters.active"
                            label="Estado de curso"
                            @onChange="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
                            item-text="name"
                        />
                    </v-col>

                    <v-col cols="3">
                        <DefaultInputDate
                            clearable
                            dense
                            range
                            :referenceComponent="'modalDateFilter1'"
                            :options="modalDateFilter1"
                            v-model="filters.dates"
                            label="Fecha de creación"
                            @onChange="refreshDefaultTable(dataTable, filters, 1).changeHeaders()"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @encuesta="
                    openFormModal(
                        modalCursoEncuesta,
                        $event,
                        'encuesta',
                        `Encuesta del curso - ${$event.name}`
                    )
                "
                @mover_curso="
                    openFormModal(
                        modalMoverCurso,
                        $event,
                        'mover_curso',
                        'Mover curso'
                    )
                "
                @segmentation="
                    openSegmentationModal($event)
                "
                
                @duplicate="
                    openFormModal(
                        duplicateFormModalOptions,
                        $event,
                        'duplicate',
                        `Duplicar curso - ${$event.name}`
                    )
                "

                @compatibility="
                    openFormModal(
                        modalFormCompatibilityOptions,
                        $event,
                        'compatibility',
                        `Compatibilidad del curso - ${$event.name}`
                    )
                "
                @create_project="openProjectModal($event)"
                @edit_project="openProjectModal($event)"
                @logs="
                    openFormModal(
                        modalLogsOptions,
                        $event,
                        'logs',
                        `Logs del Curso - ${$event.name}`
                    )
                "
                @delete="deleteCurso($event)"
                @status="updateCourseStatus($event)"
                @edit="openFormModal(modalCourseOptions, $event, 'edit', `Editar curso - ${$event.name}`)"
                @preview_medias="openFormModal(modalPreviewMediaTopicsOptions, {resource_id:$event.id,type:'course'}, 'list', `Listado de multimedias del curso: ${course_name}`)"
            />
            <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\Course"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />

            <CursosEncuestaModal
                width="50vw"
                :ref="modalCursoEncuesta.ref"
                :options="modalCursoEncuesta"
                @onCancel="closeFormModal(modalCursoEncuesta)"
                @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
            />
            <MoverCursoModal
                width="50vw"
                :ref="modalMoverCurso.ref"
                :options="modalMoverCurso"
                @onCancel="closeFormModal(modalMoverCurso)"
                @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
                :modulo_id="modulo_id"
                :curso_escuela="escuela_id"
            />

            <DialogConfirm
                :ref="deleteConfirmationDialog.ref"
                v-model="deleteConfirmationDialog.open"
                width="408px"
                title="Eliminar Curso"
                subtitle="¿Está seguro de eliminar el curso?"
                @onConfirm="confirmDelete"
                @onCancel="deleteConfirmationDialog.open = false"
            />
            <CourseValidationsDelete
                width="408px"
                :ref="courseValidationModal.ref"
                :options="courseValidationModal"
                @onCancel="closeFormModal(courseValidationModal);  closeFormModal(deleteConfirmationDialog)"
                @onConfirm="confirmValidationModal(courseValidationModal,  null, confirmDelete(false))"
                :resource="{}"
            />

            <DialogConfirm
                :ref="courseUpdateStatusModal.ref"
                v-model="courseUpdateStatusModal.open"
                :options="courseUpdateStatusModal"
                width="408px"
                title="Cambiar de estado al curso"
                subtitle="¿Está seguro de cambiar de estado al curso?"
                @onConfirm="confirmUpdateStatus"
                @onCancel="courseUpdateStatusModal.open = false"
            />
            <CourseValidationsUpdateStatus
                width="408px"
                :ref="courseValidationModalUpdateStatus.ref"
                :options="courseValidationModalUpdateStatus"
                @onCancel="closeFormModal(courseValidationModalUpdateStatus);  closeFormModal(deleteConfirmationDialog)"
                @onConfirm="confirmValidationModal(courseValidationModalUpdateStatus,   null , confirmUpdateStatus(false))"
                :resource="{}"
            />

            <SegmentFormModal
                :options="modalFormSegmentationOptions"
                width="55vw"
                model_type="App\Models\Course"
                :model_id="null"
                :ref="modalFormSegmentationOptions.ref"
                @onCancel="closeSimpleModal(modalFormSegmentationOptions)"
                @onConfirm="closeFormModal(modalFormSegmentationOptions, dataTable, filters)"
            />
            <ProjectFormModal
                width="50vw"
                :ref="modalOptionProject.ref"
                :options="modalOptionProject"
                @onConfirm="closeFormModal(modalOptionProject, dataTable, filters)"
                @onCancel="closeFormModal(modalOptionProject)"
            />
            <CompatibilityFormModal
                :options="modalFormCompatibilityOptions"
                width="55vw"
                :ref="modalFormCompatibilityOptions.ref"
                @onCancel="closeSimpleModal(modalFormCompatibilityOptions)"
                @onConfirm="closeFormModal(modalFormCompatibilityOptions, dataTable, filters)"
            />
            <CourseFormModal
                width="65vw"
                :ref="modalCourseOptions.ref"
                :options="modalCourseOptions"
                :school_id="escuela_id"
                @onConfirm="closeFormModal(modalCourseOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalCourseOptions)"
            />
            <PreviewMediaTopicsModal
                width="450px"
                :ref="modalPreviewMediaTopicsOptions.ref"
                :options="modalPreviewMediaTopicsOptions"
                @onConfirm="closeFormModal(modalPreviewMediaTopicsOptions)"
                @onCancel="closeFormModal(modalPreviewMediaTopicsOptions)"
            />
            <course-Modality-modal
                :ref="modalCourseModality.ref"
                v-model="modalCourseModality.open"
                :options="modalCourseModality"
                width="900px"
                @onConfirm="openCourseModal"
                @onCancel="modalCourseModality.open = false"
                :modalities="selects.modalities"
            />

            <DuplicateForm
                :options="duplicateFormModalOptions"
                width="50vw"
                duplicate_level="course"
                :source_name="escuela_name"
                :ref="duplicateFormModalOptions.ref"
                @onConfirm="closeFormModal(duplicateFormModalOptions, dataTable, filters)"
                @onCancel="closeFormModal(duplicateFormModalOptions)"
            />
            <direct-segmentation-form
                :ref="modalDirectSegmentationOptions.ref"
                v-model="modalDirectSegmentationOptions.open"
                :options="modalDirectSegmentationOptions"
                model_type="App\Models\Course"
                width="55vw"
                @onConfirm="modalDirectSegmentationOptions.open=false"
                @onCancel="modalDirectSegmentationOptions.open = false"
                :modalities="selects.modalities"
            />
        </v-card>
    </section>
</template>

<script>
import CursosEncuestaModal from "./CursosEncuestaModal";
import MoverCursoModal from "./MoverCursoModal";
import CourseFormModal from "./CourseFormModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import CursoValidacionesModal from "./CursoValidacionesModal";
import SegmentFormModal from "../Blocks/SegmentFormModal";
import CompatibilityFormModal from "./CompatibilityFormModal";
import LogsModal from "../../components/globals/Logs";
import ProjectFormModal from "../Project/ProjectFormModal";
import PreviewMediaTopicsModal from "../Temas/PreviewMediaTopicsModal.vue";
import CourseModalityModal from "./CourseModalityModal";
import DuplicateForm from "../Escuelas/DuplicateForm";
import DirectSegmentationForm from "./DirectSegmentationForm";

export default {
    components: {
        ProjectFormModal,
        CursosEncuestaModal,
        MoverCursoModal,
        DialogConfirm,
        'CourseValidationsDelete': CursoValidacionesModal,
        'CourseValidationsUpdateStatus': CursoValidacionesModal,
        SegmentFormModal,
        CompatibilityFormModal,
        LogsModal,
        CourseFormModal,
        PreviewMediaTopicsModal,
        CourseModalityModal,
        DuplicateForm,
        DirectSegmentationForm
    },
    props: ['modulo_id', 'modulo_name', 'escuela_id', 'escuela_name', 'ruta'],
    data() {
        let vue = this
        let route_school = (vue.escuela_id !== '') ? `/escuelas/${vue.escuela_id}` : ``;
        return {
            breadcrumbs: [
                {title: 'Escuelas', text: `${this.escuela_name}`, disabled: false, href: `/escuelas`},
                {title: 'Cursos', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: `${route_school}/cursos/search`,
                ref: 'cursosTable',
                headers: [
                    // {text: "Orden", value: "orden", align: 'center'},
                    // {text: "Orden", value: "position", align: 'center', model: 'CourseSchool', sortable: false},
                    {text: "Portada", value: "new_image_2", align: 'center', sortable: false},
                    {text: "Nombre", value: "custom_curso_nombre", sortable: false},
                    // {text: "Estado de curso", value: "curso_estado", align: 'center', sortable: false},
                    //{text: "Tipo", value: "type", sortable: false},

                    {text: "Fecha de creación", value: "created_at", align: 'center', sortable: true},
                    {text: "Escuela", value: "schools", sortable: false},

                    {text: "Estado", value: "statusActions", align: 'center', sortable: false},

                    {text: "Opciones", value: "actions", align: 'center', sortable: false}
                ],
                statusActions: [

                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit',
                        // show_condition: "is_cursalab_super_user"
                    },
                    {
                        text: "Temas",
                        icon: 'fas fa-book',
                        type: 'route',
                        route: 'temas_route',
                        countBadgeConditions: [{
                            message: 'El curso no tiene temas activos',
                            minValue: 0,
                            propertyCond: 'active_topics_count',
                            propertyShow: 'active_inactive_topics_count',
                            backgroundColor: 'red'
                        },{
                            message: 'El curso tiene temas activos',
                            minValue: 1,
                            propertyCond: 'active_topics_count',
                            propertyShow: 'active_inactive_topics_count',
                            backgroundColor: '#5458EA'
                        }]
                    },
                    {
                        text: "Segmentación",
                        icon: 'mdi mdi-account-group segmentation-icon',
                        type: 'action',
                        method_name: 'segmentation',
                        conditionalBadgeIcon: [{
                            message: 'No tienes colaboradores participantes en el curso',
                            minValue: 0,
                            propertyCond: 'segments_count',
                            color: 'red',
                            icon: 'fas fa-exclamation-triangle',
                            iconSize: '12px'
                            },
                            {
                            message: 'Selecciona a los colaboradores que participarán en el curso',
                            minValue: 1,
                            propertyCond: 'segments_count',
                            color: '#7fbade',
                            icon: 'mdi mdi-check-circle'
                        }]
                    },
                    // {
                    //     text: "Editar",
                    //     icon: 'mdi mdi-pencil',
                    //     type: 'route',
                    //     route: 'edit_route'
                    // },


                    // {
                    //     text: "Eliminar",
                    //     icon: 'far fa-trash-alt',
                    //     type: 'action',
                    //     method_name: 'delete'
                    // },
                ],
                more_actions: [
                    {
                        text: "Duplicar curso",
                        icon: 'mdi mdi-content-copy',
                        type: 'action',
                        method_name: 'duplicate'
                    },
                    {
                        text: "Compatibles",
                        icon: 'fa fa-square',
                        type: 'action',
                        count: 'compatibilities_count',
                        method_name: 'compatibility',
                        show_condition: 'compatibility_available'
                    },
                    {
                        text: "Encuesta",
                        icon: 'mdi mdi-poll',
                        type: 'action',
                        count: 'encuesta_count',
                        method_name: 'encuesta',
                    },
                    {
                        text: "Crear Tarea",
                        icon: 'fas fa-book',
                        type: 'action',
                        show_condition:'create_project',
                        method_name: 'create_project',
                        // permission_name:'can_show_tarea'
                    },
                    {
                        text: "Editar Tarea",
                        icon: 'fas fa-book',
                        type: 'action',
                        show_condition:'edit_project',
                        method_name: 'edit_project',
                        // permission_name:'can_show_tarea'
                    },
                    {
                        text: "Usuario tareas",
                        icon: 'fas fa-book',
                        show_condition:'project_users',
                        type: 'route',
                        route: 'project_users_route',
                        // permission_name:'can_show_tarea'
                    },
                    // {
                    //     text: "Actualizar Estado",
                    //     icon: 'fa fa-circle',
                    //     type: 'action',
                    //     method_name: 'status'
                    // },
                    {
                        text: "Previsualización",
                        icon: 'mdi-cellphone',
                        type: 'action',
                        method_name: 'preview_medias',
                        show_condition: 'temas_count'
                    },
                    {
                        text: "Logs",
                        icon: "mdi mdi-database",
                        type: "action",
                        show_condition: "is_super_user",
                        method_name: "logs"
                    },
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                ]
            },
            selects: {
                modules: [],
                types: [],
                modalities:[],
                statuses: [
                    {id: null, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 2, name: 'Inactivos'},
                ],
            },
            filters: {
                q: '',
                active: 1,
                type: null,
                // category: null
            },

            delete_model: null,
            update_model: null,

            duplicateFormModalOptions: {
                ref: 'DuplicateForm',
                open: false,
                action: 'duplicate',
                base_endpoint: 'cursos',
                showCloseIcon: true,
                confirmLabel: 'Copiar contenidos'
            },

            modalCursoEncuesta: {
                ref: 'CursoEncuestaModal',
                open: false,
                base_endpoint: `/escuelas/${this.escuela_id}/cursos`,
            },
            modalFormSegmentationOptions: {
                ref: 'SegmentFormModal',
                open: false,
                persistent: true,
                base_endpoint: "/segments",
                confirmLabel: "Guardar",
                resource: "segmentación"
            },

            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                base_endpoint: "/search",
                persistent: true
            },
            modalDirectSegmentationOptions:{
                open:false,
                ref: 'CourseTypeModal',
                open: false,
                base_endpoint: '/segments',
                confirmLabel: 'Guardar',
                resource: 'course',
                title: '',
                action: null,
                persistent: true,
            },
            modalFormCompatibilityOptions: {
                ref: 'CompatibilityFormModal',
                open: false,
                persistent: true,
                base_endpoint: '/cursos',
                confirmLabel: 'Guardar',
                resource: 'compatibilidad',
            },

            deleteConfirmationDialog: {
                ref: 'CourseDeleteModal',
                title: 'Eliminar Curso',
                contentText: '¿Desea eliminar este registro?',
                open: false,
                endpoint: ''
            },
            courseValidationModal: {
                ref: 'CourseListValidationModal',
                open: false,
                width: '408px'
            },

            courseUpdateStatusModal: {
                ref: 'CourseUpdateStatusModal',
                title: 'Actualizar Curso',
                contentText: '¿Desea actualizar este registro?',
                open: false,
                endpoint: '',
                title_modal: 'Cambio de estado de un <b>curso</b>',
                type_modal: 'status',
                status_item_modal: null,
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un curso!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios no podrán acceder al curso.',
                            'El diploma del curso no aparecerá para descargar desde el app.',
                            'No podrás ver el curso como opción para la descarga de reportes.',
                            'El detalle del curso activos/inactivos aparecerá en “Notas de usuario”.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un curso!',
                        details: [
                            'Los usuarios verán los cambios en su progreso en unos minutos.',
                            'Los usuarios ahora podrán acceder al curso.',
                            'El diploma del curso ahora aparecerá para descargar desde el app.',
                            'Podrás ver el curso como opción para descargar reportes.'
                        ]
                    }
                },
            },
            courseValidationModalUpdateStatus: {
                ref: 'CourseListValidationModalUpdateStatus',
                open: false,
                title_modal: 'El curso es pre-requisito',
                type_modal:'requirement',
                content_modal: {
                    requirement: {
                        title: '¡El curso que deseas desactivar es un pre-requisito! '
                    },
                }
            },

            courseValidationModalDefault: {
                ref: 'CourseListValidationModal',
                action: null,
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'CursosValidaciones',
            },

            modalMoverCurso: {
                ref: 'MoverCursoModal',
                open: false,
                base_endpoint: `/escuelas/${this.escuela_id}/cursos`,
            },

            modalDateFilter1: {
                open: false,
            },
            modalOptionProject: {
                ref: 'ProjectFormModal',
                open: false,
                base_endpoint: '/projects',
                resource: 'Tarea',
                confirmLabel: 'Guardar',
                action:'create'
            },
            modalCourseOptions: {
                ref: 'CourseFormModal',
                open: false,
                base_endpoint: '/cursos',
                confirmLabel: 'Guardar',
                resource: 'curso',
                title: '',
                action: null,
                persistent: true,
                modality:null
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
            modalCourseModality:{
                open:false,
                ref: 'CourseTypeModal',
                open: false,
                base_endpoint: '/course',
                confirmLabel: 'Guardar',
                resource: 'course',
                title: 'Selecciona qué modalidad de curso deseas crear',
                action: null,
                persistent: true,
            }
        }
    },
    mounted() {
        let vue = this

        vue.filters.module = vue.modulo_id
        vue.filters.category = vue.escuela_id

        vue.getSelects()
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/cursos/get-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    // vue.selects.modules = data.data.modules
                    vue.selects.types = data.data.types
                    vue.selects.modalities = data.data.modalities;
                    // vue.modalOptions.selects.modules = data.data.modules
                    // vue.modalOptions.selects.types = data.data.types
                })
        },
        activity() {
            console.log('activity')
        },

        deleteCurso(course) {
            let vue = this
            vue.delete_model = course
            vue.deleteConfirmationDialog.open = true
        },
        confirmDelete(validateForm = true) {
            let vue = this
            vue.deleteConfirmationDialog.open = false
            vue.showLoader()
            let url = `/escuelas/${vue.escuela_id}/cursos/${vue.delete_model.id}/delete`
            const bodyData = {validateForm}

            vue.$http.post(url, bodyData)
                .then(async ({data}) => {
                    this.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0
                    if (has_info_messages)
                        await vue.handleValidationsAfterUpdate(data.data, vue.courseValidationModal, vue.courseValidationModalDefault);
                    else
                        vue.showAlert(data.data.msg)

                    vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                })
                .catch(async error => {
                    await vue.handleValidationsBeforeUpdate(error, vue.courseValidationModal, vue.courseValidationModalDefault);
                    vue.loadingActionBtn = false
                })
        },
        changeHeaders(){
            let vue = this;
            const indexOrden = vue.dataTable.headers.findIndex(h => h.text == 'Orden');

            if(vue.filters.category && !vue.filters.type && !vue.filters.q && !vue.filters.active &&  !vue.filters.dates){
                vue.$nextTick(() => {
                    if(indexOrden == -1){
                        vue.dataTable.headers.unshift({text: "Orden", value: "position", align: 'center', model: 'CourseSchool', sortable: false}, 1);
                    }
                });
            }else{
                if(indexOrden != -1){
                    vue.$nextTick(() => {
                        vue.dataTable.headers.splice(indexOrden, 1);
                    })
                }
            }
        },
        updateCourseStatus(course) {
            let vue = this
            vue.update_model = course
            vue.courseUpdateStatusModal.open = true
            vue.courseUpdateStatusModal.status_item_modal = Boolean(vue.update_model.active)
        },
        openProjectModal(course){
            let vue = this;
            let title = 'Crear Tarea';
            if(course.create_project){
                vue.modalOptionProject.course=course
                vue.modalOptionProject.action='create';
                vue.modalOptionProject.create_from_course_list = true;
            }else{
                vue.modalOptionProject.action='edit';
                vue.modalOptionProject.create_from_course_list = false;
                title = `Editar Tarea - ${course.name}`;
            }
            console.log('course',course);
            vue.openFormModal(vue.modalOptionProject,course.project,null,title);
        },
        async confirmUpdateStatus(validateForm = true) {
            let vue = this
            vue.courseUpdateStatusModal.open = false
            vue.showLoader()

            if (validateForm)
                vue.courseValidationModalUpdateStatus.action = null;

            if (
                vue.courseValidationModalUpdateStatus.action ===
                "validations-after-update"
            ) {
                vue.hideLoader();
                vue.courseValidationModalUpdateStatus.open = false;
                return;
            }

            let url = `/escuelas/${vue.escuela_id}/cursos/${vue.update_model.id}/status`;
            const bodyData = {validateForm}

            vue.$http.put(url, bodyData)
                .then(async ({data}) => {
                    vue.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0;

                    if (has_info_messages)
                        await vue.handleValidationsAfterUpdate(data.data, vue.courseValidationModalUpdateStatus, vue.courseValidationModalDefault);
                    else {
                        vue.courseValidationModalUpdateStatus.type_modal = null
                        vue.showAlert(data.data.msg)
                        vue.courseValidationModalUpdateStatus.open = false;
                    }

                    vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                })
                .catch(error => {
                    if (error && error.errors){
                        if(error.data.validations.list){
                            error.data.validations.list.forEach(element => {
                                if(element.type == "has_active_topics" && error.data.validations.list.length == 1){
                                    vue.courseValidationModalUpdateStatus.title_modal = 'Cambio de estado de un <b>curso</b>';
                                    vue.courseValidationModalUpdateStatus.content_modal.requirement.title = '¡Estás por desactivar un curso!';
                                }
                            });
                        }
                    }
                    vue.handleValidationsBeforeUpdate(error, vue.courseValidationModalUpdateStatus, vue.courseValidationModalDefault);
                    vue.loadingActionBtn = false
                })
        },
        openCourseModal(modality){
            let vue = this;
            vue.closeFormModal(vue.modalCourseModality);
            vue.modalCourseOptions.modality = modality;
            vue.openFormModal(vue.modalCourseOptions, null, null,'Crear '+ modality.name.toLowerCase());
        },
        openSegmentationModal(resource){
            let vue = this;
            if(!resource){
                return;
            }
            if(resource.active_topics_count == 0 && resource.modality_code=='virtual'){
                vue.showAlert('Es necesario crear temas activos para poder segmentar el curso.','warning');
                return;
            }
            if(resource.modality_code == 'in-person' || resource.modality_code=='virtual'){
                vue.openFormModal(vue.modalDirectSegmentationOptions, resource, 'segmentation', `Segmentación del curso - ${resource.name}`)
                resource.show_criteria_segmentation = false;
                return;
            }
            vue.openFormModal(vue.modalFormSegmentationOptions, resource, 'segmentation', `Segmentación del curso - ${resource.name}`)
        }
    }
}
</script>
