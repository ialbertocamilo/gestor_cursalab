<template>
    <div class="wrapper">

        <v-row class="justify-content-center py-4 --mb-5">
            <div class="col-4">
                <v-row class="--justify-content-center --pt-3 --pb-3" align="center">
                    <div class="col-5">
                        <img :src="config.logo"
                             class="logo"
                            alt="Logo">
                    </div>
                    <div class="col-7">
                        <h1>Mis workspaces</h1>
                        <h3>Bienvenido a {{ config.titulo }}</h3>
                        <!-- <small>Ingresa a un workspace para administrar su contenido</small> -->
                    </div>
                </v-row>
            </div>
            <div v-if="true" class="col-4 d-flex justify-content-center align-items-center" style="gap: 1.5rem">
                <div :class="`${ !showDetail ? 'd-flex' : 'd-none' } align-items-end`">
                    <!-- <i class="mdi mdi-cloud-outline fa-2x mr-3"></i> -->
                    <div class="d-flex flex-column">
                        <p class="font-weight-bold mb-0">Almacenamiento utilizado</p>

                        <a href class="fa-2x" @click.prevent="showDetail = true" title="Ver detalle">
                            <!-- <span class="fa-2x" v-text="workspaces_total.workspaces_total_storage"> -->
                            <!-- <span class="fa-2x"> -->
                            <i class="mdi mdi-cloud-outline --fa-2x mr-3"></i>{{ workspaces_total.workspaces_total_storage }}
                            <!-- </span> -->
                            <!-- Ver detalle <span class="ml-2 fas fa-arrow-right"></span> -->
                        </a>
                    </div>
                </div>

                <div :class="`${ showDetail && 'd-none' } bg-secondary --h-100`" style="width: 1px; height: 75%;"></div>

                <div :class="`${ !showDetail ? 'd-flex' : 'd-none' } align-items-end`">
                    <!-- <i class="mdi mdi-account-multiple-outline fa-2x mr-3"></i> -->
                    <div class="d-flex flex-column">
                        <p class="font-weight-bold mb-0">Total usuarios activos</p>
                        <!-- <span class="fa-2x" v-text="workspaces_total.workspaces_total_users.toLocaleString()"> -->

                        <a href class="fa-2x" @click.prevent="showDetail = true" title="Ver detalle">
                            <!-- <span class="fa-2x"> -->
                            <i class="mdi mdi-account-multiple-outline --fa-2x mr-3"></i>{{ workspaces_total.workspaces_total_users.toLocaleString() }}
                            <!-- </span> -->
                            <!-- Ver detalle <span class="ml-2 fas fa-arrow-right"></span> -->
                        </a>
                    </div>
                </div>

                <div class="d-flex flex-column"
                    style="gap:1rem;"
                    v-if="false">
                    <v-btn color="primary" @click="openLink('/auditoria')">
                        <span class="mdi mdi-note-text-outline fa-lg mr-2"></span>
                        Auditoria
                    </v-btn>

                    <v-btn color="primary">
                        <span class="mdi mdi-plus fa-lg mr-2"></span>
                        Workspace
                    </v-btn>
                </div>
            </div>
            <div class="col-2">
                <div v-html="headerTemplate"></div>
            </div>
        </v-row>

        <v-row justify="space-between" class="py-6" style="background-color: #F9FAFB;"></v-row>

        <v-row justify="space-between" class="mt-5" v-show="!showDetail">

            <v-col cols="1" class=""></v-col>

            <v-col cols="3">
                <DefaultInput
                    clearable dense
                    v-model="filters.q"
                    label="Buscar por nombre..."
                    @onEnter="loadData(1)"
                    @clickAppendIcon="loadData(1)"
                    append-icon="mdi-magnify"
                />
            </v-col>

            <v-col cols="2">
                <DefaultSelect
                    clearable dense
                    :items="selects.statuses"
                    v-model="filters.active"
                    label="Estado de workspace"
                    @onChange="loadData(1)"
                    item-text="name"
                />
            </v-col>

            <v-col cols="5" class="d-flex justify-end">
                <DefaultModalButton
                    icon_name="fas fa-cog"
                    :label="'Configurar espacio'"
                    @click="openFormModal(modalConfigAmbienteOptions, null, null, 'Configurar espacio')"
                />
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
                    @click="view = !view,setPreference()">
                    <v-icon v-text="view ? 'mdi-grid' : 'mdi-format-list-bulleted' "/>
                </v-btn>
            </v-col>

            <v-col cols="1" class=""></v-col>
        </v-row>
        <!--
            Workspaces title
        ======================================== -->
        <v-row v-show="showDetail" class="justify-content-center mt-3 py-3">
            <v-col cols="10">
                <b class="btn_select_media" @click="showDetail = false">
                    <span class="fas fa-arrow-left  mr-2"></span> Gestión de almacenamiento y usuarios
                </b>
            </v-col>
        </v-row>


        <div v-show="!showDetail">

            <!--
                Workspaces
            ======================================== -->

            <v-row class="justify-content-center">
                <v-col cols="10" class="workspaces-wrapper">
                    <v-row class="mb-5">
                        <v-col v-for="workspace in workspaces"
                               :key="workspace.id"
                               :cols="view ? '3' : '12'" class="workspace"
                               >

                            <div class="row" :style="{'border': `${workspace.active ? '2px solid white' : '2px solid lightgray'}`}">
                                <v-col :cols="view ? '12' : '3'" class="logo-wrapper  pt-3 pb-3 cursor-pointer" @click="setActiveWorkspace(workspace.id, '/home')" title="Ir al workspace"
                                :style="{'height': `${view ? '105px' : 'auto'}`}"
                                >

                                    <img v-bind:src="workspace.logo"
                                         class="img-logo"
                                         alt=""
                                         >
                                </v-col>
                                <v-col :cols="view ? '12' : '3'" class=" justify-content-center --bg-white text-bold d-flex align-items-center">
                                    <span :style="{'color': `${workspace.active ? 'black' : 'gray'}`}">{{ workspace.name }}</span>
                                </v-col>

                                <v-col :cols="view ? '12' : '6'" :class="'stats pt-4 pb-2 d-flex  align-items-center workspace-badge ' + (view ? 'justify-content-around' : 'justify-content-end')"
                                    :style="{'background-color': `${view ? 'rgba(165, 166, 246, 0.2)' : 'white'}`}"
                                >

                                    <button
                                        type="button" class="btn btn-md"
                                        title="Ir a módulos"
                                        @click="setActiveWorkspace(workspace.id, '/modulos')"
                                    >
                                        <v-badge class="" :content="'' + workspace.modules_count" :color="workspace.active ? 'primary' : 'grey'">
                                            <v-icon class="icon" :color="workspace.active ? 'primary' : 'grey'">mdi-sitemap</v-icon>
                                            <br> <span class="table-default-icon-title" v-text="'Módulos'"/>
                                        </v-badge>
                                    </button>

                                    <button
                                        type="button" class="btn btn-md"
                                        title="Ir a usuarios"
                                        @click="setActiveWorkspace(workspace.id, '/usuarios')"
                                    >
                                        <v-badge :content="'' + workspace.users_count" :color="workspace.active ? 'primary' : 'grey'">
                                            <v-icon class="icon" :color="workspace.active ? 'primary' : 'grey'">mdi-account-group</v-icon>
                                            <br> <span class="table-default-icon-title" v-text="'Usuarios'"/>
                                        </v-badge>
                                    </button>

                                    <button
                                        type="button" class="btn btn-md"
                                        title="Ir a cursos"
                                        @click="setActiveWorkspace(workspace.id, '/cursos')"
                                    >
                                        <v-badge :content="'' + workspace.courses_count" :color="workspace.active ? 'primary' : 'grey'"> 
                                            <v-icon class="icon" :color="workspace.active ? 'primary' : 'grey'">mdi-notebook</v-icon>
                                            <br> <span class="table-default-icon-title" v-text="'Cursos'"/>
                                        </v-badge>
                                    </button>

                                    <button
                                        type="button" class="btn btn-md"
                                        @click="editWorkspace(workspace.id, workspace.name)"
                                        v-show="!view && workspace.is_cursalab_super_user"
                                    >
                                        <span class="v-badge">
                                            <v-icon class="icon" :color="workspace.active ? 'primary' : 'grey'">mdi-pencil</v-icon>
                                            <br> <span class="table-default-icon-title" v-text="'Editar'"/>
                                        </span>
                                    </button>
                                    <button
                                        type="button" class="btn btn-md"
                                        @click="openFormModal(emailsCustomFormModalOptions,workspace,null,'Customizar correos')"
                                        v-show="!view && workspace.is_cursalab_super_user"
                                    >
                                        <span class="v-badge">
                                            <v-icon class="icon" :color="workspace.active ? 'primary' : 'grey'">mdi-email</v-icon>
                                            <br> <span class="table-default-icon-title" v-text="'Correos'"/>
                                        </span>
                                    </button>
                                    <button
                                        type="button" class="btn btn-md"
                                        @click="openFormModal(
                                            workspaceDuplicateFormModalOptions,
                                            workspace,
                                            'duplicate',
                                            `Duplicar workspace - ${workspace.name}`
                                        )"
                                        v-show="!view && workspace.is_cursalab_super_user"
                                    >
                                        <span class="v-badge">
                                            <v-icon class="icon" :color="workspace.active ? 'primary' : 'grey'">mdi-content-duplicate</v-icon>
                                            <br> <span class="table-default-icon-title" v-text="'Duplicar'"/>
                                        </span>
                                    </button>

                                    <button
                                        type="button" class="btn btn-md"
                                        @click="openFormModal(
                                            modalStatusOptions,
                                            workspace,
                                            'status',
                                            `Actualizar estado de workspace - ${workspace.name}`
                                        )"
                                        v-show="!view && workspace.is_cursalab_super_user"
                                    >
                                        <span class="v-badge">
                                            <v-icon class="icon" :color="workspace.active ? 'primary' : 'grey'">mdi-circle</v-icon>
                                            <br> <span class="table-default-icon-title" v-text="'Actualizar estado'"/>
                                        </span>
                                    </button>

                                    <button
                                        type="button" class="btn btn-md"
                                        @click="openFormModal(
                                            modalDeleteOptions,
                                            workspace,
                                            'delete',
                                            `Eliminar workspace - ${workspace.name}`
                                        )"
                                        v-show="!view && workspace.is_cursalab_super_user"
                                    >
                                        <span class="v-badge">
                                            <v-icon class="icon" :color="workspace.active ? 'primary' : 'grey'">mdi-delete</v-icon>
                                            <br> <span class="table-default-icon-title" v-text="'Eliminar'"/>
                                        </span>
                                    </button>

                                    <button
                                        type="button" class="btn btn-md"
                                        @click="openFormModal(
                                            modalLogsOptions,
                                            workspace,
                                            'logs',
                                            `Logs del workspace - ${workspace.name}`
                                        )"
                                        v-show="!view && workspace.is_cursalab_super_user"
                                    >
                                        <span class="v-badge">
                                            <v-icon class="icon" :color="workspace.active ? 'primary' : 'grey'">mdi-database</v-icon>
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
        </div>


        <v-row class="justify-content-center --mt-3 --pt-3 pb-3" v-show="showDetail">
            <v-col cols="10" class="workspaces-wrapper" style="background: #f8f8fb; border-radius: 6px; padding: 10px 25px;">
                <v-row>
                    <v-col cols="6">
                        <v-card elevation="0" class="mb-5">
                            <v-card-title class="justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="mdi mdi-cloud-outline fa-2x mr-3"></span>
                                    Almacenamiento general
                                </div>
                                <div>
                                    <span v-text="workspaces_total.workspaces_total_storage"></span>
                                </div>
                            </v-card-title>
                        </v-card>

                        <div class="row mx-3" v-for="workspace of workspaces_status_total" :key="workspace.id">
                            <div class="col-3 align-self-center" v-text="workspace.name"></div>
                            <div class="col-9 d-flex align-items-center">
                                <div class="d-flex flex-column w-100">
                                    <div class="mb-2 d-flex justify-space-between">
                                        <div>
                                            Utilizado: <span class="font-weight-bold" v-text="workspace.size_medias_storage"></span>
                                        </div>
                                        <div>
                                            Total: <span class="font-weight-bold" v-text="workspace.size_medias_limit"></span>
                                        </div>
                                    </div>

                                    <v-progress-linear
                                        :color="workspace.size_medias_porcent.exceded ? 'red' : 'primary'"
                                        height="25"
                                        :value="workspace.size_medias_porcent.porcent"
                                        rounded
                                    >
                                        <div class="d-flex justify-content-end"
                                            :style="{ width: (workspace.size_medias_porcent.porcent < 10) ? '8%' :workspace.size_medias_porcent.porcent +'%'}">
                                            <strong
                                                class="text-white text-right"
                                                v-text="workspace.size_medias_porcent.porcent + '%'">
                                            </strong>
                                        </div>
                                    </v-progress-linear>
                                </div>

                             <!--    <v-btn
                                    class="ml-2"
                                    text
                                    color="primary"
                                    @click="setActiveWorkspaceRoute(workspace.id, true, 'home')">
                                    <v-icon>
                                        mdi-open-in-new
                                    </v-icon>
                                </v-btn> -->
                            </div>
                        </div>

                    </v-col>

                    <v-col cols="6">
                        <v-card elevation="0" class="mb-5">
                            <v-card-title class="justify-content-between">
                                <div class="d-flex align-items-center">
                                    <span class="mdi mdi-account-multiple-outline fa-2x mr-3"></span>
                                    Total usuarios activos
                                </div>
                                <div>
                                    <span v-text="workspaces_total.workspaces_total_users.toLocaleString()"></span>
                                </div>
                            </v-card-title>
                        </v-card>

                        <div class="row mx-3" v-for="workspace of workspaces_status_total" :key="workspace.id">
                            <div class="col-12 d-flex align-items-center">
                                <div class="d-flex flex-column w-100">
                                    <div class="mb-2 d-flex justify-space-between">
                                        <div>
                                            Activos: <span class="font-weight-bold" v-text="workspace.users_count_actives"></span>
                                        </div>
                                        <div>
                                            Total: <span class="font-weight-bold" v-text="workspace.users_count_limit"></span>
                                        </div>
                                    </div>

                                    <v-progress-linear
                                        :color="workspace.users_count_porcent.exceded ? 'red' : 'primary'"
                                        height="25"
                                        :value="workspace.users_count_porcent.porcent"
                                        rounded
                                    >
                                        <div class="d-flex justify-content-end"
                                            :style="{ width: (workspace.users_count_porcent.porcent < 10) ? '8%' :workspace.users_count_porcent.porcent +'%'}">
                                            <strong
                                                class="text-white text-right"
                                                v-text="workspace.users_count_porcent.porcent + '%'">
                                            </strong>
                                        </div>
                                    </v-progress-linear>
                                </div>

                                <!-- <v-btn
                                    class="ml-2"
                                    text
                                    color="primary"
                                    @click="setActiveWorkspaceRoute(workspace.id, true, 'usuarios')">
                                    <v-icon>
                                        mdi-open-in-new
                                    </v-icon>
                                </v-btn> -->
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>


        <!--
            Modals
        ======================================== -->

        <LogsModal
            :options="modalLogsOptions"
            width="55vw"
            :model_id="null"
            model_type="App\Models\Workspace"
            :ref="modalLogsOptions.ref"
            @onCancel="closeSimpleModal(modalLogsOptions)"
        />

        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onCancel="closeFormModal(modalDeleteOptions)"
            @onConfirm="closeFormModal(modalDeleteOptions); loadData();"
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
        <EmailsCustomModalForm
            :options="emailsCustomFormModalOptions"
            width="60vw"
            :ref="emailsCustomFormModalOptions.ref"
            @onCancel="closeSimpleModal(emailsCustomFormModalOptions)"
            @onConfirm="closeSimpleModal(emailsCustomFormModalOptions)"
        />

