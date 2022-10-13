<template>

    <div>
        <header class="page-header mt-5 py-0 mx-8">
            <div class="breadcrumb-holder container-fluid card v-card v-sheet theme--light elevation-0">
                <v-card-title>
                    Aulas Virtuales {{ usuario_id }} - {{ workspace_id }}
                    <v-spacer/>

<!--                    <v-btn icon color="primary"-->
<!--                           @click="openFormModal(modalDirectionsOptions, null, null, 'Recomendaciones')"-->
<!--                    >-->
<!--                        <v-icon v-text="'mdi-dots-vertical'"/>-->
<!--                    </v-btn>-->


                <DefaultModalButton
                    label="Open Modal Workspace"
                    @click="openFormModal(modalFormSegmentationOptions, { id: workspace_id }, 'segmentation', `Segmentación del workspace`)"/>

                <SegmentFormModal
                    :options="modalFormSegmentationOptions"
                    width="55vw"
                    model_type="App\Models\Workspace"
                    :model_id="null"
                    :ref="modalFormSegmentationOptions.ref"
                    @onCancel="closeSimpleModal(modalFormSegmentationOptions)"
                    @onConfirm="closeSimpleModal(modalFormSegmentationOptions)"
                  />


                    <DefaultInfoTooltip left
                        text="Recuerda cumplir con el horario de <br> inicio y final de tu reunión." />


                    <MeetingDirectionsModal
                        :options="modalDirectionsOptions"
                        :ref="modalDirectionsOptions.ref"
                        @onCancel="closeFormModal(modalDirectionsOptions)"
                    />

                    <DefaultModalButton
                        label="Crear reunión"
                        @click="openFormModal(modalFormOptions)"/>
                </v-card-title>
            </div>
        </header>

        <section class="client section-list">
            <div class="container-fluid">
                <div class="row">

                    <div class="col-sm-12">

                        <v-card class="card" elevation="0">
                            <v-card-text>
                                <v-row class="justify-content-start">
                                    <!--   <v-col cols="2">
                                          <DefaultSelect
                                              clearable
                                              label="Tipo"
                                              dense
                                              v-model="filters.type"
                                              :items="selects.types"
                                              item-text="name"
                                              @onChange="refreshDefaultTable(dataTable, filters, 1)"
                                          />
                                      </v-col> -->
                                    <v-col cols="3">
                                        <DefaultSelect
                                            clearable multiple
                                            label="Estado"
                                            dense
                                            v-model="filters.statuses"
                                            :items="selects.statuses"
                                            item-text="name"
                                            :show-select-all="false"
                                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
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
                                            label="Fecha"
                                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                                        />
                                    </v-col>
                                    <v-col cols="3">
                                        <DefaultInput
                                            clearable
                                            v-model="filters.q"
                                            dense
                                            label="Buscar por nombre..."
                                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                                            append-icon="mdi-magnify"
                                        />
                                    </v-col>

                                </v-row>
                                <v-row class="justify-content-start">
                                    <v-col cols="12">
                                        <DefaultTable
                                            :ref="dataTable.ref"
                                            :data-table="dataTable"
                                            :filters="filters"
                                            :default-sort-desc="true"
                                            @edit="openFormModal(modalFormOptions, $event)"
                                            @detail="openFormModal(modalDetailOptions, $event, 'detail', `Detalle de reunión: ${$event.name}`)"
                                            @finish="openFormModal(modalFinishOptions, $event, 'finalizar', `Finalizar reunión: ${$event.name}`)"
                                        />

                                        <MeetingDetailModal
                                            :options="modalDetailOptions"
                                            width="60vw"
                                            :ref="modalDetailOptions.ref"
                                            @onDuplicate="openFormDuplicateModal"
                                            @onCancel="closeSimpleModal(modalDetailOptions)"
                                            @onConfirm="closeFormModal(modalDetailOptions, dataTable, filters)"
                                        />

                                        <MeetingFinishModal
                                            :options="modalFinishOptions"
                                            width="60vw"
                                            :ref="modalFinishOptions.ref"
                                            @onCancel="closeSimpleModal(modalFinishOptions)"
                                            @onConfirm="closeFormModal(modalFinishOptions, dataTable, filters)"
                                        />

                                        <MeetingFormModal
                                            :options="modalFormOptions"
                                            width="50vw"
                                            :ref="modalFormOptions.ref"
                                            @onCancel="closeSimpleModal(modalFormOptions)"
                                            @onConfirm="closeFormModal(modalFormOptions, dataTable, filters)"
                                        />

                                        <!--   <MeetingFormModal
                                              :options="modalFormDuplicateOptions"
                                              width="50vw"
                                              :ref="modalFormDuplicateOptions.ref"
                                              @onCancel="closeSimpleModal(modalFormDuplicateOptions)"
                                              @onConfirm="closeFormModal(modalFormDuplicateOptions, dataTable, filters)"
                                          /> -->
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>
                    </div>
                </div>
            </div>

        </section>
    </div>

</template>

<script>
import SegmentFormModal from './../Blocks/SegmentFormModal';
import MeetingFinishModal from "./MeetingFinishModal";
import MeetingDirectionsModal from "./MeetingDirectionsModal";
import MeetingDetailModal from "./MeetingDetailModal";
import MeetingFormModal from "./MeetingFormModal";

