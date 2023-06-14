<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Beneficios
                <v-spacer/>
                <DefaultModalButton :label="'Beneficios'" @click="openModalSelectActivitys()"/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar beneficios"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="4">
                        <div class="bx_max_colaboradores">
                            <div class="img"><img src="/img/benefits/max_colaborador.svg"></div>
                            <p>Max cant. permitida por colaborador</p>
                            <span>5</span>
                            <div class="btns_change_max_col">
                                <div class="btn_change_max_col_up">
                                    <img src="/img/benefits/chevron_top.svg">
                                </div>
                                <div class="btn_change_max_col_down">
                                    <img src="/img/benefits/chevron_bottom.svg">
                                </div>
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openModalSelectActivitys($event)"
                @delete="openFormModal(modalDeleteOptions,$event,'delete','Eliminar Beneficio')"
                @logs="openFormModal(modalLogsOptions,$event,'logs',`Logs del Beneficio - ${$event.title}`)"
                @addSpeaker="addSpeaker($event)"
            />
        </v-card>

        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />

        <ModalSelectActivity
                :ref="modalSelectActivity.ref"
                v-model="modalSelectActivity.open"
                width="650px"
            @onCancel="modalSelectActivity.open = false"
            />
    </section>
</template>

<script>
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import ModalSelectActivity from "../../components/Benefit/ModalSelectActivity";

export default {
    components: {
        DefaultDeleteModal,
        ModalSelectActivity
    },
    mounted() {
        let vue = this;
    },
    data() {
        return {
            dataTable: {
                endpoint: '/beneficios/search',
                ref: 'BenefitTable',
                headers: [
                    {text: "Nombre", value: "title", align: 'start', sortable: true},
                    {text: "Speaker", value: "benefit_speaker", align: 'center', sortable: false},
                    {text: "Tipo", value: "benefit_type", align: 'center', sortable: false},
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
                        text: "Segmentación",
                        icon: 'fa fa-square',
                        type: 'action',
                        count: 'segments_count',
                        method_name: 'segmentation'
                    },
                ],
                more_actions: [
                    {
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    {
                        text: "Duplicar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                    {
                        text: "Gestión de colab.",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                ]
            },

            modalSelectActivity: {
                ref: 'ModalSelectActivity',
                open: false,
                endpoint: '',
            },

            filters: {
                q: null
            },
            btn_reload_data: false,
            mostrarFiltros: true,
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalDeleteOptions: {
                ref: 'BeneficioDeleteModal',
                open: false,
                base_endpoint: '/beneficios',
                endpoint: '',
            },
            file: null,
        }
    },
    methods: {
        addSpeaker( item ) {
            console.log(item);
        },
        async openModalSelectActivitys() {
            let vue = this
            vue.modalSelectActivity.open = true
        }
    }
};
</script>
<style lang="scss">
.bx_max_colaboradores {
    display: flex;
    align-items: center;
}
.bx_max_colaboradores p {
    font-size: 14px;
    margin: 0 6px;
    font-family: 'open sans';
    color: #5458EA;
}
.bx_max_colaboradores span {
    font-size: 16px;
    font-family: 'open sans';
    color: #5458EA;
    font-weight: bold;
}
.btns_change_max_col {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    line-height: 1;
    margin-left: 5px;
}
</style>
