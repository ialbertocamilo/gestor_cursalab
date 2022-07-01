<template>
    <section class="section-list">
        <DefaultFilter
            v-model="open_advanced_filter"
            @filter="advanced_filter(dataTable, filters, 1)"
        >
            <template v-slot:content>
                <v-row justify="center">
                    <!-- <v-col cols="12">
                        <DefaultSelect
                            clearable
                            dense
                            v-model="filters.estado"
                            :items="selects.estado"
                            label="Estado de evento"
                        />
                    </v-col> -->
                    <v-col cols="12">
                        <DefaultInputDate
                            clearable
                            dense
                            range
                            :referenceComponent="'modalDateFilter1'"
                            :options="modalDateFilter1"
                            v-model="filters.fecha"
                            label="Fecha"
                            @onChange="refreshDefaultTable(dataTable, filters)" 
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultInput
                            clearable
                            dense
                            v-model="filters.q"
                            label="Buscar por título"
                        />
                    </v-col>
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Notificaciones Push
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'"/> -->
                <DefaultModalButton
                    :label="'Notificación Push'"
                    @click="openFormModal(modalOptions)"
                />
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                   <!--  <v-col cols="3">
                        <DefaultSelect
                            clearable
                            dense
                            v-model="filters.estado"
                            :items="selects.estado"
                            label="Estado de evento"
                            @onChange="refreshDefaultTable(dataTable, filters)"
                        />
                    </v-col> -->
                    <v-col cols="3">
                        <DefaultInputDate
                            clearable
                            dense
                            range
                            :referenceComponent="'modalDateFilter2'"
                            :options="modalDateFilter2"
                            v-model="filters.fecha"
                            label="Fecha"
                            @onChange="refreshDefaultTable(dataTable, filters)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput
                            clearable
                            dense
                            v-model="filters.q"
                            label="Buscar por título"
                            append-icon="mdi-magnify"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-end">
                        <DefaultButton label="Ver Filtros"
                                       icon="mdi-filter"
                                       @click="open_advanced_filter = !open_advanced_filter"/>
                    </v-col>
                </v-row>
            </v-card-text>
            <!-- </v-card> -->
            <!--        Contenido-->
            <!-- <v-card flat class="elevation-0 mb-4"> -->
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                default-sort-by="created_at"
                :default-sort-desc="true"
                @detalles="openFormModal(modalDetallesOptions, $event, 'detalle', `Detalles`)"
            />
            <NotificacionPushFormModal
                :ref="modalOptions.ref"
                :options="modalOptions"
                :modulos="selects.modulos"
                @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
                @onCancel="closeFormModal(modalOptions)"
            />
            <NotificacionPushDetalleModal
                :ref="modalDetallesOptions.ref"
                :options="modalDetallesOptions"
                @onCancel="closeFormModal(modalDetallesOptions)"
            />
        </v-card>
    </section>
</template>


<script>
import NotificacionPushFormModal from "./NotificacionPushFormModal";
import NotificacionPushDetalleModal from "./NotificacionPushDetalleModal";

export default {
    components: {NotificacionPushFormModal, NotificacionPushDetalleModal},
    data() {
        return {
            dataTable: {
                endpoint: '/notificaciones_push/search',
                ref: 'NotificacionPushTable',
                headers: [
                    {text: "Título", value: "titulo"},
                    {text: "Creación", value: "created_at", align: 'center'},
                    // {text: "Fecha de envío", value: "fecha_envio"},
                    // {text: "Estado", value: "estado", sortable: false, align: 'center'},
                    {text: "Usuarios alcanzados", value: "users_reached", align: 'center', sortable: false},
                    {text: "Efectividad", value: "custom_notification_push_efectividad", sortable: false, align: 'center'},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Detalles",
                        icon: 'mdi mdi-clipboard-text',
                        type: 'action',
                        method_name: 'detalles'
                    },
                ],
            },
            filters: {
                q: null,
                estado: null,
                fecha: []

            },
            selects: {
                estado: [],
                modulos: [],
            },
            modalOptions: {
                ref: 'NotificacionPushFormModal',
                open: false,
                resource: 'Notificación Push',
                base_endpoint: '/notificaciones_push',
                confirmLabel: 'Enviar',
            },
            modalDetallesOptions: {
                ref: 'NotificacionPushDetalleModal',
                open: false,
                base_endpoint: '/notificaciones_push',
                hideConfirmBtn: true,
            },
            modalDateFilter1: {
                open: false,
            },
            modalDateFilter2: {
                open: false,
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
            const url = `/notificaciones_push/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.estados = data.data.estados
                    vue.selects.modulos = data.data.modulos
                })
        },
    }
}
</script>
