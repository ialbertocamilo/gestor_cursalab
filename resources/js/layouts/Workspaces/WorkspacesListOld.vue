<template>
    <div class="wrapper">

        <!--
            Logo
        ======================================== -->

        <v-row class="justify-content-center pt-3 pb-3">
            <div class="col-4">
                <img src="/img/we-connect-logo.png"
                     class="logo"
                     alt="We connect">
            </div>
            <div class="col-6">
                <div v-html="headerTemplate"></div>
                <!--
                <div v-if="userSession.user"
                    class="user-button-wrapper">
                    <button class="mr-3">
                        <v-icon class="icon">mdi-account</v-icon>
                        {{ userSession.user.fullname }}
                    </button>

                    <a @click="logout()">
                        <v-icon class="stats-icon">mdi-logout</v-icon>
                    </a>
                </div>
                -->
            </div>
        </v-row>

        <!--
            Main title
        ======================================== -->

        <v-row class="justify-content-center">
            <div class="col-10">
                <h1>
                    Bienvenido(a) a WeConnect 2.0
                </h1>
                <h3>
                    Ingresa a un workspace para administrar  su contenido
                </h3>
            </div>
        </v-row>

        <!--
            Workspaces title
        ======================================== -->

        <v-row class="justify-content-center mt-3 pt-3 pb-3">
            <div class="col-10">
                <h2>
                    <b>
                        Workspaces
                    </b>
                </h2>
            </div>
        </v-row>

        <!--
            Workspaces
        ======================================== -->

        <v-row class="justify-content-center">
            <v-col cols="10" class="workspaces-wrapper">
                <v-row class="mb-5">
                    <v-col v-for="workspace in workspaces"
                           :key="workspace.id"
                           cols="3" class="workspace">

                        <div class="row">
                            <div class="logo-wrapper col-12 pt-3 pb-3">

                                <!-- Logo -->

                                <img v-bind:src="workspace.logo"
                                     class="logo"
                                     alt="">

                                <!-- Edit button -->

                                <div @click="editWorkspace(workspace.id)"
                                     v-if="isAdminInWorkspace(workspace.id)"
                                     class="edit-button">
                                    <v-icon color="white" size="16px">
                                        mdi-square-edit-outline
                                    </v-icon>
                                </div>
                            </div>
                            <div class="col-12 text-center bg-white">
                                <span>{{ workspace.name }}</span>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-sitemap</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">
                                        {{ workspace.modules_count }}
                                    </span><br>
                                    <span class="label">módulos</span>
                                </div>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-account-group</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">
                                        {{ workspace.users_count }}
                                    </span><br>
                                    <span class="label">usuarios</span>
                                </div>
                            </div>
                            <div class="col-12 pt-3 pb-3 button-wrapper d-flex justify-content-center">
                                <button @click="setActiveWorkspace(workspace.id, true)"
                                        class="btn">
                                    Ingresar
                                </button>
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>

        <!--
            Configurations title
        ======================================== -->

        <v-row v-if="canAccessConfiguration"
               class="justify-content-center mt-3 pt-3">

            <div class="col-10">
                <div class="configurations-button-wrapper"
                >
                    <v-col cols="5"
                            @click="toggleConfiguration()">
                        <span style="cursor: pointer">
                            Configuraciones
                        </span>
                        <v-icon class="stats-icon">mdi-cog</v-icon>

                    </v-col>
                    <v-col v-if="false" cols="7">
                        <DefaultSelect
                            v-model="selectedWorkspaceId"
                            :items="workspacesConfig"
                            label="Workspace"
                            item-text="name"
                            item-value="id"
                            dense
                            @onChange="toggleConfiguration()"
                        />
                    </v-col>
                </div>
            </div>
        </v-row>

        <v-row
            ref="configurationTitle"
            class="justify-content-center mt-3 pt-3 pb-3">
            <div class="col-10"
                 :class="{ 'd-none': !configurationIsVisible }">
                <!-- <h2>
                    <b>
                        Configuración
                    </b>
                </h2> -->

            </div>
        </v-row>

        <!--
            Configurations
        ======================================== -->

        <v-row :class="{ 'd-none': !configurationIsVisible }"
               class="justify-content-center mb-5"
               v-if="canAccessConfiguration">
            <v-col cols="10" class="configurations-wrapper">
                <v-row class="justify-content-center pt-5">
                    <div class="col-3">
                        <h4>General</h4>

                        <div class="card p-3">
                            <h5>
                                <v-icon>mdi-account-cog</v-icon>
                                Criterios

                                <a href="/workspaces/criterios">
                                    <div class="go-icon">
                                        <v-icon color="#5D5FEF">mdi-arrow-top-right-thin</v-icon>
                                    </div>
                                </a>
                            </h5>
                            <p>Los criterios son atributos que comparten los usuarios, permiten realizar segmentación de contenido (anuncios y cursos).</p>
                        </div>
                    </div>
                    <div class="col-3">
                        <h4>Administradores y Roles</h4>

                        <div class="card p-3">
                            <h5>
                                <v-icon>mdi-account-tie</v-icon>
                                Administradores

                                <a href="/users/">
                                    <div class="go-icon">
                                        <v-icon color="#5D5FEF">
                                            mdi-arrow-top-right-thin
                                        </v-icon>
                                    </div>
                                </a>
                            </h5>
                            <p>Los administradores tienen acceso a diferentes secciones del gestor de contenidos según su rol.</p>
                        </div>

                        <div class="card p-3 mt-3">
                            <h5>
                                <v-icon>mdi-notebook</v-icon>

                                Roles

                                <a href="/roles/">
                                    <div class="go-icon">
                                        <v-icon color="#5D5FEF">
                                            mdi-arrow-top-right-thin
                                        </v-icon>
                                    </div>
                                </a>
                            </h5>
                            <p>Los roles permiten organizar y controlar el acceso de los administradores a las distintas secciones del gestor.</p>
                        </div>

                    </div>
                    <div class="col-3">
                        <h4>Atención al Cliente</h4>

                        <div class="card p-3">
                            <h5>
                                <v-icon>mdi-frequently-asked-questions</v-icon>
                                Preguntas Frecuentes

                                <a href="/preguntas-frecuentes">
                                    <div class="go-icon">
                                        <v-icon color="#5D5FEF">
                                            mdi-arrow-top-right-thin
                                        </v-icon>
                                    </div>
                                </a>
                            </h5>
                            <p>Las preguntas frecuentes permiten guiar al usuario en la aplicación web/móvil.</p>
                        </div>
                    </div>
                </v-row>
            </v-col>
        </v-row>

        <!--
            Modals
        ======================================== -->

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

