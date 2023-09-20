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
                    <!-- <v-col cols="12">
                        <DefaultSelect
                            clearable
                            dense
                            :items="selects.sub_workspaces"
                            v-model="filters.subworkspace_id"
                            label="Módulos"
                            item-text="name"
                        />
                    </v-col> -->

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

                    <!-- <template v-for="(value, selectKey, index) in selects">

                        <v-col cols="12"
                               v-if="!['sub_workspaces', 'active'].includes(selectKey) && criteria_template[index-2]">

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
                    </template> -->
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Administradores
                <v-spacer/>
                
                <DefaultModalButton
                    :label="'Crear administrador'"
                    class="btn_add_user"
                    @click="openFormModal(modalOptions, null, 'create', 'Crear administrador')"/>
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
                            label="Buscar administrador"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                            class="btn_search_user"
                        />
                    </v-col>
                    <v-col cols="4">
                    </v-col>
                    <v-col cols="4" class="d-flex justify-end">

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
                @edit="openFormModal(modalOptions, $event, 'edit', `Editar administrador ${$event.name}`)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambio de estado de <b>administrador</b>')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar administrador')"
                @reset_password="openFormModal(modalResetPasswordOptions, $event, 'user', `Restaurar contraseña de ${$event.name}`)"
                @impersonate_user="openFormModal(modalImpersonateUserOptions, $event, 'user', `Acceder como ${$event.name}` )"
                @logs="openFormModal(modalLogsOptions,$event,'logs',`Logs del administrador - ${$event.name}`)"
            />
            <UserFormModal
                width="60vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="refreshDefaultTable(dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
            />

            <UserStatusModal
                :options="modalDeleteOptions"
                :ref="modalDeleteOptions.ref"
                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalDeleteOptions)"
            />
           
            <UserResetPasswordModal
                width="45vw"
                :ref="modalResetPasswordOptions.ref"
                :options="modalResetPasswordOptions"
                @onConfirm="closeFormModal(modalResetPasswordOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalResetPasswordOptions)"
            />

            <UserImpersonateModal
                width="45vw"
                :ref="modalImpersonateUserOptions.ref"
                :options="modalImpersonateUserOptions"
                @onConfirm="closeFormModal(modalImpersonateUserOptions)"
                @onCancel="closeFormModal(modalImpersonateUserOptions)"
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
import UserFormModal from "./UserFormModal";
import UserStatusModal from "./UserStatusModal";
import UserResetPasswordModal from "./UserResetPasswordModal";
import UserImpersonateModal from "./UserImpersonateModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import LogsModal from "../../components/globals/Logs";

export default {
    components: {UserFormModal, UserStatusModal, DefaultStatusModal, UserResetPasswordModal, LogsModal, UserImpersonateModal},
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
            {text: "Nombres y apellidos", value: "name"},
            {text: "Correo", value: "email_gestor", align: 'left', sortable: true},
            {text: "Documento", value: "document", align: 'left', sortable: false},
            {text: "Opciones", value: "actions", align: 'center', sortable: false},
        ];

       

        return {
            dataTable: {
                endpoint: '/users/search',
                ref: 'UserTable',
                headers: headers,
                actions: [
                    {
                        text: "Editar",
                        icon: "mdi mdi-pencil",
                        type: "action",
                        method_name: "edit"
                    },
                    {
                        text: "Actualizar estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    
                ],
                more_actions: [
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
                    }

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
            // criteria_template: [],
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalOptions: {
                ref: 'UserFormModal',
                open: false,
                base_endpoint: '/users',
                resource: 'User',
                confirmLabel: 'Confirmar',
                showCloseIcon: true,
            },
            modalDeleteOptions: {
                ref: 'UserDeleteModal',
                open: false,
                base_endpoint: '/users',
                contentText: '¿Desea eliminar este registro?',
            },
            modalResetPasswordOptions: {
                ref: 'UserResetPasswordModal',
                open: false,
                base_endpoint: '/users',
                // hideConfirmBtn: true,
            },
            modalImpersonateUserOptions: {
                ref: 'UserImpersonateModal',
                open: false,
                base_endpoint: '/users',
                confirmLabel: 'Acceder como administrador',
                // cancelLabel: 'Cerrar',
                // hideConfirmBtn: true,
            },
            modalStatusOptions: {
                ref: 'UserStatusModal',
                open: false,
                base_endpoint: '/users',
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
        // vue.getSelects();
    },

    methods: {
        getSelects() {
            let vue = this

            // let params = vue.getAllUrlParams(window.location.search);

            // let param_subworkspace = params.subworkspace_id;
            // let param_document = params.document;
           
            // vue.filters.subworkspace_id = parseInt(param_subworkspace);
            // vue.filters.q = param_document;
            
            // if (param_subworkspace || param_document) {
            //     vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
            // }

            // const url = `/usuarios/get-list-selects`
            // vue.$http.get(url)
            //     .then(({data}) => {

            //         vue.selects.sub_workspaces = data.data.sub_workspaces;
            //         vue.criteria_template = data.data.criteria_template;
            //         vue.usersWithEmptyCriteria = data.data.users_with_empty_criteria

            //         data.data.criteria_workspace.forEach(criteria => {

            //             const new_select_obj = {[criteria.code]: criteria.values,};
            //             vue.selects = Object.assign({}, vue.selects, new_select_obj);

            //             const value = criteria.multiple ? [] : null;
            //             const new_filter_obj = {[criteria.code]: value};
            //             vue.filters = Object.assign({}, vue.filters, new_filter_obj);

            //         });

            //         // if (param_subworkspace)
            //         //     vue.filters.subworkspace_id = param_subworkspace

            //     })

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
</style>
