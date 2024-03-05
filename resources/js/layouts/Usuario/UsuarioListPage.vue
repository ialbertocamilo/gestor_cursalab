<template>
    <section class="section-list">
        <DefaultFilter
            v-model="open_advanced_filter"
            @filter="advanced_filter(dataTable, filters, 1)"
            @cleanFilters="clearObject(filters)"
            :disabled-confirm-btn="isValuesObjectEmpty(filters)"
        >
            <template v-slot:content>
                <v-row justify="center">
                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            dense
                            :items="selects.sub_workspaces"
                            v-model="filters.subworkspace_id"
                            label="Módulos"
                            item-text="name"
                        />
                    </v-col>

                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            dense
                            :items="selects.statuses"
                            v-model="filters.active"
                            label="Estado"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            item-text="name"
                        />
                    </v-col>

                    <template v-for="(value, selectKey, index) in selects">

                        <v-col cols="12"
                               v-if="!['sub_workspaces', 'active', 'module'].includes(selectKey) && criteria_template[index-2]">

                            <DefaultInputDate
                                v-if="criteria_template[index-2].field_type.code === 'date'"
                                clearable
                                dense
                                :referenceComponent="'modalDateFilter1'"
                                :options="{ open: false, }"
                                v-model="filters[selectKey]"
                                :label="criteria_template[index-2].name"
                            />

                            <DefaultAutocomplete
                                v-else
                                clearable
                                dense
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
                    <v-col cols="4">
                    </v-col>
                    <v-col cols="4" class="d-flex justify-end">

                        <div
                            class="user-count-wrapper">

                                <v-tooltip
                                    :top="true"
                                    attach
                                >
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-icon
                                            v-bind="attrs"
                                            v-on="on"
                                            size="32"
                                            color="#E01717">
                                            mdi-account
                                        </v-icon>
                                    </template>
                                    <span
                                        v-if="emptyCriteriaHasBeenCounted"
                                        v-html="`Tienes ${usersWithEmptyCriteria} usuarios con criterios vacíos.`"/>
                                </v-tooltip>

                                <span class="count" v-if="emptyCriteriaHasBeenCounted">
                                    {{ usersWithEmptyCriteria }}
                                </span>

                            <span class="description cursor-pointer"
                                  data-toggle="dropdown">
                                    Criterios vacíos
                            </span>

                            <div class="dropdown-menu dropdown-header-menu shadow-md">
                                <a class="dropdown-item py-2 dropdown-item-custom text-body"
                                   href="javascript:" @click="fetchUsersWithEmptyCriteria()">
                                    <span>Contar usuarios con criterios vacios</span>
                                </a>
                                <a class="dropdown-item py-2 dropdown-item-custom text-body" href="/exportar/node?tab=new-report&section=19">
                                    <span>Ir a la sección de reportes</span>
                                </a>
                            </div>

                        </div>

                        <DefaultButton
                            text
                            label="Aplicar filtros"
                            icon="mdi-filter"
                            @click="openFiltersModal()"
                            class="btn_filter"
                            />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :avoid_first_data_load="getUrlParamsTotal() > 0"
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openFormModal(modalOptions, $event, 'edit',  `Editar usuario ${$event.nombre} - ${$event.document}`)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambio de estado de <b>usuario</b>')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Confirmación de cambio de estado')"
                @cursos="openFormModal(modalCursosOptions, $event, 'cursos', `Cursos de ${$event.nombre} - ${$event.document}`)"
                @profile="openFormModal(modalProfileOptions, $event, 'profile', `Progreso de ${$event.nombre} - ${$event.document}`)"
                @reset="openFormModal(modalReiniciosOptions, $event, 'cursos', `Reiniciar avance de ${$event.nombre}`)"
                @reset_password="openFormModal(modalResetPasswordOptions, $event, 'user', `Restaurar contraseña de ${$event.nombre} - ${$event.document}`)"
                @impersonate_user="openFormModal(modalImpersonateUserOptions, $event, 'user', `Acceder como ${$event.nombre} - ${$event.document}` )"
                @logs="openFormModal(modalLogsOptions,$event,'logs',`Logs del Usuario - ${$event.name}`)"
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
            <UsuarioResetPasswordModal
                width="45vw"
                :ref="modalResetPasswordOptions.ref"
                :options="modalResetPasswordOptions"
                @onConfirm="closeFormModal(modalResetPasswordOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalResetPasswordOptions)"
            />
            <UsuarioImpersonateModal
                width="45vw"
                :ref="modalImpersonateUserOptions.ref"
                :options="modalImpersonateUserOptions"
                @onConfirm="closeFormModal(modalImpersonateUserOptions)"
                @onCancel="closeFormModal(modalImpersonateUserOptions)"
            />
            <UsuarioCursosModal
                width="55vw"
                :ref="modalCursosOptions.ref"
                :options="modalCursosOptions"
                @onCancel="closeFormModal(modalCursosOptions)"
            />

            <UsuarioPerfilModal
                width="70vw"
                :ref="modalProfileOptions.ref"
                :options="modalProfileOptions"
                @onCancel="closeFormModal(modalProfileOptions)"
            />

            <DefaultStatusModal
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalStatusOptions)"
            />
            <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\User"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />
        </v-card>
    </section>
