<template>

    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <!--                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>-->
                Roles
                <v-spacer/>
                <!--                <DefaultActivityButton-->
                <!--                    :label="'Actividad'"-->
                <!--                    @click="activity"/>-->
                <DefaultModalButton
                    :label="'Rol'"
                    @click="openFormModal(modalOptions, null, 'create')"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-between">
                    <!--         <v-col cols="4">
                                <DefaultSelect clearable dense
                                               :items="selects.modules"
                                               v-model="filters.module"
                                               label="Módulos"
                                               @onChange="refreshDefaultTable(dataTable, filters)"
                                />
                            </v-col> -->
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
                    <v-col cols="4">
                 <!--        <v-card class="mr-10 " elevation="0">
                            <v-card-text style="text-align:end;">
                                <span style="font-weight:bolder;" v-text="`Total de usuarios activos: ${active_users_count} / ${limit_allowed_users || '-'}`"></span>
                            </v-card-text>
                        </v-card> -->
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :avoid_first_data_load="dataTable.avoid_first_data_load"
                @reset="reset"
                @logs="
                    openFormModal(
                        modalLogsOptions,
                        $event,
                        'logs',
                        `Logs de rol - ${$event.name}`
                    )
                "
                @edit="openFormModal(modalOptions, $event, 'edit')"
            />
            <RoleFormModal
                width="45vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
                @onCancel="closeFormModal(modalOptions)"
            />
            <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\Role"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />
            <DefaultAlertDialog
                :ref="modalDeleteOptions.ref"
                :options="modalDeleteOptions">
                <template v-slot:content> {{ modalDeleteOptions.contentText }}</template>
            </DefaultAlertDialog>
        </v-card>
    </section>
</template>

<script>
import RoleFormModal from "./RoleFormModal";
import LogsModal from "../../components/globals/Logs";

export default {
    props: ["config_id"],
    components: { RoleFormModal, LogsModal },
    data() {
        return {
            active_users_count: '-',
            limit_allowed_users: '-',
            breadcrumbs: [
                {title: 'Roles', text: null, disabled: true, href: 'null'},
            ],
            dataTable: {
                avoid_first_data_load: false,
                endpoint: '/roles/search',
                ref: 'modulosTable',
                headers: [
                    {text: "ID", value: "id", align: 'center', sortable: false},
                    {text: "Nombre", value: "title"},
                    {text: "Código", value: "name"},
                    // {text: "Activos / Total", value: "active_users", sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    // {
                    //     text: "Escuelas",
                    //     icon: 'fas fa-school',
                    //     type: 'route',
                    //     // method_name: 'reset',
                    //     count: 'schools_count',
                    //     route: 'schools_route'
                    // },
                    // {
                    //     text: "Usuarios",
                    //     icon: 'fas fa-user',
                    //     type: 'route',
                    //     route: 'users_route',
                    //     count: 'users_count'
                    // },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
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
                modules: []
            },
            filters: {
                q: '',
                module: null,
            },
            modalOptions: {
                ref: 'RoleFormModal',
                open: false,
                base_endpoint: '/roles',
                confirmLabel: 'Guardar',
                resource: 'Rol',
                title: '',
                action: null,
                selects: {
                    modules: [],
                    // boticas: [],
                    // groups: [],
                    // cargos: [],
                }
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalDeleteOptions: {
                ref: "RoleDeleteModal",
                title: "Eliminar rol",
                contentText: "¿Desea eliminar este registro?",
                open: false,
                endpoint: ""
            }
        };
    },
    mounted() {
        let vue = this
        // vue.getSelects();

        vue.filters.module = vue.config_id

        // === check localstorage vademecum ===
        if(vue.dataTable.avoid_first_data_load) {
            vue.refreshDefaultTable(vue.dataTable, vue.filters, 1);
            const { storage: roleStorage } = vue.getStorageUrl('role');
            vue.openFormModal(vue.modalOptions, { id: roleStorage.id });
        }
        // === check localstorage vademecum ===
    },
    created() {
        let vue = this;
        
        // === check localstorage roles ===
        const { status, storage: roleStorage } = vue.getStorageUrl('role');
        if(status) {
            vue.filters.q = roleStorage.q;
            vue.dataTable.avoid_first_data_load = true;
        }
        // === check localstorage role ===
    },
    methods: {
        // getSelects() {
        //     let vue = this
        //     const url = `/role/get-list-selects`
        //     vue.$http.get(url)
        //         .then(({data}) => {
        //             const {limit_allowed_users, active_users_count} = data.data;
        //             vue.active_users_count = active_users_count
        //             vue.limit_allowed_users = limit_allowed_users
        //         })
        // },
        reset(user) {
            let vue = this
            // vue.consoleObjectTable(user, 'User to Reset')
        },
        activity() {
            console.log('activity')
        },
    }
}
</script>
