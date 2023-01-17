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
                    :label="'Curso'"
                    @click="openCRUDPage(`/cursos/create`)"/>
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
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>

                    <v-col cols="3">
                        <DefaultAutocomplete
                            dense
                            label="Escuelas"
                            :items="selects.schools"
                            v-model="filters.schools"
                            item-text="name"
                            item-value="id"
                            multiple
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>

                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.statuses"
                            v-model="filters.active"
                            label="Estado de curso"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            item-text="name"
                        />
                    </v-col>

                    <v-col cols="3">
                        <DefaultInput
                            learable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
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
                avoid_first_data_load
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @encuesta="openFormModal(modalCursoEncuesta, $event, 'encuesta', `Encuesta del Curso - ${$event.name}`)"
                @mover_curso="openFormModal(modalMoverCurso, $event, 'mover_curso', 'Mover Curso')"
                @segmentation="openFormModal(modalFormSegmentationOptions, $event, 'segmentation', `Segmentación del Curso - ${$event.name}`)"
                @redirect_to_course_form_page="redirect_to_course_form_page($event)"
                @compatibility="openFormModal(modalFormCompatibilityOptions, $event, 'compatibility', `Compatibilidad del curso - ${$event.name}`)"
                @delete="deleteCurso($event)"
                @status="updateCourseStatus($event)"
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

            <SegmentFormModal
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

        </v-card>
    </section>
</template>

<script>
import CursosEncuestaModal from "./CursosEncuestaModal";
import MoverCursoModal from "./MoverCursoModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import CursoValidacionesModal from "./CursoValidacionesModal";
import SegmentFormModal from "../Blocks/SegmentFormModal";
import CompatibilityFormModal from "./CompatibilityFormModal";

export default {
    components: {
        CursosEncuestaModal,
        MoverCursoModal,
        DialogConfirm,
        'CourseValidationsDelete': CursoValidacionesModal,
        'CourseValidationsUpdateStatus': CursoValidacionesModal,
        SegmentFormModal,
        CompatibilityFormModal
    },
    props: ['modulo_id', 'modulo_name',],
    data() {
        let vue = this
        // let route_school = (vue.escuela_id !== '') ? `/escuelas/${vue.escuela_id}` : ``;
        return {
            base_endpoint: `/cursos`,
            breadcrumbs: [
                {title: 'Segmentación', text: 'Cursos', disabled: false, href: null},
                // {title: 'Cursos', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: `cursos/search`,
                ref: 'cursosTable',
                headers: [
                    {text: "Portada", value: "medium_image", align: 'center', sortable: false},
                    {text: "Nombre", value: "custom_curso_nombre", sortable: false},
                    {text: "Módulos", value: "modules", sortable: false},
                    {text: "Escuela", value: "schools", sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Temas",
                        icon: 'fas fa-book',
                        type: 'route',
                        count: 'temas_count',
                        route: 'temas_route'
                    },
                    {
                        text: "Segmentación",
                        icon: 'fa fa-square',
                        type: 'action',
                        count: 'segments_count',
                        method_name: 'segmentation'
                    },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'redirect_to_course_form_page'
                    },
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
                        method_name: 'encuesta'
                    },
                    {
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    // {
                    //     text: "Eliminar",
                    //     icon: 'far fa-trash-alt',
                    //     type: 'action',
                    //     method_name: 'delete'
                    // },
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
            },
            filters: {
                q: '',
                active: null,
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
                ref: 'SegmentFormModal',
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
        getSelects() {
            let vue = this
            const url = `${vue.base_endpoint}/schools/get-data`



            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.schools = data.data.schools;
                    vue.selects.modules = data.data.modules;

                    vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                })
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
        }
    }

}
</script>
