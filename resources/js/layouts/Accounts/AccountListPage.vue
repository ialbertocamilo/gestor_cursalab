<template>

    <div>

        <header class="page-header">
            <div class="breadcrumb-holder container-fluid">
                <v-card-title>
                    <!--     <ul class="breadcrumb ">
                            <li class="breadcrumb-item"><a href="/aulas-virtuales">Anuncios</a></li>
                            <li class="breadcrumb-item active"><a href="/aulas-virtuales/cuentas">Cuentas</a></li>
                        </ul> -->
                    <a href="/aulas-virtuales">Aulas Virtuales</a> &nbsp; / Cuentas
                    <v-spacer/>

                    <v-btn icon color="primary"
                           @click="openFormModal(modalInfoOptions, null, null, 'Información sobre cuentas')"
                    >
                        <v-icon v-text="'mdi-information'"/>
                    </v-btn>

                    <DefaultModalButton
                        label="Crear cuenta"
                        @click="openFormModal(modalOptions)"/>
                </v-card-title>
            </div>
        </header>

        <section class="client section-list">

            <v-card class="card" elevation="0">
                <v-card-text>
                    <v-row class="justify-content-start">
                        <v-col cols="3">
                            <DefaultSelect
                                clearable dense
                                :items="selects.services"
                                v-model="filters.service"
                                label="Servicio"
                                item-text="name"
                                @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            />
                        </v-col>
                        <v-col cols="3">
                            <DefaultInput
                                clearable dense
                                v-model="filters.q"
                                label="Buscar por nombre..."
                                @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            />
                        </v-col>
                    </v-row>
                </v-card-text>

                <DefaultTable
                    :ref="dataTable.ref"
                    :data-table="dataTable"
                    :filters="filters"
                    @edit="openFormModal(modalOptions, $event)"
                    @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                    @token="openFormModal(modalTokenOptions, $event, 'token', 'Actualizar tokens')"
                    @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar cuenta')"
                />

                <AccountFormModal
                    width="50vw"
                    :ref="modalOptions.ref"
                    :options="modalOptions"
                    @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                    @onCancel="closeFormModal(modalOptions)"
                />

                <AccountTokenModal
                    :options="modalTokenOptions"
                    :ref="modalTokenOptions.ref"
                    @onConfirm="closeFormModal(modalTokenOptions, dataTable, filters)"
                    @onCancel="closeFormModal(modalTokenOptions)"
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

                <MeetingInfoModal
                    :options="modalInfoOptions"
                    :ref="modalInfoOptions.ref"
                    @onCancel="closeFormModal(modalInfoOptions)"
                />

            </v-card>

        </section>

    </div>

</template>


<script>
import AccountFormModal from "./AccountFormModal";
import AccountTokenModal from "./AccountTokenModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import MeetingInfoModal from "../Meetings/MeetingInfoModal";

export default {
    components: {AccountFormModal, AccountTokenModal, DefaultStatusModal, DefaultDeleteModal, MeetingInfoModal},
    data() {
        return {
            dataTable: {
                endpoint: '/aulas-virtuales/cuentas/search',
                ref: 'AccountTable',
                headers: [
                    {text: "ID", value: "id"},
                    {text: "Nombre", value: "name"},
                    {text: "Correo", value: "email"},
                    {text: "Servicio", value: "service", align: 'center', sortable: false},
                    {text: "Tipo", value: "type", align: 'center', sortable: false},
                    {text: "Plan", value: "plan", align: 'center', sortable: false},
                    {text: "Fecha de creación", value: "created_at", align: 'center', sortable: true},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
                    },
                    // {
                    //     text: "Generar Token",
                    //     icon: 'mdi mdi-key',
                    //     type: 'action',
                    //     method_name: 'token'
                    // },
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
                services: []
            },
            filters: {
                q: '',
                service: null
            },
            modalOptions: {
                ref: 'AccountFormModal',
                open: false,
                base_endpoint: '/aulas-virtuales/cuentas',
                resource: 'cuenta',
                confirmLabel: 'Guardar',
            },
            modalInfoOptions: {
                ref: 'MeetingInfoModal',
                open: false,
                base_endpoint: '/aulas-virtuales/cuentas',
                resource: 'cuenta',
                hideConfirmBtn: true,
                cancelLabel: 'Cerrar',
            },
            modalStatusOptions: {
                ref: 'AccountStatusModal',
                open: false,
                base_endpoint: '/aulas-virtuales/cuentas',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalTokenOptions: {
                ref: 'AccountTokenModal',
                open: false,
                base_endpoint: '/aulas-virtuales/cuentas',
                contentText: '¿Desea generar nuevos tokens a este registro?',
                endpoint: '',
            },
            modalDeleteOptions: {
                ref: 'AccountDeleteModal',
                open: false,
                base_endpoint: '/aulas-virtuales/cuentas',
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
            const url = `/aulas-virtuales/cuentas/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.services = data.data.services
                })
        },
        // reset(user) {
        //     let vue = this
        //     vue.consoleObjectTable(user, 'User to Reset')
        // },
        activity() {
            // console.log('activity')
        },
        confirmModal() {
            // TODO: Call store or update USER
        },
    }

}
</script>
