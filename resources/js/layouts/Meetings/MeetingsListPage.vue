<template>
    <div>
        <header class="page-header mt-5 py-0 mx-8">
            <div
                class="breadcrumb-holder container-fluid card v-card v-sheet theme--light elevation-0"
            >
                <v-card-title>
                    Sesiones Live
                    <v-spacer/>
                    <DefaultInfoTooltip
                        class="mr-5"
                        bottom
                        text="Recuerda cumplir con el horario de <br> inicio y final de tu reunión." />
                    <v-spacer/>
                     <!-- {{ usuario_id }} - {{ workspace_id }} -->

<!--                    <v-btn icon color="primary"-->
<!--                           @click="openFormModal(modalDirectionsOptions, null, null, 'Recomendaciones')"-->
<!--                    >-->
<!--                        <v-icon v-text="'mdi-dots-vertical'"/>-->
<!--                    </v-btn>-->


                <DefaultModalButton v-if="superuser"
                    label="Configurar anfitriones"
                    icon_name="mdi-contacts-outline"
                    text
                    class="---btn_anf"
                    @click="openFormModal(modalFormSegmentationOptions, { id: workspace_id }, 'segmentation', `Segmentación de Anfitriones`)"/>

                <SegmentFormModal
                    :options="modalFormSegmentationOptions"
                    width="55vw"
                    for_section="aulas_virtuales"
                    model_type="App\Models\Workspace"
                    :model_id="null"
                    :ref="modalFormSegmentationOptions.ref"
                    @onCancel="closeSimpleModal(modalFormSegmentationOptions)"
                    @onConfirm="closeSimpleModal(modalFormSegmentationOptions)"
                  />

                    <MeetingDirectionsModal
                        :options="modalDirectionsOptions"
                        :ref="modalDirectionsOptions.ref"
                        @onCancel="closeFormModal(modalDirectionsOptions)"
                    />

                    <DefaultModalButton
                        label="Crear sesión"
                        :icon=false
                        class="btn_crear"
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
                                            clearable
                                            multiple
                                            label="Estado"
                                            dense
                                            v-model="filters.statuses"
                                            :items="selects.statuses"
                                            item-text="name"
                                            :show-select-all="false"
                                            @onChange="
                                                refreshDefaultTable(
                                                    dataTable,
                                                    filters,
                                                    1
                                                )
                                            "
                                        />
                                    </v-col>
                                    <v-col cols="3">
                                        <DefaultInputDate
                                            clearable
                                            dense
                                            range
                                            :referenceComponent="
                                                'modalDateFilter1'
                                            "
                                            :options="modalDateFilter1"
                                            v-model="filters.dates"
                                            label="Fecha"
                                            @onChange="
                                                refreshDefaultTable(
                                                    dataTable,
                                                    filters,
                                                    1
                                                )
                                            "
                                        />
                                    </v-col>
                                    <v-col cols="3">
                                        <DefaultInput
                                            clearable
                                            v-model="filters.q"
                                            dense
                                            label="Buscar por nombre"
                                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                                            append-icon="mdi-magnify"
                                        />
                                    </v-col>
                                </v-row>
                                <v-row class="justify-content-start tableMeeting">
                                    <v-col cols="12">
                                        <DefaultTable
                                            :ref="dataTable.ref"
                                            :data-table="dataTable"
                                            :filters="filters"
                                            :default-sort-desc="true"
                                            @edit="
                                                openFormModal(
                                                    modalFormOptions,
                                                    $event
                                                )
                                            "
                                            @detail="
                                                openFormModal(
                                                    modalDetailOptions,
                                                    $event,
                                                    'detail',
                                                    `Detalle de reunión: ${$event.name}`
                                                )
                                            "
                                            @finish="
                                                openFormModal(
                                                    modalFinishOptions,
                                                    $event,
                                                    'finalizar',
                                                    `Finalizar reunión: ${$event.name}`
                                                )
                                            "
                                            @logs="
                                                openFormModal(
                                                    modalLogsOptions,
                                                    $event,
                                                    'logs',
                                                    `Logs de la Reunión - ${$event.name}`
                                                )
                                            "
                                        />

                                        <MeetingDetailModal
                                            :options="modalDetailOptions"
                                            width="60vw"
                                            :ref="modalDetailOptions.ref"
                                            @onDuplicate="
                                                openFormDuplicateModal
                                            "
                                            @onCancel="
                                                closeSimpleModal(
                                                    modalDetailOptions
                                                )
                                            "
                                            @onConfirm="
                                                closeFormModal(
                                                    modalDetailOptions,
                                                    dataTable,
                                                    filters
                                                )
                                            "
                                        />

                                        <MeetingFinishModal
                                            :options="modalFinishOptions"
                                            width="60vw"
                                            :ref="modalFinishOptions.ref"
                                            @onCancel="
                                                closeSimpleModal(
                                                    modalFinishOptions
                                                )
                                            "
                                            @onConfirm="
                                                closeFormModal(
                                                    modalFinishOptions,
                                                    dataTable,
                                                    filters
                                                )
                                            "
                                        />

                                        <MeetingFormModal
                                            :options="modalFormOptions"
                                            width="50vw"
                                            :ref="modalFormOptions.ref"
                                            @onCancel="
                                                closeSimpleModal(
                                                    modalFormOptions
                                                )
                                            "
                                            @onConfirm="
                                                closeFormModal(
                                                    modalFormOptions,
                                                    dataTable,
                                                    filters
                                                )
                                            "
                                        /><LogsModal
                                            :options="modalLogsOptions"
                                            width="55vw"
                                            :model_id="null"
                                            model_type="App\Models\Meeting"
                                            :ref="modalLogsOptions.ref"
                                            @onCancel="
                                                closeSimpleModal(
                                                    modalLogsOptions
                                                )
                                            "
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
import SegmentFormModal from "./../Blocks/SegmentFormModal";
import MeetingFinishModal from "./MeetingFinishModal";
import MeetingDirectionsModal from "./MeetingDirectionsModal";
import MeetingDetailModal from "./MeetingDetailModal";
import MeetingFormModal from "./MeetingFormModal";
import LogsModal from "../../components/globals/Logs";

