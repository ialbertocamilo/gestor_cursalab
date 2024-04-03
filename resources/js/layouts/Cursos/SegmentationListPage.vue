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
                <!-- <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/> -->
                Cursos

                <DefaultInfoTooltip class="mr-5" right>
                    <template v-slot:content>
                        <p><strong>Recuerda que un curso será visible para tus usuarios solo si cumple los siguientes requisitos:</strong></p>
                        <ul>
                            <li>El curso debe contar con al menos un tema activo y pertenecer al menos a una escuela activa.</li>
                            <li>El curso debe estar segmentado, ya sea por criterios o por documento.</li>
                            <li>El curso debe encontrarse activo (de manera directa o por medio de su programación).</li>
                        </ul>
                    </template>
                </DefaultInfoTooltip>

                <v-spacer/>

                <a style="font-size: 0.8em; font-weight: bold;" href="/cursos/download-segmentation"
                    target="_blank" v-if="$root.isSuperUser" class="mr-5"
                >
                    <i aria-hidden="true" class="v-icon notranslate mr-1 mdi mdi-download"></i>
                    Descargar segmentación
                </a>

                <DefaultModalButton
                    :label="'Crear curso'"
                    @click="openFormModal(modalCourseModality, null, null,'Selecciona qué modalidad de curso deseas crear')"
                />

            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="3">
                        <DefaultAutocomplete
                            clearable
                            dense
                            label="Módulos"
                            :items="selects.modules"
                            v-model="filters.segmented_module"
                            item-text="name"
                            item-value="id"
                            @onChange="getSchoolsAndRefreshTable(),changeHeaders()"
                        />
                    </v-col>

                    <v-col cols="3">
                        <DefaultAutocomplete
                            dense
                            :label="filters.segmented_module ? 'Escuelas' : 'Seleccione un módulo'"
                            :items="selects.schools"
                            v-model="filters.schools"
                            item-text="name"
                            item-value="id"
                            multiple
                            @onChange="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
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
                        <DefaultInput
                            learable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
                            append-icon="mdi-magnify"
                        />
                    </v-col>

                    <!--                    <v-col cols="3" class="d-flex justify-end">-->
                    <!--                        <DefaultButton-->
                    <!--                            label="Ver Filtros"-->
                    <!--                            icon="mdi-filter"-->
                    <!--                            @click="open_advanced_filter = !open_advanced_filter"/>-->
                    <!--                    </v-col>-->
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @encuesta="openFormModal(modalCursoEncuesta, $event, 'encuesta', `Encuesta del curso - ${$event.name}`)"
                @mover_curso="openFormModal(modalMoverCurso, $event, 'mover_curso', 'Mover curso')"
                @segmentation="openSegmentationModal($event)"
                @redirect_to_course_form_page="redirect_to_course_form_page($event)"
                @compatibility="openFormModal(modalFormCompatibilityOptions, $event, 'compatibility', `Compatibilidad del curso - ${$event.name}`)"
                @edit="openFormModal(modalCourseOptions, $event, 'edit', `Editar curso - ${$event.name}`)"
                @delete="deleteCurso($event)"
                @status="updateCourseStatus($event)"
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
                @preview_medias="openFormModal(modalPreviewMediaTopicsOptions, {resource_id:$event.id,type:'course'}, 'list', `Listado de multimedias del curso: ${$event.name}`)"
                @openMultipleSegmentationModal="openMultipleSegmentationModal($event)"
            />
            <CursosEncuestaModal
                width="50vw"
                :ref="modalCursoEncuesta.ref"
                :options="modalCursoEncuesta"
                @onCancel="closeFormModal(modalCursoEncuesta)"
                @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
            />
            <!--         <MoverCursoModal
                        width="50vw"
                        :ref="modalMoverCurso.ref"
                        :options="modalMoverCurso"
                        @onCancel="closeFormModal(modalMoverCurso)"
                        @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
                        :modulo_id="modulo_id"
                        :curso_escuela="escuela_id"
                    /> -->

            <!--    <DialogConfirm
                   :ref="deleteConfirmationDialog.ref"
                   v-model="deleteConfirmationDialog.open"
                   width="450px"
                   title="Eliminar Curso"
                   subtitle="¿Está seguro de eliminar el curso?"
                   @onConfirm="confirmDelete"
                   @onCancel="deleteConfirmationDialog.open = false"
               /> -->
            <!--        <CourseValidationsDelete
                       width="50vw"
                       :ref="courseValidationModal.ref"
                       :options="courseValidationModal"
                       @onCancel="closeFormModal(courseValidationModal);  closeFormModal(deleteConfirmationDialog)"
                       @onConfirm="confirmValidationModal(courseValidationModal,  null, confirmDelete(false))"
                       :resource="{}"
                   /> -->

            <DialogConfirm
                :ref="courseUpdateStatusModal.ref"
                v-model="courseUpdateStatusModal.open"
                width="450px"
                title="Cambiar de estado al curso"
                subtitle="¿Está seguro de cambiar de estado al curso?"
                @onConfirm="confirmUpdateStatus"
                @onCancel="courseUpdateStatusModal.open = false"
            />
            <CourseValidationsUpdateStatus
                width="50vw"
                :ref="courseValidationModalUpdateStatus.ref"
                :options="courseValidationModalUpdateStatus"
                @onCancel="closeFormModal(courseValidationModalUpdateStatus);  closeFormModal(deleteConfirmationDialog)"
                @onConfirm="confirmValidationModal(courseValidationModalUpdateStatus,   null , confirmUpdateStatus(false))"
                :resource="{}"
            />

            <SegmentCoursesFormModal
                :options="modalFormSegmentationOptions"
                width="55vw"
                model_type="App\Models\Course"
                :model_id="null"
                :ref="modalFormSegmentationOptions.ref"
                @onCancel="closeSimpleModal(modalFormSegmentationOptions)"
                @onConfirm="closeFormModal(modalFormSegmentationOptions, dataTable, filters)"
            />

            <CompatibilityFormModal
                :options="modalFormCompatibilityOptions"
                width="55vw"
                :ref="modalFormCompatibilityOptions.ref"
                @onCancel="closeSimpleModal(modalFormCompatibilityOptions)"
                @onConfirm="closeFormModal(modalFormCompatibilityOptions, dataTable, filters)"
            />
            <ProjectFormModal
                width="50vw"
                :ref="modalOptionProject.ref"
                :options="modalOptionProject"
                @onConfirm="closeFormModal(modalOptionProject, dataTable, filters)"
                @onCancel="closeFormModal(modalOptionProject)"
            />
            <CourseFormModal
                width="65vw"
                :ref="modalCourseOptions.ref"
                :options="modalCourseOptions"
                @onConfirm="closeFormModal(modalCourseOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalCourseOptions)"
            />

            <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\Course"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
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
          <SegmentMultipleCoursesModal
              :originCourseId="multipleCoursesModalOptions.resource.id"
              :subworkspacesIds="multipleCoursesModalOptions.resource.modulesIds || []"
              :options="multipleCoursesModalOptions"
              :ref="multipleCoursesModalOptions.ref"
              @onConfirm="closeFormModal(multipleCoursesModalOptions)"
              @onCancel="closeFormModal(multipleCoursesModalOptions)"
          />
        </v-card>
    </section>
