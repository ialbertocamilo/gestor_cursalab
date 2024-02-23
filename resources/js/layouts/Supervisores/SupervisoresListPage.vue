<template>
    <section class="section-list ">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Supervisores
                <v-spacer/>
                <DefaultModalButton
                    :label="'Criterios Globales'"
                    :showIcon="false"
                    @click="openFormModal(modalOptionsSCriteriosGlobales,null, null, 'Criterios Globales')"/>
                <DefaultModalButton
                    :label="'Supervisores'"
                    :showIcon="false"
                    @click="openFormModal(modalOptionsSupervisores,null, null, '+ Supervisores')"/>
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            placeholder="Buscar"
                            append-icon="mdi-magnify"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>

                    <v-col cols="4">
                        <DefaultAutocomplete
                            clearable dense
                            :items="modulos"
                            v-model="filters.subworkspace"
                            placeholder="Módulo"
                            multiple
                            :count-show-values="1"
                            item-text="name"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar un <b>supervisor</b>')"
                @asignarUsuarios="openFormModal(modalOptionsAsignarUsuarios, $event, null, 'Asignar usuarios al Supervisor')"
                @segmentation="openFormModal(modalFormSegmentationOptions, $event, 'segmentation', `Supervisor - ${$event.nombre}`)"
            />

            <SegmentFormModal
                :options="modalFormSegmentationOptions"
                width="55vw"
                model_type="App\Models\User"
                :model_id="null"
                :code="'user-supervise'"
                :tabs_title="''"
                limit-one
                :ref="modalFormSegmentationOptions.ref"
                @onCancel="closeSimpleModal(modalFormSegmentationOptions)"
                @onConfirm="closeFormModal(modalFormSegmentationOptions, dataTable, filters)"
                for_section="supervisores"
            />

            <DefaultDeleteModal
                :options="modalDeleteOptions"
                :ref="modalDeleteOptions.ref"
                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalDeleteOptions)"
            />
            <CreateSupervisorModal
                width="60vw"
                :ref="modalOptionsSupervisores.ref"
                :options="modalOptionsSupervisores"
                @onConfirm="closeFormModal(modalOptionsSupervisores, dataTable, filters)"
                @onCancel="closeFormModal(modalOptionsSupervisores)"
            />
            <CriterioGlobalesModal
                width="60vw"
                :ref="modalOptionsSCriteriosGlobales.ref"
                :options="modalOptionsSCriteriosGlobales"
                @onConfirm="changeLabelModal"
                @onCancel="changeLabelModal"
            />
            <!--            <AsignarUsuariosASupervisorModal-->
            <!--                width="60vw"-->
            <!--                :ref="modalOptionsAsignarUsuarios.ref"-->
            <!--                :options="modalOptionsAsignarUsuarios"-->
            <!--                @onConfirm="closeFormModal(modalOptionsAsignarUsuarios, dataTable, filters)"-->
            <!--                @onCancel="closeFormModal(modalOptionsAsignarUsuarios)"-->
            <!--            />-->
            <!--            <AsignarCriteriosASupervisorModal-->
            <!--                width="40vw"-->
            <!--                :ref="modalOptionsAsignarCriterios.ref"-->
            <!--                :options="modalOptionsAsignarCriterios"-->
            <!--                @onConfirm="closeFormModal(modalOptionsAsignarCriterios, dataTable, filters)"-->
            <!--                @onCancel="closeFormModal(modalOptionsAsignarCriterios)"-->
            <!--            />-->

        </v-card>
    </section>
</template>
<script>
import CreateSupervisorModal from "./CreateSupervisorModal";
// import AsignarUsuariosASupervisorModal from "./AsignarUsuariosASupervisorModal";
// import AsignarCriteriosASupervisorModal from "./AsignarCriteriosASupervisorModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import CriterioGlobalesModal from './CriterioGlobalesModal';
import SegmentFormModal from "../Blocks/SegmentFormModal";