export default {
    components: {
        MeetingDetailModal,
        MeetingFormModal,
        MeetingFinishModal,
        MeetingDirectionsModal,
        LogsModal,
        SegmentFormModal
    },
    // props: ['usuario_id', 'workspace_id', 'superuser'],
    props: {
        usuario_id: {
            type: Number | String,
            required: true
        },
        workspace_id: {
            type: Number | String,
            required: true
        },
        superuser: {
            type: Boolean,
            required: true
        }
    },
    data: () => ({
        dataTable: {
            endpoint: "/aulas-virtuales/search",
            ref: "MeetingTable",
            headers: [
                // {text: "ID", value: "id", align: 'center', sortable: false},
                // {text: "Tipo", value: "type", sortable: false},
                // {text: "Nombre", value: "name"},
                {text: "Nombre", value: "custom_meeting_name", sortable: false},
                {text: "Anfitrión", value: "host", sortable: false, align: 'center'},
                {text: "Participantes", value: "attendants_count", sortable: false, align: 'center'},
                {text: "Código", value: "prefix", sortable: false, align: 'center',tooltip: 'Puedes ingresar este código para validar que el usuario ingreso a la reunión desde nuestra plataforma.'},
                {text: "Estado", value: "status_meeting", sortable: false, align: 'center',},
                // {text: "Duración", value: "duration", align: 'center', sortable: false},
                {text: "Fecha de inicio", value: "starts_at", align: 'center', sortable: false,},
                {text: "Horario", value: "starts_at_horario", align: 'center', sortable: false,},
                {text: "Duración", value: "starts_at_duracion", align: 'center', sortable: false,},
                {text: "Opciones", value: "actions", align: 'center', sortable: false},
            ],
            actions: [
                {
                    text: "Editar",
                    icon: "mdi mdi-pencil",
                    type: "action",
                    method_name: "edit",
                    show_condition: "editable"
                },
                {
                    text: "Finalizar",
                    icon: "fas fa-check",
                    type: "action",
                    method_name: "finish",
                    show_condition: "is_live"
                },
                {
                    text: "Detalle",
                    icon: "mdi mdi-eye",
                    type: "action",
                    method_name: "detail"
                },
                {
                    text: "Logs",
                    icon: "mdi mdi-database",
                    type: "action",
                    show_condition: "is_super_user",
                    method_name: "logs"
                }
            ]
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
            ref: "MeetingDirectionsModal",
            open: false,
            base_endpoint: "/aulas-virtuales",
            resource: "cuenta",
            hideConfirmBtn: true,
            cancelLabel: "Cerrar"
        },
        modalDateOptions: {
            ref: "DateRangeFilter",
            open: false
        },
        modalDetailOptions: {
            ref: "MeetingDetailModal",
            open: false,
            base_endpoint: "/aulas-virtuales",
            confirmLabel: "Cerrar",
            hideCancelBtn: true
        },
        modalLogsOptions: {
            ref: "LogsModal",
            open: false,
            showCloseIcon: true,
            persistent: true,
            base_endpoint: "/search"
        },
        modalFormOptions: {
            ref: "MeetingFormlModal",
            open: false,
            base_endpoint: "/aulas-virtuales",
            confirmLabel: "Guardar",
            resource: "sesión"
        },
        modalFormSegmentationOptions: {
            ref: "SegmentFormModal",
            open: false,
            persistent: true,
            base_endpoint: "/segments",
            confirmLabel: "Guardar",
            resource: "segmentación"
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
            open: false
        },
        modalFinishOptions: {
            ref: "MeetingFinishModal",
            open: false,
            base_endpoint: "/aulas-virtuales",
            cancelLabel: "Cerrar"
            // hideConfirmBtn: true,
        }
    }),
    mounted() {
        let vue = this;
        vue.getSelects();
        // if (vue.usuario_id)
        //     vue.modalFormOptions.usuario_id = vue.usuario_id
    },
    methods: {
        getSelects() {
            let vue = this;
            const url = `/aulas-virtuales/get-list-selects`;
            vue.$http.get(url).then(({ data }) => {
                vue.selects.types = data.data.types;
                vue.selects.statuses = data.data.statuses;
            });
        },

        openFormDuplicateModal(resource) {
            let vue = this;
            vue.modalDetailOptions.open = false;
            vue.openFormModal(
                vue.modalFormOptions,
                resource,
                "duplicate",
                "Crear sesión"
            );
        }
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
};
</script>
<style lang="scss">
.tableMeeting table tbody td {
    font-family: "Nunito", sans-serif;
    font-weight: 400;
    font-size: 13px !important;
}
// .tableMeeting .v-data-table>.v-data-table__wrapper>table>tbody>tr:not(:last-child)>td:last-child,
// .tableMeeting .v-data-table>.v-data-table__wrapper>table>tbody>tr:not(:last-child)>td:not(.v-data-table__mobile-row),
// .tableMeeting .v-data-table>.v-data-table__wrapper>table>tbody>tr:not(:last-child)>th:last-child,
// .tableMeeting .v-data-table>.v-data-table__wrapper>table>tbody>tr:not(:last-child)>th:not(.v-data-table__mobile-row),
// .tableMeeting .v-data-table>.v-data-table__wrapper>table>thead>tr:last-child>th {
//     border-bottom: 1px solid #94dddb;
// }
.tableMeeting .v-data-table>.v-data-table__wrapper>table>thead>tr>th {
    font-family: "Nunito", sans-serif;
    font-size: 13px !important;
    font-weight: 700;
}
.tableMeeting button.v-icon{
    color: #008FFB;
}
// .tableMeeting button.v-icon.v-icon--disabled {
//     color: #94DDDB !important;
// }
// .v-tooltip__content {
//     background-color: #fff;
//     color: #5757EA;
//     border: 1px solid #5757EA;
//     border-radius: 10px;
//     box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
// }
button.btn_anf.primary {
    background-color: #fff !important;
    color: #5458ea !important;
    border-color: #5458ea !important;
    border: 1px solid;
    font-family: "Nunito", sans-serif;
    padding-left: 10px !important;
    padding-right: 10px !important;
    height: 42px !important;
}
button.btn_anf.primary .icon_tmp {
    margin-right: 5px;
    max-width: 20px;
}
button.btn_anf.primary .icon_tmp img{
    max-width: 20px;
}
// button.btn_crear {
//     font-family: "Nunito", sans-serif;
//     padding-left: 10px !important;
//     padding-right: 10px !important;
//     min-width: 175px !important;
//     height: 42px !important;
// }
</style>
