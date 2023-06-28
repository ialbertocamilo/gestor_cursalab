<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Speakers
                <v-spacer/>
                <DefaultModalButton :label="'Agregar speakers'" @click="openCRUDPage('/speakers/create')"/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar speaker"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openModalSelectActivitys($event)"
                @delete="openFormModal(modalDeleteOptions,$event,'delete','Cambio de estado de un speaker')"
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
                @selectTypeActivityModal="selectTypeActivityModal"
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
                endpoint: '/speakers/search',
                ref: 'SpeakerTable',
                headers: [
                    {text: "Perfil", value: "perfil_speaker", align: 'start', sortable: false},
                    {text: "Nombre", value: "name", align: 'start', sortable: true},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'route',
                        route: 'edit_route'
                    },
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    }
                ],
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
            modalDeleteOptions: {
                ref: 'SpeakerDeleteModal',
                open: false,
                base_endpoint: '/speakers',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar un Speaker!',
                        details: [
                            'Este speaker no podrá ser visto por los usuarios.',
                            'La información eliminada no podra recuperarse'
                        ],
                    }
                },
                width: '408px'
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
        },
        selectTypeActivityModal( value ) {
            window.location.href = '/beneficios/create?type=' + value;
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
