<template>
    <div class="wrapper">

        <!--
            Logo
        ======================================== -->

        <v-row class="justify-content-center pt-3 pb-3">
            <div class="col-6">
                <img src="/img/we-connect-logo.png"
                     class="logo"
                     alt="">
            </div>
            <div class="col-4">

            </div>
        </v-row>

        <!--
            Main title
        ======================================== -->

        <v-row class="justify-content-center">
            <div class="col-8">

                <h1>
                    Bienvenido(a) a WeConnect 2.0
                </h1>
                <h3>
                    Ingresa a un workspace para administrar  su contenido
                </h3>
            </div>
            <div class="col-2">
                <div class="user-button-wrapper">
                    <button class="mr-3">
                        <v-icon class="icon">mdi-account</v-icon>
                        Juan Perez
                    </button>
<!--                    <v-icon class="stats-icon">mdi-logout</v-icon>-->
                </div>

                <div class="configurations-button-wrapper"
                     >
                    <span @click="toggleConfiguration()"
                          style="cursor: pointer">
                        Configuraciones
                    </span>
                    <v-icon class="stats-icon">mdi-cog</v-icon>
                </div>
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
                    <v-col cols="3" class="workspace">
                        <div class="row">
                            <div class="logo-wrapper col-12 pt-3 pb-3">
                                <img src="/img/workspace1.png"
                                     class="logo"
                                     alt="">

                                <div @click="openFormModal(workspaceFormModalOptions, rowData = null, action = null, title = 'Editar workspace')"
                                     class="edit-button">
                                    <v-icon color="white" size="16px">
                                        mdi-square-edit-outline
                                    </v-icon>
                                </div>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-sitemap</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">3</span><br>
                                    <span class="label">módulos</span>
                                </div>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-account-group</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">1350</span><br>
                                    <span class="label">usuarios</span>
                                </div>
                            </div>
                            <div class="col-12 pt-3 pb-3 button-wrapper d-flex justify-content-center">
                                <button class="btn">Ingresar</button>
                            </div>
                        </div>
                    </v-col>
                    <v-col cols="3" class="workspace">
                        <div class="row">
                            <div class="logo-wrapper col-12 pt-3 pb-3">
                                <img src="/img/workspace2.png"
                                     class="logo"
                                     alt="">

                                <div @click="openFormModal(workspaceFormModalOptions, rowData = null, action = null, title = 'Editar workspace')"
                                     class="edit-button">
                                    <v-icon color="white" size="16px">
                                        mdi-square-edit-outline
                                    </v-icon>
                                </div>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-sitemap</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">3</span><br>
                                    <span class="label">módulos</span>
                                </div>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-account-group</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">1350</span><br>
                                    <span class="label">usuarios</span>
                                </div>
                            </div>
                            <div class="col-12 pt-3 pb-3 button-wrapper d-flex justify-content-center">
                                <button class="btn">Ingresar</button>
                            </div>
                        </div>
                    </v-col>
                    <v-col cols="3" class="workspace">
                        <div class="row">
                            <div class="logo-wrapper col-12 pt-3 pb-3">
                                <img src="/img/workspace3.png"
                                     class="logo"
                                     alt="">

                                <div @click="openFormModal(workspaceFormModalOptions, rowData = null, action = null, title = 'Editar workspace')"
                                     class="edit-button">
                                    <v-icon color="white" size="16px">
                                        mdi-square-edit-outline
                                    </v-icon>
                                </div>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-sitemap</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">3</span><br>
                                    <span class="label">módulos</span>
                                </div>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-account-group</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">1350</span><br>
                                    <span class="label">usuarios</span>
                                </div>
                            </div>
                            <div class="col-12 pt-3 pb-3 button-wrapper d-flex justify-content-center">
                                <button class="btn">Ingresar</button>
                            </div>
                        </div>
                    </v-col>
                    <v-col cols="3" class="workspace">
                        <div class="row">
                            <div class="logo-wrapper col-12 pt-3 pb-3">
                                <img src="/img/workspace4.png"
                                     class="logo"
                                     alt="">

                                <div @click="openFormModal(workspaceFormModalOptions, rowData = null, action = null, title = 'Editar workspace')"
                                     class="edit-button">
                                    <v-icon color="white" size="16px">
                                        mdi-square-edit-outline
                                    </v-icon>
                                </div>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-sitemap</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">3</span><br>
                                    <span class="label">módulos</span>
                                </div>
                            </div>
                            <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                <v-icon class="icon" size="30px">mdi-account-group</v-icon>
                                <div class="text-left ml-2">
                                    <span class="number">1350</span><br>
                                    <span class="label">usuarios</span>
                                </div>
                            </div>
                            <div class="col-12 pt-3 pb-3 button-wrapper d-flex justify-content-center">
                                <button class="btn">Ingresar</button>
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>

        <!--
            Configurations title
        ======================================== -->

        <v-row
            ref="configurationTitle"
            class="justify-content-center mt-3 pt-3 pb-3">
            <div class="col-10"
                 :class="{ 'd-none': !configurationIsVisible }">
                <h2>
                    <b>
                        Configuración
                    </b>
                </h2>
            </div>
        </v-row>

        <!--
            Configurations
        ======================================== -->

        <v-row :class="{ 'd-none': !configurationIsVisible }"
               class="justify-content-center mb-5">
            <v-col cols="10" class="configurations-wrapper">
                <v-row class="justify-content-center pt-5">
                    <div class="col-3">
                        <h4>Usuarios</h4>
                        <div class="card p-3">
                            <h5>
                                <v-icon>mdi-account-cog</v-icon>
                                Criterios

                                <div class="go-icon">
                                    <v-icon color="#5D5FEF">mdi-arrow-top-right-thin</v-icon>
                                </div>
                            </h5>
                            <p>
                                Configura datos generales del ambiente y de branding como el
                                logotipo, colores, etc.
                            </p>
                        </div>

                        <div class="card p-3 mt-3">
                            <h5>
                                <v-icon>mdi-account-group</v-icon>
                                Usuarios

                                <div class="go-icon">
                                    <v-icon color="#5D5FEF">mdi-arrow-top-right-thin</v-icon>
                                </div>
                            </h5>
                            <p>
                                Configura datos generales del ambiente y de branding como el
                                logotipo, colores, etc.
                            </p>
                        </div>
                    </div>
                    <div class="col-3">
                        <h4>Roles y permisos</h4>
                        <div class="card p-3">
                            <h5>
                                <v-icon>mdi-notebook</v-icon>
                                Roles

                                <div class="go-icon">
                                    <v-icon color="#5D5FEF">mdi-arrow-top-right-thin</v-icon>
                                </div>
                            </h5>
                            <p>
                                Configura datos generales del ambiente y de branding como el
                                logotipo, colores, etc.
                            </p>
                        </div>

                        <div class="card p-3 mt-3">
                            <h5>
                                <v-icon>mdi-key-variant</v-icon>
                                Permisos

                                <div class="go-icon">
                                    <v-icon color="#5D5FEF">mdi-arrow-top-right-thin</v-icon>
                                </div>
                            </h5>
                            <p>
                                Configura datos generales del ambiente y de branding como el
                                logotipo, colores, etc.
                            </p>
                        </div>

                        <div class="card p-3 mt-3">
                            <h5>
                                <v-icon>mdi-account-tie</v-icon>
                                Administradores

                                <div class="go-icon">
                                    <v-icon color="#5D5FEF">mdi-arrow-top-right-thin</v-icon>
                                </div>
                            </h5>
                            <p>
                                Configura datos generales del ambiente y de branding como el
                                logotipo, colores, etc.
                            </p>
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
            @onConfirm=""
        />

    </div>
</template>

<script>

import WorkspacesForm from "./WorkspacesForm";

export default {
    components: {
        WorkspacesForm
    },
    data: () => ({
        configurationIsVisible: false
        ,
        workspaceFormModalOptions: {
            ref: 'WorkspacesForm',
            open: false,
            confirmLabel: 'Guardar'
        }
    })
    ,
    mounted() {

    }
    ,
    methods: {
        /**
         * Show/hide configuration, also when configuration
         * is shown scroll to its position
         */
        toggleConfiguration () {

            // Scroll to configuration's element position,
            // only if it is hidden, since it value is
            // gonna change  to visible

            if (!this.configurationIsVisible) {

                // Calculates configuration's position

                let el = this.$refs.configurationTitle;
                let position = el.getBoundingClientRect().top +
                               document.documentElement.scrollTop;

                this.scrollToSmoothly(position,500);
            }

            // Toggle flag to hide configuration

            this.configurationIsVisible = !this.configurationIsVisible;
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
    padding: 5px 0 5px 15px;
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

.workspace .stats .number,
.workspace .stats .label
{
    opacity: 0;
    transition: opacity 800ms;
}

.workspace:hover .stats .number,
.workspace:hover .stats .label
{
    opacity: 1;
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