</template>

<script>
import CursosEncuestaModal from "./CursosEncuestaModal";
import CourseFormModal from "./CourseFormModal";
import MoverCursoModal from "./MoverCursoModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import CursoValidacionesModal from "./CursoValidacionesModal";
import SegmentCoursesFormModal from "../Blocks/SegmentCoursesFormModal";
import CompatibilityFormModal from "./CompatibilityFormModal";
import ProjectFormModal from "../Project/ProjectFormModal";
import LogsModal from "../../components/globals/Logs";
import PreviewMediaTopicsModal from "../Temas/PreviewMediaTopicsModal.vue";
import CourseModalityModal from "./CourseModalityModal";
import DirectSegmentationForm from "./DirectSegmentationForm";
import SegmentMultipleCoursesModal
  from "../Blocks/SegmentMultipleCoursesModal.vue";

export default {
    components: {
      SegmentMultipleCoursesModal,
        ProjectFormModal,
        CursosEncuestaModal,
        MoverCursoModal,
        DialogConfirm,
        'CourseValidationsDelete': CursoValidacionesModal,
        'CourseValidationsUpdateStatus': CursoValidacionesModal,
        SegmentCoursesFormModal,
        CompatibilityFormModal,
        CourseFormModal,
        LogsModal,
        PreviewMediaTopicsModal,
        CourseModalityModal,
        DirectSegmentationForm
    },
    props: ['modulo_id', 'modulo_name',],
    data() {
        let vue = this
        // let route_school = (vue.escuela_id !== '') ? `/escuelas/${vue.escuela_id}` : ``;
        return {
            base_endpoint: `/cursos`,
            breadcrumbs: [
                {title: 'Cursos', text: '', disabled: false, href: null},
                // {title: 'Cursos', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: `cursos/search`,
                ref: 'cursosTable',
                headers: [
                    {text: "Portada", value: "new_image", align: 'center', sortable: false},
                    {text: "Nombre", value: "custom_curso_nombre", sortable: false},
                    // {text: "Nombre", value: "curso_nombre_escuela", sortable: false},
                    // {text: "Estado de curso", value: "curso_estado", align: 'center', sortable: false},
                    {text: "Fecha de creación", value: "created_at", sortable: false},
                    {text: "Escuela", value: "schools", sortable: false},
                    // {text: "Módulos", value: "modules", sortable: false},
                    //{text: "Módulos", value: "images", sortable: false},

                    {text: "Estado", value: "statusActions", align: 'center', sortable: false},

                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
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
                        },{
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
                    //     type: 'action',
                    //     method_name: 'redirect_to_course_form_page'
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
                        text: "Crear tarea",
                        icon: 'fas fa-book',
                        type: 'action',
                        show_condition:'create_project',
                        method_name: 'create_project',
                        // permission_name:'can_show_tarea'
                    },
                    {
                        text: "Editar tarea",
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
                    //     text: "Actualizar estado",
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
                      text: "Reusar segmentación",
                      icon: "mdi mdi-account-switch",
                      type: "action",
                        show_condition: 'segments_count',
                        method_name: "openMultipleSegmentationModal"
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
                schools: [],
                types: [],
                statuses: [
                    {id: null, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 2, name: 'Inactivos'},
                ],
                modalities:[],
            },
            filters: {
                q: '',
                active: 1,
                type: null,
                schools: [],
                segmented_module: null,
                // category: null
            },

            delete_model: null,
            update_model: null,

            modalCursoEncuesta: {
                ref: 'CursoEncuestaModal',
                open: false,
                base_endpoint: `/cursos`,
            },
            modalFormSegmentationOptions: {
                ref: 'SegmentCoursesFormModal',
                open: false,
                persistent: true,
                base_endpoint: '/segments',
                confirmLabel: 'Guardar',
                resource: 'segmentación',
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
            },
            modalOptionProject: {
                ref: 'ProjectFormModal',
                open: false,
                base_endpoint: '/projects',
                resource: 'Tarea',
                confirmLabel: 'Guardar',
                action:'create'
            },
            modalCourseModality:{
                open:false,
                ref: 'CourseTypeModal',
                base_endpoint: '/course',
                confirmLabel: 'Guardar',
                resource: 'course',
                title: 'Selecciona qué modalidad de curso deseas crear',
                action: null,
                persistent: true,
            },
            modalDirectSegmentationOptions:{
                open:false,
                ref: 'CourseDirectSegmentation',
                base_endpoint: '/segments',
                confirmLabel: 'Guardar',
                resource: 'course',
                title: '',
                action: null,
                persistent: true,
            },
            courseUpdateStatusModal: {
                ref: 'CourseUpdateStatusModal',
                title: 'Actualizar Curso',
                contentText: '¿Desea actualizar este registro?',
                open: false,
                endpoint: ''
            },
            courseValidationModalUpdateStatus: {
                ref: 'CourseListValidationModalUpdateStatus',
                open: false,
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
                base_endpoint: `/cursos`,
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
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                base_endpoint: "/search",
                persistent: true
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
            multipleCoursesModalOptions: {
              ref: 'SegmentMultipleCoursesModal',
              open: false,
              title: 'Segmentación múltiple',
              resource: {},
              hideConfirmBtn: false,
              persistent: true,
              cancelLabel: 'Cancelar',
              confirmLabel: 'Continuar'
            },
        }
    },
    mounted() {
        let vue = this

        let params = vue.getAllUrlParams(window.location.search);

        const param_segmented_module = params.segmented_module;
        const param_schools = params.schools;
        const param_q = params.q;

        if (param_segmented_module)
            vue.filters.segmented_module = parseInt(param_segmented_module);

        if (param_schools)
            vue.filters.schools = param_schools.map(school => parseInt(school));

        if (param_q)
            vue.filters.q = param_q;

        vue.getSelects();

    },
    methods: {
        getSchoolsAndRefreshTable(){
            let vue = this

            vue.getSchoolSelects()
            vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
        },
        getSchoolSelects() {
            let vue = this

            if (!vue.filters.segmented_module) {
                vue.selects.schools = [];
                vue.filters.schools = [];
                return false;
            }

            const url = `${vue.base_endpoint}/schools/subworkspace/${vue.filters.segmented_module}/get-data`

            vue.$http.get(url)
                .then(({data}) => {
                    vue.filters.schools = [];
                    vue.selects.schools = data.data.schools;
                })
        },
        getSelects() {
            let vue = this
            const url = `${vue.base_endpoint}/schools/get-data`



            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.schools = data.data.schools;
                    vue.selects.modules = data.data.modules;
                    vue.selects.modalities = data.data.modalities;
                    // vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                })
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
        changeHeaders(){
            let vue = this;
            const indexOrden = vue.dataTable.headers.findIndex(h => h.text == 'Orden');
            if(vue.filters.schools.length==1 && !vue.filters.type && !vue.filters.q && !vue.filters.active &&  !vue.filters.dates){
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

        updateCourseStatus(course) {
            let vue = this
            vue.update_model = course
            vue.courseUpdateStatusModal.open = true
        },
        async confirmUpdateStatus(validateForm = true) {
            let vue = this
            vue.courseUpdateStatusModal.open = false
            vue.showLoader()

            if (validateForm)
                vue.courseValidationModalUpdateStatus.action = null;


            if (vue.courseValidationModalUpdateStatus.action === 'validations-after-update') {
                vue.hideLoader();
                vue.courseValidationModalUpdateStatus.open = false;
                return;
            }


            let url = `/escuelas/${vue.update_model.first_school_id.id}/cursos/${vue.update_model.id}/status`;
            const bodyData = {validateForm}

            vue.$http.put(url, bodyData)
                .then(async ({data}) => {
                    vue.hideLoader()
                    const has_info_messages = data.data.messages.list.length > 0;

                    if (has_info_messages)
                        await vue.handleValidationsAfterUpdate(data.data, vue.courseValidationModalUpdateStatus, vue.courseValidationModalDefault);
                    else {
                        vue.showAlert(data.data.msg)
                        vue.courseValidationModalUpdateStatus.open = false;
                    }

                    vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                })
                .catch(error => {
                    vue.handleValidationsBeforeUpdate(error, vue.courseValidationModalUpdateStatus, vue.courseValidationModalDefault);
                    vue.loadingActionBtn = false
                })
        },

        redirect_to_course_form_page(course) {
            let vue = this;
            let url = `${course.edit_route}?`;

            url = `${url}${vue.addParamsToURL(url, vue.filters)}`;
            window.location.href = url;

            // const query_string = new URLSearchParams(vue.filters);
            // window.location.href = `${url}?${query_string.toString()}`;

            // const win = window.open(course.edit_route, '_blank');
            // win.focus();
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
        },
        openMultipleSegmentationModal(resource) {
          this.multipleCoursesModalOptions.resource = resource
          this.multipleCoursesModalOptions.open = true;
        }
    }

}
</script>
<style>
.segmentation-icon {
    font-size: 20px;
    line-height: 6px;
}
</style>
