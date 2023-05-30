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
                endpoint: '/votaciones/search',
                ref: 'VotacionesTable',
                headers: [
                    {text: "Camapaña", value: "campaign", align: 'center', sortable: false },
                    {text: "F.Inicio - F.Final", value: "date"},
                    {text: "Etapa", value: "stage"},
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
                ],
                more_actions:[
                    
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
            }
        }
    }
}
</script>
