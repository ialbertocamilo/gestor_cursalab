<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Soporte
                <v-spacer/>
                <DefaultActivityButton label="Administrar Formulario APP"
                                       @click="goToCategorias"/>
                <!-- <v-spacer/> -->
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col >
                        <DefaultSelect clearable dense
                                       :items="selects.modulos"
                                       v-model="filters.modulo"
                                       label="Módulos"
                                       @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col >
                        <DefaultSelect clearable dense
                                       :items="selects.estados"
                                       v-model="filters.status"
                                       label="Estados"
                                       @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col >
                        <DefaultInput clearable dense
                                      v-model="filters.q"
                                      label="Buscar por DNI, nombre, ticket..."
                                      @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                                      append-icon="mdi-magnify"
                                      @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col>
                        <DefaultInputDate
                            clearable
                            dense
                            :referenceComponent="'dateFilterStart'"
                            :options="dateFilterStart"
                            v-model="filters.starts_at"
                            label="Fecha inicio"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>

                    <v-col>
                        <DefaultInputDate
                            clearable
                            dense
                            :referenceComponent="'dateFilterEnd'"
                            :options="dateFilterEnd"
                            v-model="filters.ends_at"
                            label="Fecha fin"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @edit="openFormModal(modalOptions, $event, null, 'Editar Ticket')"
                          @show="openFormModal(modalShowOptions, $event, null, 'Detalle de Ticket')"
            />

            <SoporteFormModal width="40vw"
                              :ref="modalOptions.ref"
                              :options="modalOptions"
                              @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                              @onCancel="closeFormModal(modalOptions)"
            />

           <SoporteShowModal width="40vw"
                              :ref="modalShowOptions.ref"
                              :options="modalShowOptions"
                              @onCancel="closeFormModal(modalShowOptions)"
            />

        </v-card>
    </section>
</template>


<script>
import SoporteFormModal from "./SoporteFormModal";
import SoporteShowModal from "./SoporteShowModal";

export default {
    components: {
        SoporteFormModal,
        SoporteShowModal
    }
    ,
    data() {
        return {
            dateFilterStart: {
                open: false,
            }
            ,
            dateFilterEnd: {
                open: false,
            }
            ,
            dataTable: {
                endpoint: '/soporte/search',
                ref: 'SoporteTable',
                headers: [
                    {text: "# Ticket", value: "id", align: 'center', sortable: true},
                    {text: "Módulo", value: "image", align: 'center', sortable: false},
                    {text: "DNI", value: "dni", align: 'center', sortable: false},
                    {text: "Nombre", value: "nombre", sortable: false},
                    {text: "Motivo", value: "reason"},
                    {text: "Estado", value: "status", align: 'center',},
                    {text: "Fecha de registro", value: "created_at", align: 'center', sortable: true},
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
                        text: "Detalle",
                        icon: 'mdi mdi-eye',
                        type: 'action',
                        method_name: 'show'
                    },
                    // {
                    //     text: "Eliminar",
                    //     icon: 'far fa-trash-alt',
                    //     type: 'action',
                    //     method_name: 'delete'
                    // },
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
                modulos: [],
                estados: [],
            },
            filters: {
                q: '',
                modulo: null,
                status: null,
                starts_at: null,
                ends_at: null
            },
            modalOptions: {
                ref: 'SoporteFormModal',
                open: false,
                base_endpoint: '/soporte',
                resource: 'Soporte',
                confirmLabel: 'Guardar',
            },
            modalShowOptions: {
                ref: 'SoporteShowModal',
                open: false,
                base_endpoint: '/soporte',
                endpoint: '',
                hideConfirmBtn: true,
                cancelLabel: "Cerrar"
            },
            modalDeleteOptions: {
                ref: 'SoporteDeleteModal',
                open: false,
                base_endpoint: '/soporte',
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
            const url = `/soporte/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modulos = data.data.modulos
                    vue.selects.estados = data.data.estados
                })
        },
        goToCategorias() {
            const url = `/soporte/formulario-ayuda`
            window.location.href = url
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
