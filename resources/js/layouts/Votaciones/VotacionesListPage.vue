<template>
    <section class="section-list ">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Votaciones
                <v-spacer/>
                <DefaultModalButton
                    :label="'Agregar campaña'"
                    @click="openCRUDPage(`/votacion/create`)"/>
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.statuses"
                            v-model="filters.active"
                            label="Estado"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            item-text="name"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @stage="changeStateStageCampaign($event)"
                @duplicate="openFormModal(modalDuplicateOptions, $event, 'status', 'Duplicar Registro')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar registro')"
                @report="checkDuplicateCampaign({ type:'report', options:modalReportOptions }, 
                         $event, 
                         'Campaña incompleta',
                         '¡No puedes acceder a reportes!',
                         'Debes completar todos los campos de la campaña para acceder a esta funcionalidad.')"

                @detail="checkDuplicateCampaign({ type:'route' , options:'detail_route' }, 
                         $event, 
                         'Campaña incompleta', 
                         '¡No puedes ingresar a tu campaña!', 
                         'Debes completar todos los campos de la campaña para acceder a esta funcionalidad.')"

                @status="checkDuplicateCampaign({ type:'action', options:modalStatusOptions }, 
                         $event, 
                         'Campaña incompleta',
                         '¡No puedes activar tu campaña!',
                         'Debes completar todos los campos de la campaña para activarla.')"
            />
        </v-card>
        
        <!-- === DUPLICATE MODAL === -->
        <DefaultDuplicateModal
            :options="modalDuplicateOptions"
            :ref="modalDuplicateOptions.ref"
            @onConfirm="closeFormModal(modalDuplicateOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalDuplicateOptions)"
        />

        <!-- === DELETE MODAL ===  -->
        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />

        <!-- === STATUS MODAL ===  -->
        <DefaultStatusModal
            :options="modalStatusOptions"
            :ref="modalStatusOptions.ref"
            @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalStatusOptions)"
        />

        <!-- === ALERT MODAL === -->
        <VotacionesAlertDuplicateModal
            :ref="modalCheckDuplicateOptions.ref"
            :options="modalCheckDuplicateOptions"
            @onCancel="closeFormModal(modalCheckDuplicateOptions)"
            @onConfirm="closeFormModal(modalCheckDuplicateOptions)"
        />

        <!-- === STAGES MODAL === -->
        <VotacionesChangeStagesModal
            :ref="modalChangeStageOptions.ref"
            :options="modalChangeStageOptions"
            @onCancel="closeFormModal(modalChangeStageOptions)"
            @onConfirm="closeFormModal(modalChangeStageOptions,  dataTable, filters)"
        />

        <!-- === REPORT MODAL === -->
        <VotacionesReportModal 
            :ref="modalReportOptions.ref"
            :options="modalReportOptions"
            @onCancel="closeFormModal(modalReportOptions)"
            @onConfirm="closeFormModal(modalReportOptions,  dataTable, filters)"
        />

    </section>
</template>

<script>
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import DefaultDuplicateModal from "../Default/DefaultDuplicateModal";

import VotacionesAlertDuplicateModal from "./VotacionesAlertDuplicateModal.vue";
import VotacionesChangeStagesModal from "./VotacionesChangeStagesModal.vue";
import VotacionesReportModal from "./VotacionesReportModal.vue";

