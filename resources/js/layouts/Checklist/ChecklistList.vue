<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <span class="fw-bold font-nunito">Checklists</span>
                <v-spacer/>
                <DefaultModalButton
                    :label="'Crear checklist'"
                    @click="openFormModal(modalChecklistModality, null, null,'Selecciona el tipo de actividad a realizar')"
                />
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por título..."
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
            <!-- @activities="openFormModal(modalActivities,$event, null,'Crear Checklist > Actividades')" -->
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openFormModal(modalChecklist, $event, null,`Editar Checklist - ${$event.title}`)"
                @abrirModalCreateEditChecklist="openFormModal(modalChecklistModality, null, null,'Selecciona el tipo de actividad a realizar')"
                @duplicate="duplicateChecklist($event)"
                @delete="openFormModal(modalDeleteOptions,$event,'delete','Eliminar un <b>checklist</b>')"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambio de estado de un <b>checklist</b>')"
                @logs="openFormModal(modalLogsOptions,$event,'logs',`Logs del Checklist - ${$event.title}`)"
                @segmentation="openFormModal(modalFormSegmentationOptions, $event, 'segmentation', `Segmentación del checklist - ${$event.title}`)"
                @supervisors="openFormModal(modalSupervisorOptions,$event,null,`Vinculación de auditores - ${$event.title}`)"
            />
            <!-- @alumnos="openFormModal(modalOptions, $event, 'ver_alumnos', 'Alumnos')" -->
        </v-card>
        <!-- <ModalSubidaMasivaChecklist
            template_url="/templates/Plantilla_Checklist.xlsx"
            v-model="modal.asignar_ver_alumnos"
            :width="'40%'"
            :options="optionsModalSubidaMasivaChecklist"
            @onCancel="optionsModalSubidaMasivaChecklist.open=false"
            @onConfirm = "optionsModalSubidaMasivaChecklist.open=false"
            @showSnackbar="mostrarSnackBar($event)"
            @refreshTable="refreshDefaultTable(dataTable, filters, 1)"
        /> -->

        <ModalCreateChecklist
            :ref="modalChecklist.ref"
            :options="modalChecklist"
            width="60vw"
            @onConfirm="
                closeSimpleModal(modalChecklistModality),
                closeSimpleModal(modalChecklist);
                refreshDefaultTable(dataTable, filters, 1);
                openNextStepConfigurationModal($event)
            "
            @onClose="closeSimpleModal(modalChecklist)"
        />

        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />
        <LogsModal
            :options="modalLogsOptions"
            width="55vw"
            :model_id="null"
            model_type="App\Models\CheckList"
            :ref="modalLogsOptions.ref"
            @onCancel="closeSimpleModal(modalLogsOptions)"
        />
        <DefaultStatusModal
            :options="modalStatusOptions"
            :ref="modalStatusOptions.ref"
            @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalStatusOptions)"
        />
        <ChecklistModality
            :ref="modalChecklistModality.ref"
            v-model="modalChecklistModality.open"
            :options="modalChecklistModality"
            width="900px"
            @onConfirm="openChecklistModal"
            @onCancel="closeSimpleModal(modalChecklistModality)"
            :modalities="modalities"
        />
        <ChecklistConfigurationModal
            :ref="modalChecklistConfiguration.ref"
            v-model="modalChecklistConfiguration.open"
            :options="modalChecklistConfiguration"
            width="900px"
            @onCancel="closeSimpleModal(modalChecklistConfiguration)"
            @onConfirm="changeConfiguration"
        />
        <ActivitiesModal
            :ref="modalActivities.ref"
            v-model="modalActivities.open"
            :options="modalActivities"
            width="1000px"
            @onConfirm="closeSimpleModal(modalActivities);openNextStepConfigurationModal($event)"
            @onCancel="closeSimpleModal(modalActivities)"
        />
        <SegmentFormModal
            :options="modalFormSegmentationOptions"
            width="55vw"
            model_type="App\Models\Checklist"
            :model_id="null"
            :ref="modalFormSegmentationOptions.ref"
            @onCancel="closeSimpleModal(modalFormSegmentationOptions)"
            @onConfirm="verifyNextStep($event),closeFormModal(modalFormSegmentationOptions, dataTable, filters)"
        />
        <SegmentFormModal
            :options="modalSupervisorOptions"
            width="55vw"
            model_type="App\Models\ChecklistSupervisor"
            :model_id="null"
            :ref="modalSupervisorOptions.ref"
            @onCancel="closeSimpleModal(modalSupervisorOptions)"
            @onConfirm="verifyNextStep($event),closeFormModal(modalSupervisorOptions, dataTable, filters)"
        />
        <!-- <SupervisorSegmentationModal 
            :options="modalSupervisorOptions"
            width="55vw"
            model_type="App\Models\Checklist"
            :model_id="null"
            :ref="modalSupervisorOptions.ref"
            @onCancel="closeSimpleModal(modalSupervisorOptions)"
            @onConfirm="openNextStepConfigurationModal($event),closeFormModal(modalSupervisorOptions, dataTable, filters)"
        /> -->
        <DialogConfirm
            :ref="finishConfigurationModal.ref"
            v-model="finishConfigurationModal.open"
            width="450px"
            title="Checklist finalizado"
            :subtitle="finishConfigurationModal.subtitle"
            txt_btn_cancel="Activarlo más tarde"
            txt_btn_confirm="Activar checklist"
            @onConfirm="confirmUpdateStatus"
            @onCancel="finishConfigurationModal.open = false"
        />
    </section>
