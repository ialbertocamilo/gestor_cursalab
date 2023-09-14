<template>
    <section class="section-list ">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Diplomas
                <v-spacer/>
                <DefaultModalButton
                    :label="'Diploma'"
                    @click="openCRUDPage(`/diploma/create`)"/>
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por tÍtulo..."
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
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar registro')"
            />
        </v-card>
        
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

    </section>
</template>

<script>
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";

export default {
    components: { DefaultStatusModal, DefaultDeleteModal },
    data() {
        return {
            dataTable: {
                endpoint: '/diplomas/search',
                ref: 'DiplomaTable',
                headers: [
                    {text: "Previsualización", value: "image", align: 'center', sortable: false },
                    {text: "TÍtulo", value: "title"},
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
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
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
                ref: 'DiplomaDeleteModal',
                open: false,
                base_endpoint: '/diplomas',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
            modalStatusOptions: {
                ref: 'DiplomaStatusModal',
                open: false,
                base_endpoint: '/diplomas',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            }
        }
    }
}

/*
certifications: [
    {id: 1, name: 'Diploma Cursalab - Default'},
    {id: 2, name: 'Diploma Cursalab - [Con fecha]'},
    {id: 3, name: 'Diploma Cursalab - [Con fecha y nota]'},
],
*/
</script>