export default {
    components: {MeetingDetailModal, MeetingFormModal, MeetingFinishModal, MeetingDirectionsModal, SegmentFormModal},
    props: ['usuario_id', 'workspace_id'],
    data: () => ({
        dataTable: {
            endpoint: '/aulas-virtuales/search',
            ref: 'MeetingTable',
            headers: [
                // {text: "ID", value: "id", align: 'center', sortable: false},
                // {text: "Tipo", value: "type", sortable: false},
                // {text: "Nombre", value: "name"},
                {text: "Nombre", value: "custom_meeting_name", sortable: false},
                // {text: "# Invitados", value: "attendants_count", sortable: false, align: 'center'},
                {text: "Anfitrión", value: "host", sortable: false, align: 'center'},
                {text: "Estado", value: "status", sortable: false, align: 'center',},
                // {text: "Duración", value: "duration", align: 'center', sortable: false},
                {text: "Fecha de inicio", value: "starts_at", align: 'center',},
                {text: "Opciones", value: "actions", align: 'center', sortable: false},
            ],
            actions: [
                {
                    text: "Editar",
                    icon: 'mdi mdi-pencil',
                    type: 'action',
                    method_name: 'edit',
                    show_condition: 'editable'
                },
                {
                    text: "Finalizar",
                    icon: 'fas fa-check',
                    type: 'action',
                    method_name: 'finish',
                    show_condition: 'is_live'
                },
                {
                    text: "Detalle",
                    icon: 'mdi mdi-eye',
                    type: 'action',
                    method_name: 'detail'
                },
            ],
            // more_actions: [
            //     {
            //         text: "Cancelar",
            //         icon: 'mdi mdi-cancel',
            //         type: 'action',
            //         method_name: 'cancel',
            //         show_condition: 'cancelable'
            //     },
            //     {
            //         text: "Eliminar",
            //         icon: 'mdi mdi-trash-can',
            //         type: 'action',
            //         method_name: 'delete',
            //         show_condition: 'deletable'
            //     },
            // ]
        },
        filters: {
            type: [],
            statuses: [],
            dates: [],
            // date: [],
            q: null
        },
        selects: {
            types: [],
            statuses: []
        },
        modalDirectionsOptions: {
            ref: 'MeetingDirectionsModal',
            open: false,
            base_endpoint: '/aulas-virtuales',
            resource: 'cuenta',
            hideConfirmBtn: true,
            cancelLabel: 'Cerrar',
        },
        modalDateOptions: {
            ref: 'DateRangeFilter',
            open: false,
        },
        modalDetailOptions: {
            ref: 'MeetingDetailModal',
            open: false,
            base_endpoint: '/aulas-virtuales',
            confirmLabel: 'Cerrar',
            hideCancelBtn: true
        },
        modalFormOptions: {
            ref: 'MeetingFormlModal',
            open: false,
            base_endpoint: '/aulas-virtuales',
            confirmLabel: 'Guardar',
            resource: 'reunión',
        },
        modalFormSegmentationOptions:{
            ref: 'SegmentFormModal',
            open: false,
            persistent: true,
            base_endpoint: '/segments',
            confirmLabel: 'Guardar',
            resource: 'segmentación',
        },
        // modalFormDuplicateOptions: {
        //     ref: 'MeetingFormlModal',
        //     open: false,
        //     base_endpoint: '/aulas-virtuales',
        //     confirmLabel: 'Guardar',
        //     resource: 'reunión',
        //     action: 'duplicate',
        // },
        modalDateFilter1: {
            open: false,
        },
        modalFinishOptions: {
            ref: 'MeetingFinishModal',
            open: false,
            base_endpoint: '/aulas-virtuales',
            cancelLabel: 'Cerrar',
            // hideConfirmBtn: true,
        },
    }),
    mounted() {
        let vue = this
        vue.getSelects();
        // if (vue.usuario_id)
        //     vue.modalFormOptions.usuario_id = vue.usuario_id
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/aulas-virtuales/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.types = data.data.types
                    vue.selects.statuses = data.data.statuses
                })
        },

        openFormDuplicateModal(resource) {
            let vue = this
            vue.modalDetailOptions.open = false;
            vue.openFormModal(vue.modalFormOptions, resource, 'duplicate', 'Crear reunión');
        },
        //      async activity() {
        //          console.log('entra');
        // let vue = this;
        // await axios.post("/descargarEventosEnVivo", {responseType: 'blob',
        // 		params: {
        // 			tipo: vue.filters.types,
        // 			master: vue.master,
        // 			filtro: vue.filters.status,
        // 		}
        // 	})
        // 	.then((res) => {
        // 		const downloadUrl = window.URL.createObjectURL(new Blob([res.data]));
        //         const link = document.createElement('a');
        //         link.href = downloadUrl;
        //         link.setAttribute('download', 'Aulas_Virtuales.xlsx'); //any other extension
        //         document.body.appendChild(link);
        //         link.click();
        //         link.remove();
        // 	})
        // 	.catch((err) => {
        // 		console.log(err);
        // 	});
        //      },
    }
}
</script>
