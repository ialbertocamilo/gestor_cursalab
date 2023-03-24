<template>
    <section class="section-list ">
        <DefaultFilter
            v-model="open_advanced_filter"
            @filter="advanced_filter(dataTable, filters, 1)"
            @cleanFilters="clearObject(filters)"
            :disabled-confirm-btn="isValuesObjectEmpty(filters)"
        >
            <template v-slot:content>
                <v-row>
                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            :items="selects.sub_workspaces"
                            v-model="filters.subworkspace_id"
                            label="Módulos"
                            item-text="name"
                        />
                    </v-col>

                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            :items="selects.statuses"
                            v-model="filters.active"
                            label="Estado"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            item-text="name"
                        />
                    </v-col>

                    <template v-for="(value, selectKey, index) in selects">

                        <v-col cols="12"
                               v-if="!['sub_workspaces', 'active'].includes(selectKey) && criteria_template[index-2]">

                            <DefaultInputDate
                                v-if="criteria_template[index-2].field_type.code === 'date'"
                                clearable
                                :referenceComponent="'modalDateFilter1'"
                                :options="{ open: false, }"
                                v-model="filters[selectKey]"
                                :label="criteria_template[index-2].name"
                            />

                            <DefaultAutocomplete
                                v-else
                                clearable
                                :items="value"
                                v-model="filters[selectKey]"
                                :label="criteria_template[index-2].name"
                                item-text="name"
                                :multiple="criteria_template[index-2].multiple"
                                :show-select-all="false"
                            />
                        </v-col>

                    </template>

                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Usuarios
                <v-spacer/>
                <DefaultActivityButton
                    :label="'Reinicios masivos'"
                    @click="goToReiniciosMasivos"/>
                <DefaultModalButton
                    :label="'Usuario'"
                    class="btn_add_user"
                    @click="openFormModal(modalOptions, null, 'create')"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar usuario"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                            class="btn_search_user"
                        />
                    </v-col>
                    <v-col cols="5">
                    </v-col>
                    <v-col cols="3" class="d-flex justify-end">
                        <DefaultButton
                            text
                            label="Aplicar filtros"
                            icon="mdi-filter"
                            @click="open_advanced_filter = !open_advanced_filter"
                            class="btn_filter"
                            />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openFormModal(modalOptions, $event, 'edit')"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambio de estado de <b>usuario</b>')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Confirmación de cambio de estado')"
                @cursos="openFormModal(modalCursosOptions, $event, 'cursos', `Cursos de ${$event.nombre} - ${$event.document}`)"
                @reset="openFormModal(modalReiniciosOptions, $event, 'cursos', `Reiniciar avance de ${$event.nombre}`)"
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
    props: {
        workspace_id: {
            type: Number|String,
            required: true
        },
        // show_meeting_section: {
        //     type: String,
        //     required: true
        // }

    },
    data() {

        let headers = [
            {text: "Nombres y Apellidos", value: "name"},
            {text: "Módulo", value: "module", sortable: false},
            {text: "Documento", value: "document", align: 'left', sortable: false},
            {text: "Opciones", value: "actions", align: 'center', sortable: false},
        ];

        if (this.workspace_id == 25) {
            headers = [
                {text: "Nombres y Apellidos", value: "name"},
                {text: "Módulo", value: "module", sortable: false},
                {text: "Carrera", value: "career", sortable: false},
                {text: "Ciclo", value: "cycle", sortable: false},
                {text: "Documento", value: "document", align: 'left', sortable: false},
                {text: "Opciones", value: "actions", align: 'center', sortable: false},
            ];
        }

        return {
            dataTable: {
                endpoint: '/usuarios/search',
                ref: 'UsuarioTable',
                headers: headers,
                actions: [
                    {text: "Cursos", icon: 'mdi mdi-notebook-multiple', type: 'action', method_name: 'cursos'},

                    {
                        text: "Reiniciar",
                        icon: 'fas fa-history',
                        type: 'action',
                        method_name: 'reset',
                        show_condition: 'pruebas_desaprobadas',
                        count: 'failed_topics_count',
                    },

                    {text: "Editar", icon: 'mdi mdi-pencil', type: 'action', method_name: 'edit'},
                ],
                more_actions: [
                    {
                        text: "Reporte",
                        type: 'route',
                        icon: 'mdi mdi-file-document-multiple',
                        route: 'reporte_route',
                        route_type: 'external'
                    },
                    {
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                ]
            },
            selects: {
                sub_workspaces: [],
                statuses: [
                    {id: null, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 2, name: 'Inactivos'},
                ],
                // statuses: [
                //     null => 'Todos',
                //     1 => 'Activos',
                //     0 => 'Inactivos',
                // ],
            },
            filters: {
                q: '',
                subworkspace_id: null,
                active: null,
            },
            criteria_template: [],
            modalOptions: {
                ref: 'UsuarioFormModal',
                open: false,
                base_endpoint: '/usuarios',
                resource: 'Usuario',
                confirmLabel: 'Confirmar',
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
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un usuario!',
                        details: [
                            'El usuario no podrá ingresar a la plataforma.',
                            'Podrá enviar solicitudes desde la sección de ayuda del Log in.',
                            'Aparecerá en los reportes y consultas con el estado inactivo.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un usuario!',
                        details: [
                            'El usuario ahora podrá ingresar a la plataforma.',
                            'Podrá rendir los cursos, de estar segmentado.'
                        ]
                    }
                },
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

            const url = `/usuarios/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {

                    vue.selects.sub_workspaces = data.data.sub_workspaces;
                    vue.filters.subworkspace_id = parseInt(param_subworkspace);
                    vue.criteria_template = data.data.criteria_template;

                    data.data.criteria_workspace.forEach(criteria => {

                        const new_select_obj = {[criteria.code]: criteria.values,};
                        vue.selects = Object.assign({}, vue.selects, new_select_obj);

                        const value = criteria.multiple ? [] : null;
                        const new_filter_obj = {[criteria.code]: value};
                        vue.filters = Object.assign({}, vue.filters, new_filter_obj);

                    });

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
<style lang="scss">
button.btn_add_user {
    padding-left: 20px !important;
    padding-right: 20px !important;
}
button.btn_add_user .v-btn__content {
    align-items: flex-end;
    justify-content: center;
    vertical-align: bottom;
    font-size: 14px;
    font-family: "Nunito", sans-serif;
    font-weight: 400;
    line-height: 1;
}
button.btn_add_user .v-btn__content i {
    font-size: 13px;
}
.btn_search_user {
    max-width: 320px;
}
.btn_search_user .v-text-field__slot label.v-label {
    color: #434D56;
    font-size: 14px;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    font-weight: 400;
}
.btn_search_user span.v-btn__content i.v-icon.mdi.mdi-magnify {
    color: #434D56;
    font-size: 20px;
}
.btn_search_user.v-text-field--outlined:not(.v-input--is-focused):not(.v-input--has-state)>.v-input__control>.v-input__slot fieldset {
    border-color: #C4C4C4;
}
.btn_filter span.v-btn__content {
    font-size: 14px;
    font-family: "Nunito", sans-serif;
    line-height: 20px;
    font-weight: 400;
}
.btn_filter span.v-btn__content i {
    font-size: 16px;
    margin: 0 !important;
}
</style>
