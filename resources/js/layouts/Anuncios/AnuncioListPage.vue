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
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                default-sort-by="publish_date"
                :default-sort-desc="true"
                @edit="openFormModal(modalOptions, $event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar anuncio')"
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

        </v-card>
    </section>
</template>


<script>

import AnuncioFormModal from "./AnuncioFormModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";

export default {

    components: {
        AnuncioFormModal, DefaultStatusModal, DefaultDeleteModal
    }
    ,
    data() {
        return {
            dataTable: {
                endpoint: '/anuncios/search',
                ref: 'AnuncioTable',
                headers: [
                    {text: "Banner", value: "image", align: 'center', sortable: false},
                    {text: "Nombre", value: "nombre"},
                    {text: "Fecha de publicación", value: "publish_date", align: 'center', sortable: false},
                    {text: "Venció", value: "expired", align: 'center', sortable: false},
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
                modules: []
            },
            filters: {
                q: '',
                module: null
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
            },
            modalDeleteOptions: {
                ref: 'AnuncioDeleteModal',
                open: false,
                base_endpoint: '/anuncios',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
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