export default {
    components: { DefaultStatusModal, DefaultDeleteModal, DefaultDuplicateModal, VotacionesAlertDuplicateModal, VotacionesChangeStagesModal, VotacionesReportModal },
    data() {
        return {
            dataTable: {
                endpoint: '/votaciones/search',
                ref: 'VotacionesTable',
                headers: [
                    {text: "Campaña", value: "title", align: 'left', sortable: false },
                    {text: "F.Inicio - F.Final", value: "inicio_fin", sortable: false },
                    {text: "Etapa", value: "etapa", align: 'center', sortable: false },
                    {text: "Opciones", value: "actions", align: 'center', sortable: false },

                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'route',
                        route: 'edit_route'
                    },
                    {
                        text: "Detalle",
                        icon: 'mdi mdi-eye',
                        type: 'action',
                        method_name: 'detail'
                    },
                    {
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                ],
                more_actions:[
                    {
                        text: "Duplicar",
                        icon: 'fas fa-copy',
                        type: 'action',
                        method_name: 'duplicate'
                    },
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                    {
                        text: "Reportes",
                        icon: 'far fa-clipboard',
                        type: 'action',
                        method_name: 'report'
                    }
                ]
            },
            selects: {
                modules: [],
                statuses: [
                    {id: null, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 0, name: 'Inactivos'},
                ],
            },
            filters: {
                q: '',
                subworkspace_id: null,
                active: null,
            },
            modalDeleteOptions: {
                ref: 'VotacionDeleteModal',
                open: false,
                base_endpoint: '/votaciones',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
            modalStatusOptions: {
                ref: 'VotacionStatusModal',
                open: false,
                base_endpoint: '/votaciones',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalDuplicateOptions: {
                ref: 'VotacionDuplicateModal',
                open: false,
                base_endpoint: '/votaciones',
                contentText: '¿Desea duplicar/copiar este registro?',
                endpoint: '',
            },
            modalReportOptions:{
                ref: 'VotacionReporteModal',
                open: false,
                confirmLabel: 'Aceptar',
                base_endpoint: '/votaciones',
                contentText: '',
                endpoint: '',
                showCloseIcon: true,
                hideConfirmBtn: true,
                resource: {
                    stage_votation: null,
                    campaign:{}
                }
            },
            modalCheckDuplicateOptions: {
                ref: 'VotacionCheckDuplicateModal',
                open: false,
                confirmLabel: 'Entendido',
                subTitle:'¡No puedes activar tu campaña!',
                contentText:'Debes completar todos los campos de la campaña para activarla.',
                showCloseIcon: true,
                hideCancelBtn: true
            },
            modalChangeStageOptions: {
                ref: 'VotacionChangeStageModal',
                open: false,
                confirmLabel: 'Guardar',
                base_endpoint: '/votaciones/status/stages',
                contentText:'Aqui puedes habilitar o deshabilitar las etapas de tu campaña.',
                showCloseIcon: true,
                resource:{
                    stages:{
                        stage_content: null,
                        stage_postulate: null,
                        stage_votation: null
                    },
                    campaign_id: null
                }
            }
        }
    },
    methods: {
        checkDuplicateCampaign(dataOptions, event, title, subTitle = null, contentText = null) {
            let vm = this;
            vm.showLoader();
            
            if(!event.active) {
                vm.$http.get('/votaciones/check_duplicate/'+event.id).then((res) => {
                    const { data } = res.data;
                    vm.hideLoader();

                    if(data) {
                        vm.openModalOrRedirectPage(dataOptions, event); // open
                    }else {

                        if(subTitle) vm.modalCheckDuplicateOptions.subTitle = subTitle;
                        if(contentText) vm.modalCheckDuplicateOptions.contentText = contentText;

                        vm.openFormModal(vm.modalCheckDuplicateOptions, null, 'status', title);
                    }
                }); 
            } else {
                vm.hideLoader();
                vm.openModalOrRedirectPage(dataOptions, event); // open
            }
        },
        openModalOrRedirectPage(dataOptions, event) {
            const vm = this;
            const { type, options } = dataOptions; 

            switch(type) {
                case 'report':
                    // console.log(event);
                    options.resource.campaign = event;
                    options.resource.campaign_id = event.id; 
                    options.resource.stage_votation = event.stages.stage_votation; 

                    vm.openFormModal(options, event, 'status', 'Generar reporte');
                break;
                case 'action': 
                    vm.openFormModal(options, event, 'status', 'Actualizar estado');
                break;
                case 'route': 
                    window.location.href = event[options];
                break;
            }
        },
        changeStateStageCampaign(event) {
            let vm = this;

            vm.modalChangeStageOptions.resource.stages = { ...event.stages };
            vm.modalChangeStageOptions.resource.campaign_id = event.id;
            
            vm.openFormModal(vm.modalChangeStageOptions, event, 'status', 'Configurar etapas');
        }
    }
}
</script>
