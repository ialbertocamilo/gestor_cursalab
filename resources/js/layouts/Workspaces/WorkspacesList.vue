<template>
    <div class="wrapper">

        <v-row class="justify-content-center pt-3 pb-3">
            <div class="col-6">
                <v-row class="--justify-content-center --pt-3 --pb-3" align="center">
                    <div class="col-4">
                        <img src="/img/we-connect-logo.png"
                             class="logo"
                            alt="We connect">
                    </div>
                    <div class="col-6">
                        <h1>Mis workspaces</h1>
                        <h3>Bienvenido(a) a WeConnect 2.0</h3>
                        <small>
                            Ingresa a un workspace para administrar  su contenido
                        </small>
                    </div>
                </v-row>
            </div>
            <div class="col-4">
                <div v-html="headerTemplate"></div>
            </div>
        </v-row>

        <!--
            Workspaces
        ======================================== -->

        <v-row justify="space-between" class="mx-1" style="background-color: #F9FAFB; border-radius: 6px">
            <v-col cols="4">
                <v-btn
                    elevation="0"
                    small
                    color="primary"
                    :fab="view === 'grid'"
                    :icon="view === 'list'"
                    @click="view = 'grid'">
                    <v-icon v-text="'mdi-grid' "/>
                </v-btn>
                <v-btn
                    elevation="0"
                    small
                    color="primary"
                    :fab="view === 'list'"
                    :icon="view === 'grid'"
                    @click="view = 'list'">
                    <v-icon v-text="'mdi-format-list-bulleted'"/>
                </v-btn>
            </v-col>
            <!-- <v-col cols="2">
                <DefaultSelect
                    label="Ordenar por"
                    dense
                    v-model="filters.order_by"
                    :items="selects.order_by"
                    @onChange="getData"
                />
            </v-col> -->
        </v-row>

        <v-row class="justify-content-center">
            <v-col cols="10" class="workspaces-wrapper">
                <v-row class="mb-5">
                    <v-col v-for="workspace in workspaces"
                           :key="workspace.id"
                           cols="3" class="workspace">

                        <div class="row">
                            <div class="logo-wrapper col-12 pt-3 pb-3 cursor-pointer" @click="setActiveWorkspace(workspace.id, '/welcome')" title="Ir al workspace" >

                                <img v-bind:src="workspace.logo"
                                     class="logo"
                                     alt="">

                               <!--  <div @click="editWorkspace(workspace.id)"
                                     v-if="workspace.isAdminInWorkspace"
                                     class="edit-button">
                                    <v-icon color="white" size="16px">
                                        mdi-square-edit-outline
                                    </v-icon>
                                </div> -->
                            </div>
                            <div class="col-12 text-center bg-white">
                                <span>{{ workspace.name }}</span>
                            </div>
                            <!-- <div class="col-4 stats pt-3 d-flex justify-content-center align-items-center cursor-pointer" @click="setActiveWorkspace(workspace.id, '/modulos')">
                                <v-icon class="icon" size="25px">mdi-sitemap</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">
                                        {{ workspace.modules_count }}
                                    </span><br>
                                    <span class="label">módulos</span>
                                </div>
                            </div>

                            <div class="col-4 stats pt-3 d-flex justify-content-center align-items-center cursor-pointer" @click="setActiveWorkspace(workspace.id, '/usuarios')">
                                <v-icon class="icon" size="25px">mdi-account-group</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">
                                        {{ workspace.users_count }}
                                    </span><br>
                                    <span class="label">usuarios</span>
                                </div>
                            </div>
 -->
                            <!-- <div class="col-4 stats pt-3 d-flex justify-content-center align-items-center cursor-pointer" @click="setActiveWorkspace(workspace.id, '/cursos')">
                                <v-icon class="icon" size="25px">mdi-notebook</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">
                                        {{ workspace.courses_count }}
                                    </span><br>
                                    <span class="label">cursos</span>
                                </div>
                            </div> -->

                                <!-- v-if="!action.show_condition || (action.show_condition && item[action.show_condition])" -->

                            <div class="col-12 stats pt-6 d-flex justify-content-center align-items-center cursor-pointer workspace-badge">

                                <button
                                    type="button" class="btn btn-md mx-2"
                                    title="Ir a módulos"
                                    @click="setActiveWorkspace(workspace.id, '/modulos')"
                                >
                                    <v-badge class="" :content="workspace.modules_count">
                                        <v-icon class="icon" color="primary">mdi-sitemap</v-icon>
                                        <br> <span class="table-default-icon-title" v-text="'Módulos'"/>
                                    </v-badge>
                                </button>

                                <button
                                    type="button" class="btn btn-md mx-2"
                                    title="Ir a usuarios"
                                    @click="setActiveWorkspace(workspace.id, '/usuarios')"
                                >
                                    <v-badge :content="workspace.users_count">
                                        <v-icon class="icon" color="primary">mdi-account-group</v-icon>
                                        <br> <span class="table-default-icon-title" v-text="'Usuarios'"/>
                                    </v-badge>
                                </button>

                                <button
                                    type="button" class="btn btn-md mx-2"
                                    title="Ir a cursos"
                                    @click="setActiveWorkspace(workspace.id, '/cursos')"
                                >
                                    <v-badge :content="workspace.courses_count">
                                        <v-icon class="icon" color="primary">mdi-notebook</v-icon>
                                        <br> <span class="table-default-icon-title" v-text="'Cursos'"/>
                                    </v-badge>
                                </button>

                            </div>

                            <!-- <div class="col-12 pt-3 pb-3 button-wrapper d-flex justify-content-center">
                                <button @click="setActiveWorkspace(workspace.id, '/welcome')"
                                        class="btn">
                                    Ingresar
                                </button>
                            </div> -->
                        </div>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>

        <v-row class="justify-content-end">
            <v-col cols="1" class="d-flex align-items-end justify-content-around">
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
        </v-row>


        <WorkspacesForm
            :options="workspaceFormModalOptions"
            width="60vw"
            :ref="workspaceFormModalOptions.ref"
            @onCancel="closeSimpleModal(workspaceFormModalOptions)"
            @onConfirm="loadData()"
        />

    </div>
