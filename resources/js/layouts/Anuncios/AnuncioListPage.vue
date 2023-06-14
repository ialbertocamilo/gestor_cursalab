<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Anuncios
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'" @click="activity"/> -->
                <DefaultModalButton
                    :label="'Anuncio'"
                    @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!-- FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.modules"
                            v-model="filters.module"
                            label="Módulos"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
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
                :avoid_first_data_load="dataTable.avoid_first_data_load"
                @edit="openFormModal(modalOptions, $event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambio de estado de un <b>anuncio</b>')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminación de un <b>anuncio</b>')"
                @logs="
                    openFormModal(
                        modalLogsOptions,
                        $event,
                        'logs',
                        `Logs del Anuncio - ${$event.nombre}`
                    )
                "
            />

            <AnuncioFormModal
                width="50vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
            />

            <DefaultStatusModal
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalStatusOptions)"
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
                model_type="App\Models\Announcement"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />
        </v-card>
    </section>
</template>

<script>
import AnuncioFormModal from "./AnuncioFormModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import LogsModal from "../../components/globals/Logs";

export default {
    components: {
        AnuncioFormModal,
        DefaultStatusModal,
        DefaultDeleteModal,
        LogsModal
    },
    data() {
        return {
            dataTable: {
                avoid_first_data_load: false,
                endpoint: '/anuncios/search',
                ref: 'AnuncioTable',
                headers: [
                    {text: "Banner", value: "image", align: 'center', sortable: false},
                    {text: "Nombre", value: "nombre"},
                    {text: "Fecha de publicación", value: "publish_date", align: 'center', sortable: false},
                    {text: "Estado de publicación", value: "expired", align: 'center', sortable: false},
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
                        text: "Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
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
                ],
                more_actions: [
                    // {
                    //     text: "Actividad",
                    //     icon: 'fas fa-file',
                    //     type: 'action',
                    //     method_name: 'activity'
                    // },
                ]
            },
            selects: {
                modules: [],
                statuses: [
                    {id: null, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 2, name: 'Inactivos'},
                ],
            },
            filters: {
                q: '',
                module: null,
                active: 1
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"

            },
            modalOptions: {
                ref: 'AnuncioFormModal',
                open: false,
                base_endpoint: '/anuncios',
                resource: 'Anuncio',
                confirmLabel: 'Guardar',
            },
            modalStatusOptions: {
                ref: 'AnuncioStatusModal',
                open: false,
                base_endpoint: '/anuncios',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un anuncio!',
                        details: [
                            'Este anuncio no podrá ser visto por los usuarios.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un anuncio!',
                        details: [
                            'Este anuncio ahora podrá ser visto por los usuarios.'
                        ]
                    }
                },
                width: '408px'
            },
            modalDeleteOptions: {
                ref: 'AnuncioDeleteModal',
                open: false,
                base_endpoint: '/anuncios',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar un anuncio!',
                        details: [
                            'Este anuncio no podrá ser visto por los usuarios.',
                            'No se podrá recuperar.'
                        ],
                    }
                },
                width: '408px'
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
        // === check localstorage anuncio ===
        if(vue.dataTable.avoid_first_data_load) {
            vue.refreshDefaultTable(vue.dataTable, vue.filters, 1);
            const { storage: vademecumStorage } = vue.getStorageUrl('anuncio');
            vue.openFormModal(vue.modalOptions, { id: vademecumStorage.id });
        }
        // === check localstorage anuncio ===
    },
    created() {
        let vue = this;

        // === check localstorage anuncio ===
        const { status, storage: anuncioStorage } = vue.getStorageUrl('anuncio');
        if(status) {
            vue.filters.q = anuncioStorage.q;
            vue.filters.module = anuncioStorage.module[0]; // considerar que puede ser multimple
            vue.filters.active = anuncioStorage.active;

            vue.dataTable.avoid_first_data_load = true;
        }
        // === check localstorage anuncio ===
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/anuncios/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data.modules
                })
        },
        // reset(user) {
        //     let vue = this
        //     vue.consoleObjectTable(user, 'User to Reset')
        // },
        activity() {
            console.log('activity')
        },
        confirmModal() {
            // TODO: Call store or update USER
        },
    }
}
</script>