</template>

<script>
import UsuarioFormModal from "./UsuarioFormModal";
import UsuarioStatusModal from "./UsuarioStatusModal";
import UsuarioCursosModal from "./UsuarioCursosModal";
import UsuarioPerfilModal from "./UsuarioPerfilModal";
import UsuarioReiniciosModal from "./UsuarioReiniciosModal";
import UsuarioResetPasswordModal from "./UsuarioResetPasswordModal";
import UsuarioImpersonateModal from "./UsuarioImpersonateModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import LogsModal from "../../components/globals/Logs";

export default {
    components: {UsuarioFormModal, UsuarioStatusModal, UsuarioCursosModal, UsuarioReiniciosModal, DefaultStatusModal, UsuarioResetPasswordModal, LogsModal, UsuarioImpersonateModal, UsuarioPerfilModal},
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
            {text: "Documento", value: "document", align: 'center', sortable: false},
            {text: "Opciones", value: "actions", align: 'center', sortable: false},
        ];

        if (this.workspace_id == 25) {
            headers = [
                {text: "Nombres y Apellidos", value: "name"},
                {text: "Módulo", value: "module", sortable: false},
                {text: "Carrera", value: "career", sortable: false},
                {text: "Ciclo", value: "cycle", sortable: false},
                {text: "Documento", value: "document", align: 'center', sortable: false},
                {text: "Opciones", value: "actions", align: 'center', sortable: false},
            ];
        }

        return {
            usersWithEmptyCriteria: 0,
            filtersValuesHasBeenLoaded : false,
            emptyCriteriaHasBeenCounted: false,
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
                        show_condition: 'show_badge',
                        count: 'failed_topics_count',
                    },

                    {
                        text: "Editar",
                        icon: "mdi mdi-pencil",
                        type: "action",
                        method_name: "edit"
                    },

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
                        text: "Actualizar estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    {
                        text: "Restaurar contraseña",
                        icon: 'fa fa-key',
                        type: 'action',
                        method_name: 'reset_password'
                    },
                    {
                        text: "Acceder como usuario",
                        icon: 'fa fa-user',
                        type: 'action',
                        // show_condition: "is_super_user",
                        method_name: 'impersonate_user'
                    },
                    {
                        text: "Logs",
                        icon: "mdi mdi-database",
                        type: "action",
                        show_condition: "is_super_user",
                        method_name: "logs"
                    },
                    {   text: "Progreso",
                        icon: 'mdi mdi-account-box',
                        type: 'action',
                        method_name: 'profile',
                        // show_condition: "is_cursalab_super_user",
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
                active: 1,
            },
            criteria_template: [],
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
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
            modalProfileOptions: {
                ref: 'UsuarioPerfilModal',
                open: false,
                base_endpoint: '/usuarios',
                cancelLabel: 'Cerrar',
                hideConfirmBtn: true,
                persistent: true,
            },
            modalReiniciosOptions: {
                ref: 'UsuarioReiniciosModal',
                open: false,
                base_endpoint: '/usuarios',
                cancelLabel: 'Cerrar',
                hideConfirmBtn: true,
            },
            modalResetPasswordOptions: {
                ref: 'UsuarioResetPasswordModal',
                open: false,
                base_endpoint: '/usuarios',
                // hideConfirmBtn: true,
            },
            modalImpersonateUserOptions: {
                ref: 'UsuarioImpersonateModal',
                open: false,
                base_endpoint: '/usuarios',
                confirmLabel: 'Acceder como usuario',
                // cancelLabel: 'Cerrar',
                // hideConfirmBtn: true,
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
                width: '408px'
            },
        }
    },
    mounted() {
        let vue = this

          // === check localstorage multimedia ===
        const { status, storage: usuarioStorage } = vue.getStorageUrl('usuarios', 'module_data');
        // console.log('created_usuarios:', {status, usuarioStorage});

        if(status) {
            vue.filters.active = usuarioStorage.active;
            vue.refreshDefaultTable(vue.dataTable, vue.filters, 1);
        // === check localstorage anuncio ===
        }

        // When subworkspace_id URL param is present refresh table
        // with that parameter

        let params = vue.getAllUrlParams(window.location.search);
        if (params.subworkspace_id) {
            vue.refreshDefaultTable(vue.dataTable, vue.filters, 1);
        }
    },
    methods: {
        getSelects() {
            this.showLoader()

            let vue = this

            let params = vue.getAllUrlParams(window.location.search);

            let param_subworkspace = params.subworkspace_id;
            let param_document = params.document;

            vue.filters.subworkspace_id = parseInt(param_subworkspace);
            vue.filters.q = param_document;

            if (param_subworkspace || param_document) {
                vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
            }

            const url = `/usuarios/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {

                    vue.selects.sub_workspaces = data.data.sub_workspaces;
                    vue.criteria_template = data.data.criteria_template;
                    //vue.usersWithEmptyCriteria = data.data.users_with_empty_criteria

                    data.data.criteria_workspace.forEach(criteria => {

                        const new_select_obj = {[criteria.code]: criteria.values,};
                        vue.selects = Object.assign({}, vue.selects, new_select_obj);

                        const value = criteria.multiple ? [] : null;
                        const new_filter_obj = {[criteria.code]: value};
                        vue.filters = Object.assign({}, vue.filters, new_filter_obj);

                    });

                    // if (param_subworkspace)
                    //     vue.filters.subworkspace_id = param_subworkspace

                    this.filtersValuesHasBeenLoaded = true;
                    this.hideLoader()
                })

        },
        fetchUsersWithEmptyCriteria() {

            const vue = this

            this.showLoader()
            const url = `/usuarios/users-empty-criteria`
            vue.$http.get(url)
                .then(({data}) => {

                    vue.usersWithEmptyCriteria = data.data.users_with_empty_criteria
                    vue.emptyCriteriaHasBeenCounted = true;
                    this.hideLoader()
                })
        },
        reset(user) {
            let vue = this
            vue.consoleObjectTable(user, 'User to Reset')
        },
        goToReiniciosMasivos() {
            window.location.href = "/intentos-masivos";
        },
        activity() {
            console.log('activity')
        },
        openFiltersModal() {

            this.open_advanced_filter = !this.open_advanced_filter;

            if (!this.filtersValuesHasBeenLoaded)
                this.getSelects()
        },
        goToReports() {
            window.location.href = '/exportar/node?tab=new-report&section=19';
        }
    }
}
</script>

<style>
.user-count-wrapper {
    position: relative;
    width: 200px;
}

.user-count-wrapper .count {
    height: 15px;
    position: absolute;
    display: flex;
    justify-content: center;
    align-items: center;
    left: 20px;
    bottom: 2px;
    font-size: 11px;
    padding: 0 5px 0 5px;
    border-radius: 14px;
    border: 1px solid white;
    color: white;
    background-color: #E01717;
}

.user-count-wrapper .description {
    color: #E01717;
}

.user-count-wrapper a {
    text-decoration: none;
}

</style>
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

.dropdown-menu {
    width: 300px;
}

.cursor-pointer {
    cursor: pointer;
}

</style>
