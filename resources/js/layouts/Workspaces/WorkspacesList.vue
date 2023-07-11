<template>
    <div class="wrapper">

        <v-row class="justify-content-center pt-3 pb-3 --mb-5">
            <div class="col-6">
                <v-row class="--justify-content-center --pt-3 --pb-3" align="center">
                    <div class="col-3">
                        <img :src="config.logo"
                             class="logo"
                            alt="Logo">
                    </div>
                    <div class="col-6">
                        <h1>Mis workspaces</h1>
                        <h3>Bienvenido(a) a {{ config.titulo }}</h3>
                        <small>Ingresa a un workspace para administrar su contenido</small>
                    </div>
                </v-row>
            </div>
            <div class="col-4">
                <div v-html="headerTemplate"></div>
            </div>
        </v-row>

        <v-row justify="space-between" class="py-6" style="background-color: #F9FAFB;"></v-row>

        <v-row justify="space-between" class="mt-5" >

            <v-col cols="1" class=""></v-col>

            <v-col cols="3">
                <DefaultInput
                    clearable dense
                    v-model="filters.q"
                    label="Buscar por nombre..."
                    @onEnter="loadData"
                    @clickAppendIcon="loadData"
                    append-icon="mdi-magnify"
                />
            </v-col>

            <v-col cols="2">
                <DefaultSelect
                    clearable dense
                    :items="selects.statuses"
                    v-model="filters.active"
                    label="Estado de workspace"
                    @onChange="loadData"
                    item-text="name"
                />
            </v-col>

            <v-col cols="5" class="d-flex justify-end">
                <v-btn
                    elevation="0"
                    small
                    color="primary"
                    :fab="true"
                    title="Crear workspace"
                    @click="createWorkspace"
                    class="mr-2"
                    v-if="is_superuser"
                    >
                    <v-icon v-text="'mdi-plus'"/>
                </v-btn>

                <v-btn
                    elevation="0"
                    small
                    color="primary"
                    title="Cambiar vista"
                    :fab="true"
                    @click="view = !view">
                    <v-icon v-text="view ? 'mdi-grid' : 'mdi-format-list-bulleted' "/>
                </v-btn>
            </v-col>

            <v-col cols="1" class=""></v-col>
        </v-row>

        <v-row class="justify-content-center">
            <v-col cols="10" class="workspaces-wrapper">
                <v-row class="mb-5">
                    <v-col v-for="workspace in workspaces"
                           :key="workspace.id"
                           :cols="view ? '3' : '12'" class="workspace"
                           >

                        <div class="row">
                            <v-col :cols="view ? '12' : '3'" class="logo-wrapper  pt-3 pb-3 cursor-pointer" @click="setActiveWorkspace(workspace.id, '/home')" title="Ir al workspace" 
                            :style="{'height': `${view ? '105px' : 'auto'}`}"
                            >

                                <img v-bind:src="workspace.logo"
                                     class="img-logo"
                                     alt=""
                                     >
                            </v-col>
                            <v-col :cols="view ? '12' : '3'" class=" justify-content-center bg-white text-bold d-flex align-items-center">
                                <span>{{ workspace.name }}</span>
                            </v-col>

                            <v-col :cols="view ? '12' : '6'" :class="'stats pt-4 pb-2 d-flex  align-items-center workspace-badge ' + (view ? 'justify-content-around' : 'justify-content-end')"
                                :style="{'background-color': `${view ? 'rgba(165, 166, 246, 0.2)' : 'white'}`}"
                            >

                                <button
                                    type="button" class="btn btn-md"
                                    title="Ir a módulos"
                                    @click="setActiveWorkspace(workspace.id, '/modulos')"
                                >
                                    <v-badge class="" :content="'' + workspace.modules_count">
                                        <v-icon class="icon" color="primary">mdi-sitemap</v-icon>
                                        <br> <span class="table-default-icon-title" v-text="'Módulos'"/>
                                    </v-badge>
                                </button>

                                <button
                                    type="button" class="btn btn-md"
                                    title="Ir a usuarios"
                                    @click="setActiveWorkspace(workspace.id, '/usuarios')"
                                >
                                    <v-badge :content="'' + workspace.users_count">
                                        <v-icon class="icon" color="primary">mdi-account-group</v-icon>
                                        <br> <span class="table-default-icon-title" v-text="'Usuarios'"/>
                                    </v-badge>
                                </button>

                                <button
                                    type="button" class="btn btn-md"
                                    title="Ir a cursos"
                                    @click="setActiveWorkspace(workspace.id, '/cursos')"
                                >
                                    <v-badge :content="'' + workspace.courses_count">
                                        <v-icon class="icon" color="primary">mdi-notebook</v-icon>
                                        <br> <span class="table-default-icon-title" v-text="'Cursos'"/>
                                    </v-badge>
                                </button>

                                <button
                                    type="button" class="btn btn-md"
                                    @click="editWorkspace(workspace.id)"
                                    v-show="!view && workspace.is_super_user"
                                >
                                    <span class="v-badge">
                                        <v-icon class="icon" color="primary">mdi-pencil</v-icon>
                                        <br> <span class="table-default-icon-title" v-text="'Editar'"/>
                                    </span>
                                </button>

                                <!-- <button
                                    type="button" class="btn btn-md"
                                    @click="openFormModal(
                                        workspaceDuplicateFormModalOptions,
                                        workspace,
                                        'duplicate',
                                        `Duplicar workspace - ${workspace.name}`
                                    )"
                                    v-show="!view && workspace.is_super_user"
                                >
                                    <span class="v-badge">
                                        <v-icon class="icon" color="primary">mdi-content-duplicate</v-icon>
                                        <br> <span class="table-default-icon-title" v-text="'Duplicar'"/>
                                    </span>
                                </button> -->

                                <button
                                    type="button" class="btn btn-md"
                                    @click="openFormModal(
                                        modalLogsOptions,
                                        workspace,
                                        'logs',
                                        `Logs del módulo - ${workspace.name}`
                                    )"
                                    v-show="!view && workspace.is_super_user"
                                >
                                    <span class="v-badge">
                                        <v-icon class="icon" color="primary">mdi-database</v-icon>
                                        <br> <span class="table-default-icon-title" v-text="'Log'"/>
                                    </span>
                                </button>

                            </v-col>
                  
                        </div>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>


        <v-row class="justify-content-end">

            <v-col cols="1" class=""></v-col>

            <v-col cols="1" class="d-flex align-items-end justify-end">
                <small
                    v-text="`${pagination.fromRow} - ${pagination.toRow} de ${pagination.total_rows}`"/>
            </v-col>
            
            <v-col cols="1" class="d-flex align-items-center justify-content-around">
                <v-icon :disabled="pagination.actual_page === 1" v-text="'mdi-chevron-left'"
                        @click="changePage(false)"/>
                <v-icon :disabled="pagination.actual_page === pagination.total_pages"
                        v-text="'mdi-chevron-right'"
                        @click="changePage(true)"/>
            </v-col>

            <v-col cols="1" class=""></v-col>
        </v-row>

        <v-row class="mb-15"></v-row>

        <LogsModal
            :options="modalLogsOptions"
            width="55vw"
            :model_id="null"
            model_type="App\Models\Workspace"
            :ref="modalLogsOptions.ref"
            @onCancel="closeSimpleModal(modalLogsOptions)"
        />

        <WorkspacesForm
            :options="workspaceFormModalOptions"
            width="60vw"
            :ref="workspaceFormModalOptions.ref"
            @onCancel="closeSimpleModal(workspaceFormModalOptions)"
            @onConfirm="loadData()"
        />

        <WorkspacesDuplicateForm
            :options="workspaceDuplicateFormModalOptions"
            width="60vw"
            :ref="workspaceDuplicateFormModalOptions.ref"
            @onCancel="closeSimpleModal(workspaceDuplicateFormModalOptions)"
            @onConfirm="loadData()"
        />

    </div>