</template>

<script>

import WorkspacesForm from "./WorkspacesForm";
// import WorkspacesListGrid from "./WorkspacesListGrid";
// import WorkspacesListView from "./WorkspacesListView";

export default {
    props: [ 'header' ],
    components: {
        WorkspacesForm,
        
    },
    data: () => ({
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
        workspaceFormModalOptions: {
            ref: 'WorkspacesForm',
            open: false,
            action: 'edit',
            base_endpoint: 'workspaces',
            confirmLabel: 'Guardar'
        },
        view: 'grid',
        loading: true,
        data: [],
        pagination: {
            total_pages: 1,
            actual_page: 1,
            rows_per_page: 6,
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
        /**
         * Open form to edit workspace, and update the session workspace
         *
         * @param workspaceId
         */
        editWorkspace(workspaceId) {

            this.openFormModal(
                this.workspaceFormModalOptions,
                {workspaceId: workspaceId},
                null,
                'Editar workspace'
            );
            this.setActiveWorkspace(workspaceId, false);
        }
        ,
        /**
         * Load workspaces list from server
         */
        loadData(page = null) {

            let vue = this;

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
                    vue.workspaces = data.data.data;

                    // vue.data = data.data.data
                    // console.log(vue.data)
                    if (vue.pagination.actual_page > data.data.total_pages)
                        vue.pagination.actual_page = data.data.total_pages

                    vue.pagination.total_pages = data.data.last_page;
                    vue.pagination.fromRow = data.data.from || 0;
                    vue.pagination.toRow = data.data.to || 0;
                    vue.pagination.total_rows = data.data.total;
                    vue.loading = false

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
         * Load session data from server
         */
        // loadSession() {

        //     let vue = this;

        //     // Load session data
        //     vue.workspacesAdmin = [];
        //     let url = `/usuarios/session`
        //     this.$http
        //         .get(url)
        //         .then(({data}) => {

        //             vue.userSession = data;
        //             vue.findConfigWorkspaces();


        //         });
        // }
        // ,
        /**
         * Find those workspaces the user has config access to
         */
        // findConfigWorkspaces() {

        //     let vue = this;

        //     if (!vue.userSession.user) return;

        //     // When user roles include admin (3)
        //     // allow configuration access

        //     if (vue.userSession.user.roles) {

        //         vue.userSession
        //             .user
        //             .roles.forEach(r => {

        //             // Superusers has access to all workspaces

        //             if (r.role_id === vue.superUserRoleId) {
        //                 vue.workspacesAdmin = vue.workspaces;
        //                 vue.canAccessConfiguration = true;
        //             }

        //             // Config users

        //             if (r.role_id === vue.configRoleId) {
        //                 let workspace = vue.workspaces
        //                                    .find(w => w.id === r.scope)

        //                 if (workspace)
        //                     vue.workspacesConfig.push(workspace)
        //             }
        //         })

        //         // If user has admin access to at least
        //         // one workspace, update flag to show
        //         // configuration area

        //         if (vue.workspacesConfig.length > 0)
        //             vue.canAccessConfiguration = true;

        //     }
        // }
        // ,
        /**
         * Check whether user has admin role for specific
         * @param workspaceId
         * @returns {boolean}
         */
        // isAdminInWorkspace (workspaceId) {

        //     let isAdmin = false;
        //     let vue = this;

        //     if (!vue.userSession.user) return isAdmin;

        //     vue.userSession
        //         .user
        //         .roles.forEach(r => {

        //         let workspaceMatch = workspaceId === r.scope;
        //         let isAdminOrSuper = (
        //             r.role_id === vue.adminRoleId ||
        //             r.role_id === vue.superUserRoleId
        //         );

        //         if (workspaceMatch && isAdminOrSuper) {
        //             isAdmin = true;
        //         }
        //     })

        //     return isAdmin;
        // }
        // ,
        /**
         * Show/hide configuration, also when configuration
         * is shown scroll to its position
         */
        // toggleConfiguration () {

        //     // Scroll to configuration's element position,
        //     // only if it is hidden, since it value is
        //     // going to change to visible

        //     if (!this.configurationIsVisible) {

        //         // Calculates configuration's position

        //         let el = this.$refs.configurationTitle;
        //         let position = el.getBoundingClientRect().top +
        //                        document.documentElement.scrollTop;

        //         this.scrollToSmoothly(position,500);
        //     }

        //     // Show configuration

        //     this.configurationIsVisible = true;
        // }
        // ,
        /**
         * Scroll to provided position with animation
         *
         * @param {number} pos position in pixels
         * @param {number} duration animation duration in miliseconds
         */
        // scrollToSmoothly(pos, duration) {

        //     let currentPos = window.pageYOffset;
        //     let start = null;
        //     if (duration == null) duration = 500;
        //     pos = +pos, duration = +duration;

        //     window.requestAnimationFrame(function step(currentTime) {

        //         start = !start ? currentTime : start;
        //         let progress = currentTime - start;

        //         if (currentPos < pos) {
        //             window.scrollTo(0, ((pos - currentPos) * progress / duration) + currentPos);
        //         } else {
        //             window.scrollTo(0, currentPos - ((currentPos - pos) * progress / duration));
        //         }

        //         if (progress < duration) {
        //             window.requestAnimationFrame(step);
        //         } else {
        //             window.scrollTo(0, pos);
        //         }
        //     });
        // }
        // ,
        /**
         * Update workspace in User's session and
         * redirect to welcome page
         *
         * @param workspaceId
         * @param redirect
         */
        setActiveWorkspace(workspaceId, redirect = '/welcome') {

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

.wrapper {
    background: white;
}

.user-button-wrapper button {
    display: flex;
    align-items: center;
    width: 90%;
    padding: 5px 10px 5px 15px;
    border-radius: 6px;
    color: white;
    background: #A5A6F6;
    font-family: 'Roboto', serif;
    font-style: normal;
    font-weight: 700 !important;
    font-size: 16px;
    line-height: 19px;
    margin-bottom: 15px
}

.user-button-wrapper button .icon {
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    margin-right: 15px;
    color: #5D5FEF;
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

/*.configurations-wrapper {*/
/*    background: #F8F8FB;*/
.workspaces-wrapper {
/*    border-radius: 6px;*/
}

/*.workspaces-wrapper{
    padding: 26px 40px 26px 40px;
}*/

/*.configurations-wrapper {
    padding: 56px 50px 56px 50px;
}
*/
.workspace {
   /* padding-right: 8px;
    padding-left: 8px;*/
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
    height: 105px;
/*    border-top-left-radius: 5px;*/
/*    border-top-right-radius: 5px;*/
    background: white;
}

.workspace .logo-wrapper .logo {
    max-height: 65%;
    max-width: 65%;
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

.workspace .button-wrapper {
    background-color: rgba(165, 166, 246, 0.2);
/*    border-bottom-left-radius: 5px;*/
/*    border-bottom-right-radius: 5px;*/
}

.workspace .button-wrapper:before {
    content: '';
    position: absolute;
    width: 90%;
    height: 1px;
    top: 0;
    left: 0;
    right: 0;
    margin: auto;
    border: 1px solid rgba(255,255,255,.8);
    z-index: 1;
}

.workspace .button-wrapper button {
    width: 123px;
    height: 40px;
    background: #5458EA;
    box-shadow: 0 8px 20px rgba(84, 88, 234, 0.1);
    border-radius: 6px;
    font-family: 'Roboto', serif;
    font-style: normal;
    font-weight: 400;
    font-size: 16px;
    color: white;
}

.workspace .stats {
    font-family: 'Roboto', serif;
    font-style: normal;
    line-height: 13px;
    background-color: rgba(165, 166, 246, 0.2);
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

/*.configurations-wrapper h4 {
    font-family: 'Roboto', serif;
    font-style: normal;
    font-weight: 700;
    font-size: 16px !important;
    line-height: 19px;
    color: #28314F !important;
}

.configurations-wrapper .card h5 {
    font-family: 'Roboto', serif;
    font-style: normal;
    font-weight: 500;
    font-size: 14px !important;
    line-height: 16px;
    color: #28314F !important;
}

.configurations-wrapper .card p {
    padding-top: 16px;
    font-family: 'Roboto', serif;
    font-style: normal;
    font-weight: 400;
    font-size: 14px !important;
    line-height: 16px;
    text-align: justify;
    color: #333D5D !important;
}*/

.card {
    background: white;
    border: none;
}

.cursor-pointer {
    cursor: pointer;
}

.table-default-icon-title {
    font-size: 0.85em !important;
    font-weight: 500;
}

/*.workspace-badge .v-badge__badge {
    font-size: 10px !important;
    height: auto !important;
    padding: 4px 6px !important;
}
*/
/*.go-icon {
    position: absolute;
    top: 8px;
    right: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: rgba(165, 166, 246, 0.25);
    width: 26px;
    height: 26px;
    border-radius: 50%;
    cursor: pointer;
    color: #5D5FEF;
}*/
</style>
