<template>

    <div>
        <header class="page-header mt-5 py-0 mx-8">
            <div class="breadcrumb-holder container-fluid card v-card v-sheet theme--light elevation-0">
                <v-card-title>
                    <v-row class="justify-content-start">
                                 
                                    <!-- <v-col cols="3">
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
                                    </v-col> -->
                                    <v-col cols="6">
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
                    <v-spacer/>

          <!--           <DefaultInfoTooltip left
                        text="Recuerda cumplir con el horario de <br> inicio y final de tu programa." /> -->


                    <DefaultModalButton
                        label="Crear programa"
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
                                    <v-col cols="12">
                                        <DefaultTable
                                            :ref="dataTable.ref"
                                            :data-table="dataTable"
                                            :filters="filters"
                                            :default-sort-desc="true"
                                            @edit="openFormModal(modalFormOptions, $event)"
                                            @detail="openFormModal(modalDetailOptions, $event, 'detail', `Detalle de programa: ${$event.name}`)"
                                            @finish="openFormModal(modalFinishOptions, $event, 'finalizar', `Finalizar programa: ${$event.name}`)"
                                        />

                                        <BlockDetailModal
                                            :options="modalDetailOptions"
                                            width="60vw"
                                            :ref="modalDetailOptions.ref"
                                            @onDuplicate="openFormDuplicateModal"
                                            @onCancel="closeSimpleModal(modalDetailOptions)"
                                            @onConfirm="closeFormModal(modalDetailOptions, dataTable, filters)"
                                        />

                                      
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
import BlockDetailModal from "./BlockDetailModal";
// import BlockFormModal from "./BlockFormModal";

export default {
    components: {BlockDetailModal},
    // components: {BlockDetailModal, BlockFormModal, BlockFinishModal, BlockDirectionsModal,},
    props: ['usuario_id'],
    data: () => ({
        dataTable: {
            endpoint: '/programas/search',
            ref: 'BlockTable',
            headers: [
                // {text: "ID", value: "id", align: 'center', sortable: false},
                // {text: "Tipo", value: "type", sortable: false},
                // {text: "Nombre", value: "name"},
                {text: "", value: "custom_block", sortable: false},
                // {text: "Criterios", value: "criterion_values_count", sortable: false},
                // {text: "Rutas", value: "segments_count", sortable: false},
                // // {text: "# Invitados", value: "attendants_count", sortable: false, align: 'center'},
                // // {text: "Anfitrión", value: "host", sortable: false, align: 'center'},
                // {text: "Estado", value: "status", sortable: false, align: 'center',},
                // // {text: "Duración", value: "duration", align: 'center', sortable: false},
                // {text: "Fecha de creación", value: "created_at", align: 'center',},
                // {text: "Opciones", value: "actions", align: 'center', sortable: false},
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
       
        modalDateOptions: {
            ref: 'DateRangeFilter',
            open: false,
        },
        modalDetailOptions: {
            ref: 'BlockDetailModal',
            open: false,
            base_endpoint: '/programas',
            confirmLabel: 'Cerrar',
            hideCancelBtn: true
        },
        modalFormOptions: {
            ref: 'BlockFormlModal',
            open: false,
            base_endpoint: '/programas',
            confirmLabel: 'Guardar',
            resource: 'programa',
        },
        // modalFormDuplicateOptions: {
        //     ref: 'BlockFormlModal',
        //     open: false,
        //     base_endpoint: '/programas',
        //     confirmLabel: 'Guardar',
        //     resource: 'programa',
        //     action: 'duplicate',
        // },
        modalDateFilter1: {
            open: false,
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
            const url = `/programas/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.types = data.data.types
                    vue.selects.statuses = data.data.statuses
                })
        },

        openFormDuplicateModal(resource) {
            let vue = this
            vue.modalDetailOptions.open = false;
            vue.openFormModal(vue.modalFormOptions, resource, 'duplicate', 'Crear programa');
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

<style type="text/css">
    .custom-expansion-block.theme--light.v-expansion-panels .v-expansion-panel {
        background-color: inherit !important; 
        color: inherit !important; 
    }

    .custom-expansion-block .segments {

        border: 1px solid #cfcfcf;
        padding: 10px;
        margin: 10px auto;

    }
</style>