<!--         <DialogConfirm
            :ref="modalStatusOptions.ref"
            v-model="modalStatusOptions.open"
            width="450px"
            title="Cambiar de estado al workspace"
            subtitle="¿Está seguro de cambiar de estado al workspace?"
            @onConfirm="closeFormModal(modalStatusOptions); loadData();"
            @onCancel="closeFormModal(modalStatusOptions);"
        /> -->

        <DefaultStatusModal
            :options="modalStatusOptions"
            :ref="modalStatusOptions.ref"
            @onConfirm="closeFormModal(modalStatusOptions); loadData();"
            @onCancel="closeFormModal(modalStatusOptions)"
        />
        <ConfigAmbiente
            :options="modalConfigAmbienteOptions"
            :ref="modalConfigAmbienteOptions.ref"
            @onConfirm="closeFormModal(modalConfigAmbienteOptions)"
            @onCancel="closeFormModal(modalConfigAmbienteOptions)"
        />
    </div>
</template>

<script>

import WorkspacesForm from "./WorkspacesForm";
import ConfigAmbiente from './ConfigAmbiente';
import WorkspacesDuplicateForm from "./WorkspacesDuplicateForm";
import LogsModal from "../../components/globals/Logs";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
// import DialogConfirm from "../../components/basicos/DialogConfirm";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import EmailsCustomModalForm from "./EmailsCustomModalForm";


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
        WorkspacesForm, LogsModal, WorkspacesDuplicateForm, DefaultDeleteModal, DefaultStatusModal,ConfigAmbiente,
        EmailsCustomModalForm
    },
    data: () => ({
        config: {
            logo: null,
            title: null,
        },
        headerTemplate : '',
        superUserRoleId : 1,
        configRoleId: 2,
        adminRoleId : 3,
        selectedWorkspaceId: null,
        workspaces: [],
        workspacesConfig: [],
        userSession: {},
        configurationIsVisible: false,
        canAccessConfiguration: false,
        showDetail: false,
        workspaces_total:{
            workspaces_total_storage: '',
            workspaces_total_users: '',
        },
        workspaces_status_total:[],
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
        emailsCustomFormModalOptions:{
            ref: ' emailsCustomForm',
            open: false,
            action: '',
            base_endpoint: 'workspaces',
            showCloseIcon: true,
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
        modalDeleteOptions: {
            ref: 'WorkspaceDeleteModal',
            open: false,
            base_endpoint: '/workspaces',
            contentText: '¿Desea eliminar este registro?',
            endpoint: '',
            width: '40vw',
        },
        modalStatusOptions: {
            ref: 'WorkspaceStatusModal',
            open: false,
            base_endpoint: '/workspaces',
            // contentText: '¿Desea actualizar este registro?',
            endpoint: '',
            width: '40vw',
            content_modal: {
                active: {
                    title: '¿Desea activar este workspace?',
                    details: [
                        'El workspace quedará habilitado para su uso.',
                        'Todos los usuarios y administradores podrán volver a iniciar sesión.',
                    ]
                },
                inactive: {
                    title: '¿Desea inactivar este workspace?',
                    details: [
                        'El workspace quedará deshabilitado para su uso.',
                        'Todos los usuarios y administradores no podrán iniciar sesión.',
                    ]
                },
            }
        },
        modalConfigAmbienteOptions:{
            ref: 'ConfigAmbienteModal',
            open: false,
            base_endpoint: '/ambiente',
            confirmLabel: 'Guardar',
            resource: 'Ambiente',
            title: 'Configurar Ambiente',
            action: null,
            persistent: true,
        }
    })
    ,
    mounted() {

        this.loadData();
        this.initializeHeaderTemplate();
        this.loadDataStorage();
        this.loadPreference();
    },
    watch: {
        selectedWorkspaceId () {
            if (this.selectedWorkspaceId > 0) {
                this.setActiveWorkspace(
                    this.selectedWorkspaceId, false
                );
            }
        }
    },
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
        editWorkspace(workspaceId, workspace_name) {

            this.openFormModal(
                this.workspaceFormModalOptions,
                {workspaceId: workspaceId},
                'edit',
                `Editar workspace - ${workspace_name}`
            );
            this.setActiveWorkspace(workspaceId, false);
        }
        ,
        /**
         * Open form to store workspace, and update the session workspace
         *
         * @param workspaceId
         */
        storeWorkspace() {

            this.workspaceFormModalOptions.action = 'create';

            this.openFormModal(
                this.workspaceFormModalOptions,
                null,
                null,
                'Crear workspace'
            );
        }
        ,
        /**
         * Load workspaces list from server
         */
        // loadData() {

        //     let vue = this;

        //     let url = `/workspaces/search`

        //     this.$http
        //         .get(url)
        //         .then(({data}) => {
        //             vue.workspaces = data.data.data;
        //             this.loadSession();
        //         })
        // },
        /**
         * Load session data from server
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
         * redirect to default page
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

                    // Redirect to default page

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
        ,
        setActiveWorkspaceRoute(workspaceId, redirect, route = 'home') {

            let vue = this;

            // Submit request to update workspace in session

            let formData = vue.getMultipartFormData('PUT');
            let url = `/usuarios/session/workspace/${workspaceId}`;
            this.$http
                .post(url, formData)
                .then(() => {

                    // Redirect to default page

                    if (redirect) {
                        window.location.href = '/'+route;
                    }
                });
        }
        ,
         /**
         * Load workspaces list from server
         */
        loadDataStorage() {

            let vue = this;

            let url = `/general/workspaces-status`
            vue.showLoader();

            this.$http
                .get(url)
                .then(({data}) => {
                    const { workspaces_total, workspaces_status_total } = data.data

                    vue.workspaces_total = workspaces_total;
                    vue.workspaces_status_total = workspaces_status_total;

                    vue.hideLoader();
                })
            
        },
        loadPreference(){
            //Preferencia al cargar los datos listado o grilla
            let preferencesJSON = localStorage.getItem('preferences');
            let preferences = {};
            if (!preferencesJSON) {
                preferences = {
                    workspace_list_diplay_format: 'grilla' // Valor predeterminado para mostrar en formato de grilla
                };
                this.view = true;
                localStorage.setItem('preferences', JSON.stringify(preferences));
            } else {
                preferences = JSON.parse(preferencesJSON);
                this.view = preferences.workspace_list_diplay_format === 'grilla';
            }
        },
        setPreference(){
            let preferencesJSON = localStorage.getItem('preferences');
            let preferences = JSON.parse(preferencesJSON);
            preferences.workspace_list_diplay_format = this.view ? 'grilla' : 'lista';
            localStorage.setItem('preferences', JSON.stringify(preferences));
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