</template>

<script>

import WorkspacesForm from "./WorkspacesForm";
import WorkspacesDuplicateForm from "./WorkspacesDuplicateForm";
import LogsModal from "../../components/globals/Logs";

export default {
    // props: [ 'header'],
    props: {
        is_superuser: {
            type: Boolean,
            required: false,
            default: false
        },
    },
    components: {
        WorkspacesForm, LogsModal, WorkspacesDuplicateForm
        
    },
    data: () => ({
        config: {
            logo: null,
            title: null,
        },
        headerTemplate : '',
        // superUserRoleId : 1,
        // configRoleId: 2,
        // adminRoleId : 3,
        // selectedWorkspaceId: null,
        workspaces: [],
        workspacesConfig: [],
        userSession: {},
        // configurationIsVisible: false,
        // canAccessConfiguration: false,
        workspaceFormModalOptions: {
            ref: 'WorkspacesForm',
            open: false,
            action: 'edit',
            base_endpoint: 'workspaces',
            showCloseIcon: true,
            confirmLabel: 'Guardar'
        },
        workspaceDuplicateFormModalOptions: {
            ref: 'WorkspacesDuplicateForm',
            open: false,
            action: 'duplicate',
            base_endpoint: 'workspaces',
            showCloseIcon: true,
            confirmLabel: 'Duplicar'
        },
        view: true,
        loading: true,
        data: [],
        pagination: {
            total_pages: 1,
            actual_page: 1,
            rows_per_page: 8,
            fromRow: 1,
            toRow: 1,
            total_rows: 0,
        },
        sortParams: {
            sortBy: null,
            sortDesc: false
        },
        filters: {
            q: null,
            tipo: [],
            fecha: [],
            order_by: null
        },
        selects: {
            statuses: [
                {id: null, name: 'Todos'},
                {id: 1, name: 'Activos'},
                {id: 2, name: 'Inactivos'},
            ],
        },
        filters: {
            q: '',
            active: 1,
            type: null,
            // category: null
        },
        modalLogsOptions: {
            ref: "LogsModal",
            open: false,
            showCloseIcon: true,
            base_endpoint: "/search",
            persistent: true
        },
    })
    ,
    mounted() {

        this.loadData();
        this.initializeHeaderTemplate();
    },
    // watch: {
    //     selectedWorkspaceId () {
    //         if (this.selectedWorkspaceId > 0) {
    //             this.setActiveWorkspace(
    //                 this.selectedWorkspaceId, false
    //             );
    //         }
    //     }
    // },
    methods: {
        initializeHeaderTemplate() {
            this.headerTemplate = document
                        .getElementById('header-template').innerHTML;
        }
        ,
        createWorkspace() {

            this.openFormModal(
                this.workspaceFormModalOptions,
                null,
                'create',
                'Crear workspace'
            );
            // this.setActiveWorkspace(workspaceId, false);
        },
        /**
         * Open form to edit workspace, and update the session workspace
         *
         * @param workspaceId
         */
        editWorkspace(workspaceId) {

            this.openFormModal(
                this.workspaceFormModalOptions,
                {workspaceId: workspaceId},
                'edit',
                'Editar workspace'
            );
            this.setActiveWorkspace(workspaceId, false);
        }
        ,
        /**
         * Open form to duplicate workspace
         *
         * @param workspaceId
         */
        duplicateWorkspace(workspaceId) {

            this.openFormModal(
                this.workspaceFormModalOptions,
                {workspaceId: workspaceId},
                'duplicate',
                'Duplicar workspace'
            );
            // this.setActiveWorkspace(workspaceId, false);
        }
        ,
        /**
         * Load workspaces list from server
         */
        loadData(page = null) {

            let vue = this;

            vue.showLoader()
            vue.loading = true

            if (page)
                vue.pagination.actual_page = page

            let url = `/workspaces/search?` +
                `page=${page || vue.pagination.actual_page}` +
                `&paginate=${vue.pagination.rows_per_page}`

            // if (vue.sortParams.sortBy) // Add param to sort result
                // url += `&sortBy=${vue.sortParams.sortBy}`

            // if (vue.sortParams.sortDesc) // Add param to sort orientation
                // url += `&sortDesc=${vue.sortParams.sortDesc}`

            const filters = vue.addParamsToURL("", vue.filters)
            // console.log('FILTROS :: ', filters)

            url = url + filters

            this.$http
                .get(url)
                .then(({data}) => {
                    vue.workspaces = data.data.workspaces.data;
                    vue.config = data.data.config;

                    // vue.data = data.data.data
                    // console.log(vue.data)
                    if (vue.pagination.actual_page > data.data.workspaces.total_pages)
                        vue.pagination.actual_page = data.data.workspaces.total_pages

                    vue.pagination.total_pages = data.data.workspaces.last_page;
                    vue.pagination.fromRow = data.data.workspaces.from || 0;
                    vue.pagination.toRow = data.data.workspaces.to || 0;
                    vue.pagination.total_rows = data.data.workspaces.total;
                    vue.loading = false

                    vue.hideLoader()

                    // this.loadSession();
                })
        },
        changePage(sum) {
            let vue = this
            if (sum) {
                if (vue.pagination.actual_page < vue.pagination.total_pages)
                    vue.pagination.actual_page++
            } else {
                if (vue.pagination.actual_page > 1)
                    vue.pagination.actual_page--
                else
                    vue.pagination.actual_page = 1
            }
            vue.loadData()
        },

        /**
         * Update workspace in User's session and
         * redirect to welcome page
         *
         * @param workspaceId
         * @param redirect
         */
        setActiveWorkspace(workspaceId, redirect = '/home') {

            let vue = this;

            // Submit request to update workspace in session

            let formData = vue.getMultipartFormData('PUT');
            let url = `/usuarios/session/workspace/${workspaceId}`;
            this.$http
                .post(url, formData)
                .then(() => {

                    // Redirect to welcome page

                    if (redirect) {
                        window.location.href = redirect;
                    }
                });
        }
        ,
        logout() {

            this.$http
                .post('/logout')
                .then(() => {
                    window.location.href = '/login';
                })
        }
    }
}
</script>

