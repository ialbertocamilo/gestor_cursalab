<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Cuentas Zoom
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'" @click="activity"/> -->
                <DefaultModalButton :label="'Cuenta Zoom'"
                                    @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                   <!--  <v-col cols="3">
                        <DefaultSelect clearable dense
                                       :items="selects.modules"
                                       v-model="filters.module"
                                       label="Módulos"
                                       @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col> -->
                    <v-col cols="3">
                        <DefaultInput clearable dense
                                      v-model="filters.q"
                                      label="Buscar por nombre..."
                                      @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @edit="openFormModal(modalOptions, $event)"
                          @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                          @token="openFormModal(modalTokenOptions, $event, 'token', 'Actualizar tokens')"
                          @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar cuenta zoom')"
            />

            <CuentaZoomFormModal width="50vw"
                              :ref="modalOptions.ref"
                              :options="modalOptions"
                              @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                              @onCancel="closeFormModal(modalOptions)"
            />

            <CuentaZoomTokenModal :options="modalTokenOptions"
                                :ref="modalTokenOptions.ref"
                                @onConfirm="closeFormModal(modalTokenOptions, dataTable, filters)"
                                @onCancel="closeFormModal(modalTokenOptions)"
            />

            <DefaultStatusModal :options="modalStatusOptions"
                                :ref="modalStatusOptions.ref"
                                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
                                @onCancel="closeFormModal(modalStatusOptions)"
            />

            <DefaultDeleteModal :options="modalDeleteOptions"
                                :ref="modalDeleteOptions.ref"
                                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                                @onCancel="closeFormModal(modalDeleteOptions)"
            />

        </v-card>
    </section>
</template>


<script>
import CuentaZoomFormModal from "./CuentaZoomFormModal";
import CuentaZoomTokenModal from "./CuentaZoomTokenModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";

export default {
    components: {CuentaZoomFormModal, CuentaZoomTokenModal, DefaultStatusModal, DefaultDeleteModal},
    data() {
        return {
            dataTable: {
                endpoint: '/cuentas-zoom/search',
                ref: 'CuentaZoomTable',
                headers: [
                    {text: "ID", value: "id"},
                    {text: "Nombre", value: "usuario"},
                    {text: "Correo", value: "correo"},
                    {text: "Tipo", value: "tipo"},
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
                    {
                        text: "Generar Token",
                        icon: 'mdi mdi-key',
                        type: 'action',
                        method_name: 'token'
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
                ref: 'CuentaZoomFormModal',
                open: false,
                base_endpoint: '/cuentas-zoom',
                resource: 'Cuenta Zoom',
                confirmLabel: 'Guardar',
            },
            modalStatusOptions: {
                ref: 'CuentaZoomStatusModal',
                open: false,
                base_endpoint: '/cuentas-zoom',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalTokenOptions: {
                ref: 'CuentaZoomTokenModal',
                open: false,
                base_endpoint: '/cuentas-zoom',
                contentText: '¿Desea generar nuevos tokens a este registro?',
                endpoint: '',
            },
            modalDeleteOptions: {
                ref: 'CuentaZoomDeleteModal',
                open: false,
                base_endpoint: '/cuentas-zoom',
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
            // const url = `/cuentas-zoom/get-list-selects`
            // vue.$http.get(url)
            //     .then(({data}) => {
            //         vue.selects.modules = data.data.modules
            //     })
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