</template>

<script>
// modalActivitiesIAOptions
import ModalSubidaMasivaChecklist from "../../components/Entrenamiento/Checklist/ModalSubidaMasivaChecklist.vue";
import ModalCreateChecklist from "../../components/Entrenamiento/Checklist/ModalCreateChecklist.vue";
import ModalAsignarChecklistCurso from "../../components/Entrenamiento/Checklist/ModalAsignarChecklistCurso.vue";

import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import LogsModal from "../../components/globals/Logs";

import ChecklistModality from "./ChecklistModality";
import ChecklistConfigurationModal from './ChecklistConfigurationModal';
import ActivitiesModal from './ActivitiesModal';

import SegmentFormModal from "../Blocks/SegmentFormModal";
import SupervisorSegmentationModal from "./SupervisorSegmentationModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";

export default {
    components: {
        ModalCreateChecklist,
        ModalAsignarChecklistCurso,
        DefaultDeleteModal,
        DefaultStatusModal,
        LogsModal,
        ModalSubidaMasivaChecklist,
        ChecklistModality,
        ChecklistConfigurationModal,
        ActivitiesModal,
        SegmentFormModal,
        SupervisorSegmentationModal,
        DialogConfirm
    },
    data() {
        return {
            modalities:[],
            dataTable: {
                endpoint: '/entrenamiento/checklist/v2/search',
                ref: 'ChecklistTable',
                custom_no_data_text:{
                    label:'Agregar un checklist',
                    icon_button:'mdi mdi-plus',
                    action:'abrirModalCreateEditChecklist'
                },
                headers: [
                    {text: "Título", value: "title", align: 'start', sortable: false},
                    {text: "Modalidad", value: "checklist_modality", align: 'start', sortable: false},
                    {text: "Tipo", value: "checklist_type", align: 'start', sortable: false},
                    {text: "¿Es recurrente?", value: "replicate", align: 'start', sortable: false},
                    {text: "Fecha límite de vigencia", value: "finishes_at", align: 'start', sortable: true},
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
                        text: "Actividades",
                        icon: 'mdi mdi-book-variant',
                        // method_name: 'activities',
                        type: 'route',
                        route: 'activities_route',
                        conditionalBadgeIcon: [{
                            message: 'No tienes actividades en el checklist',
                            minValue: 0,
                            propertyCond: 'activities_count',
                            color: 'red',
                            icon: 'fas fa-exclamation-triangle',
                            iconSize: '12px'
                        },{
                            message: 'Añades actividades en el checklist',
                            minValue: 1,
                            propertyCond: 'activities_count',
                            color: '#7fbade',
                            icon: 'mdi mdi-check-circle'
                        }]
                    },
                    {
                        text: "Segmentación",
                        icon: 'mdi mdi-account-group segmentation-icon',
                        type: 'action',
                        method_name: 'segmentation',
                        show_condition:'can_create_segmentation',
                        conditionalBadgeIcon: [{
                            message: 'No tienes colaboradores participantes en el checklist',
                            minValue: 0,
                            propertyCond: 'segments_count',
                            color: 'red',
                            icon: 'fas fa-exclamation-triangle',
                            iconSize: '12px'
                        },{
                            message: 'Selecciona a los colaboradores que participarán en el checklist',
                            minValue: 1,
                            propertyCond: 'segments_count',
                            color: '#7fbade',
                            icon: 'mdi mdi-check-circle'
                        }]
                    },
                    {
                        text: "Auditores",
                        icon: 'mdi mdi-account',
                        type: 'action',
                        method_name: 'supervisors',
                        conditionalBadgeIcon: [{
                            message: 'No tienes auditores participantes en el checklist',
                            minValue: 0,
                            propertyCond: 'supervisors_count',
                            color: 'red',
                            icon: 'fas fa-exclamation-triangle',
                            iconSize: '12px'
                        },{
                            message: 'Selecciona a los auditores que participarán en el checklist',
                            minValue: 1,
                            propertyCond: 'supervisors_count',
                            color: '#7fbade',
                            icon: 'mdi mdi-check-circle'
                        }]
                    },
                    {
                        text: "Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                ],
                more_actions: [
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

            filters: {
                q: null
            },
            btn_reload_data: false,
            mostrarFiltros: true,
            modal: {
                crear_editar_checklist: false,
                ver_items: false,
                asignar: false,
                subida_masiva: false,
                modality_id:null,
                base_endpoint:'/entrenamiento/checklists'
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalDeleteOptions: {
                ref: 'ChecklistDeleteModal',
                open: false,
                base_endpoint: '/entrenamiento/checklists',
                endpoint: '',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar un checklist! ',
                        details: [
                            'Todos los colaboradores seleccionados se les borrará los datos de su checklist.',
                            'El registro se eliminará de la base de datos y no podrá recuperarse'
                        ],
                    }
                },
                width: '408px'
            },
            dataModalChecklist: {},
            dataModalVerItems: {},
            checklists: [],
            txt_filter_checklist: "",
            file: null,

            modalStatusOptions: {
                ref: 'ChecklistUpdateStatusModal',
                open: false,
                base_endpoint: '/entrenamiento/checklists',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un checklist!',
                        details: [
                            'Todos los colaboradores seleccionados se les desactivará el checklist.',
                            'El registro se desactivará en la base de datos.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un checklist!',
                        details: [
                            'Todos los colaboradores seleccionados se les activará el checklist.',
                            'El registro se activará en la base de datos.'
                        ]
                    }
                },
                width: '408px'
            },
            checklistCreateEditModal: {
                id: 0,
                title: '',
                description: '',
                active: true,
                checklist_actividades: [],
                courses: [],
                segments: [],
                segmentation_by_document: {
                    segmentation_by_document:[]
                }
            },
            optionsModalSubidaMasivaChecklist:{
                open: false,
                title: 'Subir checklist'
            },
            modalChecklist:{
                open:false,
                ref: 'ChecklistModal',
                base_endpoint: '/entrenamiento/checklist/v2',
                confirmLabel: 'Guardar',
                resource: 'checklist',
                action: null,
                persistent: true,
            },
            modalChecklistModality:{
                open:false,
                ref: 'ChecklistModalityModal',
                base_endpoint: '/checklist',
                confirmLabel: 'Guardar',
                resource: 'checklist',
                title: 'Selecciona el tipo de actividad a realizar',
                action: null,
                persistent: true,
            },
            modalChecklistConfiguration:{
                open:false,
                ref: 'ChecklistConfiguration',
                base_endpoint: '/checklist',
                confirmLabel: 'Guardar',
                resource: 'checklist',
                title: '¡Haz creado la base del checklist!',
                action: null,
                persistent: true,
            },
            modalActivities:{
                open:false,
                ref: 'modalActivities',
                base_endpoint: '/entrenamiento/checklist/v2',
                confirmLabel: 'Guardar',
                resource: 'checklist',
                title: '',
                action: null,
                persistent: true,
            },
            modalSegment: {
                open: false,
                reg:'ModalSegment',
                ver_items: false,
                asignar: false,
                subida_masiva: false,
                model_type:"App\Models\Checklist"
            },
            dataModalSegment: {},
            modalFormSegmentationOptions: {
                ref: 'SegmentFormModal',
                open: false,
                persistent: true,
                base_endpoint: "/segments",
                confirmLabel: "Guardar",
                resource: "segmentación"
            },
            modalSupervisorOptions:{
                ref: 'AuditorFormModal',
                open: false,
                persistent: true,
                base_endpoint: "/segments",
                confirmLabel: "Guardar",
                resource: "Auditor"
            },
            finishConfigurationModal:{
                ref: 'finishConfigurationModal',
                title: 'Checklist finalizado',
                subtitle: `<img src="/images/innovation-flatline.png" />
                            <br> 
                            <span style="font-size: 18px;font-weight: bold;">Has terminado de configurar tu checklist.</span>
                            <br>
                            <span style="font-size: 18px;">¿Deseas activar tu checklist?</span>`,
                open: false,
                endpoint: '/entrenamiento/checklist/v2'
            }
        }
    },
    mounted() {
        let vue = this;
        vue.getInitData();
    },
    methods: {
        async getInitData(){
            let vue = this;
            await vue.$http.get(`/entrenamiento/checklists/init-data`).then(({data})=>{
                vue.modalities = data.data.modalities;
            }).catch(()=>{

            })
        },
        updateChecklistStatus(checklist) {
            let vue = this
            vue.update_model = checklist
            vue.checklistUpdateStatusModal.open = true
            vue.checklistUpdateStatusModal.status_item_modal = Boolean(vue.update_model.active)
        },

        async confirmUpdateStatus(validateForm = true) {
            let vue = this
            vue.checklistUpdateStatusModal.open = false
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

        },
        async closeModalSubidaMasiva() {
            let vue = this;
            vue.modal.subida_masiva = false;
        },
        // async duplicateChecklist(checklist) {

        //     let vue = this;
        //     let new_checklist = {...checklist}

        //     this.showLoader()
        //     vue.$http.post(`/entrenamiento/checklists/search_checklist`, { id: new_checklist.id})
        //         .then((res) => {
        //             let res_checklist = res.data.data.checklist;
        //             if (res_checklist != null) {

        //                 new_checklist = res_checklist;

        //                 new_checklist.title = new_checklist.title  + ' - copia'
        //                 new_checklist.duplicado = true

        //                 vue.abrirModalCreateEditChecklist(new_checklist)

        //             }else{
        //                 vue.$notification.warning(`No se pudo obtener datos del checklist`, {
        //                     timer: 6,
        //                     showLeftIcn: false,
        //                     showCloseIcn: true
        //                 });
        //                 vue.closeModalCreateEditChecklist();
        //                 vue.refreshDefaultTable(vue.dataTable, vue.filters);
        //             }
        //             this.hideLoader()
        //         })
        //         .catch((err) => {
        //             console.log(err);
        //             this.hideLoader()
        //         });
        // },
        async closeModalCreateEditChecklist() {
            let vue = this;
            // await vue.getData();
            vue.$refs.ModalCreateChecklist.resetValidation()
            vue.dataModalChecklist = {
                id: 0,
                title: '',
                description: '',
                active: true,
                checklist_actividades: [],
                courses: []
            };
            vue.checklistCreateEditModal = {
                id: 0,
                title: '',
                description: '',
                active: true,
                checklist_actividades: [],
                courses: [],
                segments: [],
                segmentation_by_document: {
                    segmentation_by_document:[]
                }
            };
            vue.modal.crear_editar_checklist = false;
        },
        closeModalAsignarChecklistCurso() {
            let vue = this;
            vue.modal.asignar = false;
        },
        openChecklistModal(modality){
            let vue = this;
            vue.openFormModal(vue.modalChecklistModality,'activity_card');
            vue.modalChecklist.modality = modality;
            vue.openFormModal(vue.modalChecklist);
            // vue.abrirModalCreateEditChecklist(vue.checklistCreateEditModal);
        },
        changeConfiguration({checklist,next_step}){
            let vue = this;
            vue.closeSimpleModal(vue.modalChecklistConfiguration);
            vue.closeSimpleModal(vue.modalChecklistModality);
            switch (next_step) {
                case 'create_activities':
                    window.location.href = `/entrenamiento/checklist/v2/${checklist.id}/activities`;
                break;
                case 'segmentation_card':
                    vue.openFormModal(vue.modalFormSegmentationOptions, checklist, 'segmentation', `Segmentación del checklist - ${checklist.title}`)
                break;
                case 'supervisor_card':
                    vue.openFormModal(vue.modalSupervisorOptions, checklist, null,`Auditores - ${checklist.title}`);
                break;
                default:
                break;
            }
           
        },
        closeModalSegment(){
            vue.closeSimpleModal(vue.modalSegment);
        },
        confirmModalSegment(){
            vue.closeSimpleModal(vue.modalSegment);
        },
        async verifyNextStep(checklist){
            let vue = this;
            await vue.$http.get(`/entrenamiento/checklist/v2/${checklist.id}/verify-next-step`)
                .then(({data})=>{
                    vue.openNextStepConfigurationModal(data.data);
                });
        },
        openNextStepConfigurationModal(configuration_data){
            let vue = this;
            if(configuration_data.next_step){
                vue.openFormModal(vue.modalChecklistConfiguration,configuration_data);
            }else if(!configuration_data.checklist.active){
                vue.finishConfigurationModal.endpoint = `/entrenamiento/checklist/v2/${configuration_data.checklist.id}/status`;
                vue.openSimpleModal(vue.finishConfigurationModal);
            }
        },
        async confirmUpdateStatus(){
            let vue = this;
            vue.showLoader();
            await vue.$http.put(vue.finishConfigurationModal.endpoint)
                .then(({data}) => {
                    vue.hideLoader();
                    vue.showAlert(data.data.msg);
                    vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                    vue.closeSimpleModal(vue.finishConfigurationModal);
                });
        }
    }
};
</script>