<style scoped>

.v-application--wrap {
    background: #ffffff !important;
}

.wrapper {
    background: white;
}

h1 {
    font-family: 'Roboto', serif;
    font-style: normal;
    font-weight: 700;
    font-size: 20px;
    line-height: 23px;
    color: #28314F;
}

h2 {
    font-family: 'Roboto', serif;
    font-style: normal;
    font-weight: 700;
    font-size: 16px;
    line-height: 19px;
    color: #28314F;
}

h3 {
    font-family: 'Roboto', serif;
    font-style: normal;
    font-weight: 400;
    font-size: 14px;
    line-height: 16px;
    color: #28314F;
}

.logo {
    max-height: 80px;
    max-width: 150px;
}


.workspace .row {
    margin-left: 0.1px;
    margin-right: 0.1px;
/*    box-shadow: 6px 1px 18px 2px rgb(212 213 219 / 10%);*/
/*    box-shadow: 3px 2px 20px 0px rgb(179 179 179 / 66%);*/
    box-shadow: 2px 2px 20px 0px rgb(217 217 217 / 50%);
}

.workspace .logo-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
/*    height: 105px;*/
/*    border-top-left-radius: 5px;*/
/*    border-top-right-radius: 5px;*/
    background: white;
}

.workspace .logo-wrapper .img-logo {
    max-height: 65%;
    max-width: 65%;
}

.workspace-badge button {
    cursor: pointer;
}

.workspace-badge .icon {
    font-size: 18px;
}

.workspace .logo-wrapper .edit-button {
    position: absolute;
    display: flex;
    justify-content: center;
    align-items: center;
    top: 0;
    right: 0;
    background: #A5A6F6;
    width: 30px;
    height: 30px;
    border-bottom-left-radius: 7px;
    cursor: pointer;
    opacity: 0;
    transition: opacity 500ms;
}

.workspace:hover .logo-wrapper .edit-button {
    opacity: 1;
}

.workspace .stats {
    font-family: 'Roboto', serif;
    font-style: normal;
    line-height: 13px;
/*    background-color: rgba(165, 166, 246, 0.2);*/
}

.workspace .stats .icon {
    color: #5d5fef;
}

.workspace .stats .number {
    font-size: 12px;
    font-weight: 600;
}

.workspace .stats .label {
    font-size: 12px;
}

.card {
    background: white;
    border: none;
}

.cursor-pointer {
    cursor: pointer;
}

.table-default-icon-title {
    font-size: 0.85em !important;
    font-weight: 400;
}

</style>