export default {
    components: {
        CreateSupervisorModal,
        DefaultDeleteModal,
        // AsignarUsuariosASupervisorModal,
        // AsignarCriteriosASupervisorModal,
        CriterioGlobalesModal,
        SegmentFormModal
    },
    data() {
        return {
            filters: {
                q: '',
                subworkspace: []
            },
            dataTable: {
                endpoint: '/supervisores/search',
                ref: 'SupervisorTable',
                headers: [
                    {text: "Módulo", value: "modulo", align: 'start', sortable: false},
                    {text: "Nombre y Apellidos", value: "nombre", align: 'start', sortable: false},
                    // {text: "Apellidos", value: "apellidos", align: 'start', sortable: false},
                    {text: "Documento", value: "dni", align: 'start', sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    // {
                    //     text: "Criterios",
                    //     icon: 'fas fa-th-large',
                    //     type: 'action',
                    //     method_name: 'asignarCriterios',
                    //     count: 'segments_count'
                    // },
                    // {
                    //     text: "Usuarios",
                    //     icon: 'fas fa-user',
                    //     type: 'action',
                    //     method_name: 'asignarUsuarios',
                    //     count: 'users_count'
                    // },
                    {
                        text: "Segmentación",
                        icon: 'fa fa-square',
                        type: 'action',
                        count: 'segments_count',
                        method_name: 'segmentation',
                        show_condition: "show_segmentation",
                    },
                    {
                        text: "Eliminar",
                        icon: 'fas fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                ],
                more_actions: []
            },
            modalFormSegmentationOptions: {
                ref: 'SegmentFormModal',
                open: false,
                persistent: true,
                base_endpoint: '/segments',
                confirmLabel: 'Guardar',
                resource: 'segmentación',
            },
            modalOptionsSupervisores: {
                ref: 'CreateSupervisorModal',
                open: false,
                base_endpoint: '/supervisores',
                title: 'Registrar por enlace',
                confirmLabel: 'Guardar',
                showCloseIcon: true
            },
            // modalOptionsAsignarUsuarios: {
            //     ref: 'AsignarUsuarios',
            //     open: false,
            //     base_endpoint: '/supervisores',
            //     title: 'Asignar Usuarios',
            //     confirmLabel: 'Guardar',
            //     showCloseIcon: true
            // },
            // modalOptionsAsignarCriterios: {
            //     ref: 'AsignarCriterios',
            //     open: false,
            //     base_endpoint: '/supervisores',
            //     title: 'Asignar criterios',
            //     confirmLabel: 'Guardar',
            //     showCloseIcon: true
            // },
            modalDeleteOptions: {
                ref: 'SupervisorDeleteModal',
                open: false,
                base_endpoint: '/supervisores',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
                showCloseIcon: true,
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar un supervisor!',
                        details: [
                            'El supervisor ya no podrá descargar reportes.',
                            'Se eliminará junto con su segmentación de usuarios.'
                        ],
                    }
                },
            },
            modalOptionsSCriteriosGlobales: {
                ref: 'AsignarCriteriosGlobalesModal',
                open: false,
                hideCancelBtn: true,
                base_endpoint: '/supervisores',
                title: 'Criterios Globales',
                confirmLabel: 'Siguiente',
                cancelLabel: 'Atras',
                showCloseIcon: true,
                loading: false
            },
            modulos: []
        }
    },
    mounted() {
        this.getInitalData();
    },
    methods: {
        async getInitalData() {
            let vue = this;
            await axios.get('/supervisores/modulos').then(({data}) => {
                vue.modulos = data.data.modulos;
            })
        },
        changeLabelModal(step) {
            let vue = this;
            console.log(step);
            switch (step) {
                case 1:
                    vue.modalOptionsSCriteriosGlobales.hideCancelBtn = true;
                    vue.modalOptionsSCriteriosGlobales.confirmLabel = 'Siguiente';
                    break;
                case 2:
                    vue.modalOptionsSCriteriosGlobales.hideCancelBtn = false;
                    vue.modalOptionsSCriteriosGlobales.confirmLabel = 'Guardar';
                    break;
                case 3:
                    vue.modalOptionsSCriteriosGlobales.hideCancelBtn = true;
                    vue.modalOptionsSCriteriosGlobales.confirmLabel = 'Siguiente';
                    vue.closeFormModal(vue.modalOptionsSCriteriosGlobales, vue.dataTable, vue.filters);
                    break;
                default:
                    vue.closeFormModal(vue.modalOptionsSCriteriosGlobales, vue.dataTable, vue.filters);
                    break;
            }
        }
    }
}
</script>