export default {
    props: [ 'header' ],
    components: {
        WorkspacesForm
    },
    data: () => ({
        headerTemplate : ''
        ,
        superUserRoleId : 1
        ,
        configRoleId: 2
        ,
        adminRoleId : 3
        ,
        selectedWorkspaceId: null
        ,
        workspaces: []
        ,
        workspacesConfig: []
        ,
        userSession: {}
        ,
        configurationIsVisible: false
        ,
        canAccessConfiguration: false
        ,
        workspaceFormModalOptions: {
            ref: 'WorkspacesForm',
            open: false,
            action: 'edit',
            base_endpoint: 'workspaces',
            confirmLabel: 'Guardar'
        }
    })
    ,
    mounted() {

        this.loadData();
        this.initializeHeaderTemplate();
    }
    ,
    watch: {
        selectedWorkspaceId () {
            if (this.selectedWorkspaceId > 0) {
                this.setActiveWorkspace(
                    this.selectedWorkspaceId, false
                );
            }
        }
    }
    ,
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
        loadData() {

            console.log('loadData OLD')
            let vue = this;

            let url = `/workspaces/search`

            this.$http
                .get(url)
                .then(({data}) => {
                    vue.workspaces = data.data.data;
                    this.loadSession();
                })
        }
        ,/**
         * Load session data from server
         */
        loadSession() {

            let vue = this;

            // Load session data
            vue.workspacesAdmin = [];
            let url = `/usuarios/session`
            this.$http
                .get(url)
                .then(({data}) => {

                    vue.userSession = data;
                    vue.findConfigWorkspaces();


                });
        }
        ,
        /**
         * Find those workspaces the user has config access to
         */
        findConfigWorkspaces() {

            let vue = this;

            if (!vue.userSession.user) return;

            // When user roles include admin (3)
            // allow configuration access

            if (vue.userSession.user.roles) {

                vue.userSession
                    .user
                    .roles.forEach(r => {

                    // Superusers has access to all workspaces

                    if (r.role_id === vue.superUserRoleId) {
                        vue.workspacesAdmin = vue.workspaces;
                        vue.canAccessConfiguration = true;
                    }

                    // Config users

                    if (r.role_id === vue.configRoleId) {
                        let workspace = vue.workspaces
                                           .find(w => w.id === r.scope)

                        if (workspace)
                            vue.workspacesConfig.push(workspace)
                    }
                })

                // If user has admin access to at least
                // one workspace, update flag to show
                // configuration area

                if (vue.workspacesConfig.length > 0)
                    vue.canAccessConfiguration = true;

            }
        }
        ,
        /**
         * Check whether user has admin role for specific
         * @param workspaceId
         * @returns {boolean}
         */
        isAdminInWorkspace (workspaceId) {

            let isAdmin = false;
            let vue = this;

            if (!vue.userSession.user) return isAdmin;

            vue.userSession
                .user
                .roles.forEach(r => {

                let workspaceMatch = workspaceId === r.scope;
                let isAdminOrSuper = (
                    r.role_id === vue.adminRoleId ||
                    r.role_id === vue.superUserRoleId
                );

                if (workspaceMatch && isAdminOrSuper) {
                    isAdmin = true;
                }
            })

            return isAdmin;
        }
        ,
        /**
         * Show/hide configuration, also when configuration
         * is shown scroll to its position
         */
        toggleConfiguration () {

            // Scroll to configuration's element position,
            // only if it is hidden, since it value is
            // going to change to visible

            if (!this.configurationIsVisible) {

                // Calculates configuration's position

                let el = this.$refs.configurationTitle;
                let position = el.getBoundingClientRect().top +
                               document.documentElement.scrollTop;

                this.scrollToSmoothly(position,500);
            }

            // Show configuration

            this.configurationIsVisible = true;
        }
        ,
        /**
         * Scroll to provided position with animation
         *
         * @param {number} pos position in pixels
         * @param {number} duration animation duration in miliseconds
         */
        scrollToSmoothly(pos, duration) {

            let currentPos = window.pageYOffset;
            let start = null;
            if (duration == null) duration = 500;
            pos = +pos, duration = +duration;

            window.requestAnimationFrame(function step(currentTime) {

                start = !start ? currentTime : start;
                let progress = currentTime - start;

                if (currentPos < pos) {
                    window.scrollTo(0, ((pos - currentPos) * progress / duration) + currentPos);
                } else {
                    window.scrollTo(0, currentPos - ((currentPos - pos) * progress / duration));
                }

                if (progress < duration) {
                    window.requestAnimationFrame(step);
                } else {
                    window.scrollTo(0, pos);
                }
            });
        }
        ,
        /**
         * Update workspace in User's session and
         * redirect to welcome page
         *
         * @param workspaceId
         * @param redirect
         */
        setActiveWorkspace(workspaceId, redirect) {

            let vue = this;

            // Submit request to update workspace in session

            let formData = vue.getMultipartFormData('PUT');
            let url = `/usuarios/session/workspace/${workspaceId}`;
            this.$http
                .post(url, formData)
                .then(() => {

                    // Redirect to welcome page

                    if (redirect) {
                        window.location.href = '/welcome';
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
    max-height: 100px;
    max-width: 200px;
}

.workspaces-wrapper,
.configurations-wrapper {
    background: #F8F8FB;
    border-radius: 6px;
}

.workspaces-wrapper{
    padding: 56px 50px 56px 50px;
}

.configurations-wrapper {
    padding: 56px 50px 56px 50px;
}

.workspace .row {
    padding-right: 8px;
    padding-left: 8px;
}

.workspace .logo-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 105px;
    border-top-left-radius: 5px;
    border-top-right-radius: 5px;
    background: white;
}

.workspace .logo-wrapper .logo {
    max-height: 90%;
    max-width: 90%;
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
    border-bottom-left-radius: 5px;
    border-bottom-right-radius: 5px;
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
    font-size: 16px;
    font-weight: 800;
}

.workspace .stats .label {
    font-size: 13px;
}

.configurations-wrapper h4 {
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
}

.card {
    background: white;
    border: none;
}

.go-icon {
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
}
</style>
