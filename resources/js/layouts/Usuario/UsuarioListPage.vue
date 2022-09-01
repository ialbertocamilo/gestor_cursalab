<template>
    <section class="section-list ">
<!--        <DefaultFilter-->
<!--            v-model="open_advanced_filter"-->
<!--            @filter="advanced_filter(dataTable, filters, 1)"-->
<!--            @cleanFilters="clearObject(filters)"-->
<!--            :disabled-confirm-btn="isValuesObjectEmpty(filters)"-->
<!--        >-->
<!--            <template v-slot:content>-->
<!--                <v-row>-->
<!--                    <v-col cols="12">-->
<!--                        <DefaultSelect-->
<!--                            clearable-->
<!--                            :items="selects.sub_workspaces"-->
<!--                            v-model="filters.subworkspace_id"-->
<!--                            label="Módulos"-->
<!--                            item-text="name"-->
<!--                        />-->
<!--                    </v-col>-->
<!--                </v-row>-->
<!--            </template>-->
<!--        </DefaultFilter>-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Usuarios
                <v-spacer/>
                <DefaultActivityButton
                    :label="'Reinicios masivos'"
                    @click="goToReiniciosMasivos"/>
                <DefaultModalButton
                    :label="'Usuario'"
                    @click="openFormModal(modalOptions, null, 'create')"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
<!--                    <v-col cols="3">-->
<!--                        <DefaultSelect-->
<!--                            clearable dense-->
<!--                            :items="selects.workspaces"-->
<!--                            v-model="filters.workspace_id"-->
<!--                            label="Workspace"-->
<!--                            @onChange="refreshDefaultTable(dataTable, filters, 1)"-->
<!--                            item-text="name"-->
<!--                        />-->
<!--                    </v-col>-->
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.sub_workspaces"
                            v-model="filters.subworkspace_id"
                            label="Módulos"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            item-text="name"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre o DNI..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
<!--                    <v-col cols="3" class="d-flex justify-content-end">-->
<!--                        <DefaultButton-->
<!--                            label="Ver Filtros"-->
<!--                            icon="mdi-filter"-->
<!--                            @click="open_advanced_filter = !open_advanced_filter"/>-->
<!--                    </v-col>-->
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openFormModal(modalOptions, $event, 'edit')"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Confirmación de cambio de estado')"
                @cursos="openFormModal(modalCursosOptions, $event, 'cursos', `CURSOS DE ${$event.nombre} - ${$event.document}`)"
                @reset="openFormModal(modalReiniciosOptions, $event, 'cursos', `REINICIAR AVANCE DE ${$event.nombre}`)"
            />
            <UsuarioFormModal
                width="60vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="refreshDefaultTable(dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
            />
            <UsuarioStatusModal
                :options="modalDeleteOptions"
                :ref="modalDeleteOptions.ref"
                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalDeleteOptions)"
            />
            <UsuarioReiniciosModal
                width="55vw"
                :ref="modalReiniciosOptions.ref"
                :options="modalReiniciosOptions"
                @onReinicioTotal="refreshDefaultTable(dataTable, filters)"
                @onCancel="closeFormModal(modalReiniciosOptions)"
            />
            <UsuarioCursosModal
                width="55vw"
                :ref="modalCursosOptions.ref"
                :options="modalCursosOptions"
                @onCancel="closeFormModal(modalCursosOptions)"
            />

            <DefaultStatusModal
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalStatusOptions)"
            />

        </v-card>
    </section>
</template>


<script>
import UsuarioFormModal from "./UsuarioFormModal";
import UsuarioStatusModal from "./UsuarioStatusModal";
import UsuarioCursosModal from "./UsuarioCursosModal";
import UsuarioReiniciosModal from "./UsuarioReiniciosModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";

export default {
    components: {UsuarioFormModal, UsuarioStatusModal, UsuarioCursosModal, UsuarioReiniciosModal, DefaultStatusModal},
    data() {
        return {
            dataTable: {
                endpoint: '/usuarios/search',
                ref: 'UsuarioTable',
                headers: [
                    {text: "Nombres y Apellidos", value: "name"},
                    {text: "Documento", value: "document", align: 'left', sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    // {
                    //     text: "Reporte",
                    //     type: 'route',
                    //     icon: 'mdi mdi-file-document-multiple',
                    //     route: 'reporte_route',
                    //     route_type: 'external'
                    // },
                    // {text: "Cursos", icon: 'mdi mdi-notebook-multiple', type: 'action', method_name: 'cursos'},
                    {text: "Editar", icon: 'mdi mdi-pencil', type: 'action', method_name: 'edit'},
                ],
                more_actions: [
                    {   text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    {
                        text: "Reiniciar",
                        icon: 'fas fa-history',
                        type: 'action',
                        method_name: 'reset',
                        show_condition: 'pruebas_desaprobadas'
                    },
                ]
            },
            selects: {
                sub_workspaces: [],
                workspaces: [],
                carreras: [],
                ciclos: [],
            },
            filters: {
                q: '',
                subworkspace_id: null,
                workspace_id: null,
                carrera: null,
                ciclos: []
            },
            modalOptions: {
                ref: 'UsuarioFormModal',
                open: false,
                base_endpoint: '/usuarios',
                resource: 'Usuario',
                confirmLabel: 'Guardar',
            },
            modalDeleteOptions: {
                ref: 'UsuarioDeleteModal',
                open: false,
                base_endpoint: '/usuarios',
                contentText: '¿Desea eliminar este registro?',
            },
            modalCursosOptions: {
                ref: 'UsuarioCursosModal',
                open: false,
                base_endpoint: '/usuarios',
                cancelLabel: 'Cerrar',
                hideConfirmBtn: true,
            },
            modalReiniciosOptions: {
                ref: 'UsuarioReiniciosModal',
                open: false,
                base_endpoint: '/usuarios',
                cancelLabel: 'Cerrar',
                hideConfirmBtn: true,
            },
            modalStatusOptions: {
                ref: 'UsuarioStatusModal',
                open: false,
                base_endpoint: '/usuarios',
                contentText: '¿Desea cambiar de estado a este registro?',
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

            let uri = window.location.search.substring(1);
            let params = new URLSearchParams(uri);
            let param_subworkspace = params.get("subworkspace_id");
            console.log("PARAM:: ", param_subworkspace)

            const url = `/usuarios/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.sub_workspaces = data.data.sub_workspaces

                    vue.filters.subworkspace_id = parseInt(param_subworkspace)

                    // if (param_subworkspace)
                    //     vue.filters.subworkspace_id = param_subworkspace

                    // vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                })

        },
        reset(user) {
            let vue = this
            vue.consoleObjectTable(user, 'User to Reset')
        },
        goToReiniciosMasivos() {
            window.location.href = "/masivo/usuarios/index_reinicios";
        },
        activity() {
            console.log('activity')
        },
    }

}
</script>
