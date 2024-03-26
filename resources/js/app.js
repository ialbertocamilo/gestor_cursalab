require("./bootstrap");
const FileSaver = require("file-saver");

window.Vue = require("vue");
import vuetify from "./plugins/vuetify";
import mixin from "./mixin";
import snackbar from "./mixins/snackbar";
import VueNotification from "@kugatsu/vuenotification";


import store from "./store";
// MIXIN
Vue.mixin(mixin);
Vue.mixin(snackbar);

import { BootstrapVue, IconsPlugin } from "bootstrap-vue";
import ApexCharts from "apexcharts";
import Toast from "vue-toastification";
import "vue-toastification/dist/index.css";
import VueApexCharts from "vue-apexcharts";
import globalComponents from "./global-components";
import common_http_request from "./mixins/common_http_request";
import commons from "./mixins/commons";
import reports from "./mixins/reports";
import axiosDefault from "./plugins/axios";

import Permissions from "./mixins/Permissions";
Vue.mixin(Permissions);

Vue.use(VueApexCharts);
Vue.component("apexcharts", VueApexCharts);
Vue.mixin(globalComponents);
Vue.mixin(common_http_request);
Vue.mixin(commons);
Vue.mixin(reports);

import custom from "./custom";
Vue.mixin(custom);

Vue.use(VueNotification, {
    timer: 20,
    success: {
        background: "#d4edda",
        color: "#0f5132"
    }
});
import * as VueGoogleMaps from 'vue2-google-maps'
// test: AIzaSyAutlRxqIv1gr3J3RcPdKOfprDRluJdmNg
// prod: AIzaSyBnS1vTLtRnpAH0H64msTG-I4OeEZcVNxE
Vue.use(VueGoogleMaps , {
    load: {
        key: 'AIzaSyBnS1vTLtRnpAH0H64msTG-I4OeEZcVNxE',
        libraries: 'places',
    },
})

Vue.use(Toast, {
    position: "bottom-right",
    transition: "Vue-Toastification__slideBlurred",
    timeout: 10000,
    closeOnClick: false,
    pauseOnFocusLoss: true,
    pauseOnHover: false,
    draggable: true,
    draggablePercent: 0.6,
    showCloseButtonOnHover: false,
    hideProgressBar: false,
    closeButton: "button",
    icon: false,
    newestOnTop: true
});
// Vue.use(axiosDefault)
Vue.prototype.$http = axiosDefault;

// Install BootstrapVue
Vue.use(BootstrapVue);
// Optionally install the BootstrapVue icon components plugin
Vue.use(IconsPlugin);
// import 'bootstrap/dist/css/bootstrap.css'
import "bootstrap-vue/dist/bootstrap-vue.css";

Vue.component("tags-form", require("./components/forms/Tags.vue"));
Vue.component("curricula-form", require("./components/forms/Curricula.vue"));

Vue.component("reportes-layout", require("./layouts/ReportesLayout.vue"));
Vue.component("reportes-general-layout", require("./layouts/Reportes/ReporteGeneralList.vue"));

Vue.component("reportes-conf-layout", require("./layouts/ReportesConferencias.vue"));
Vue.component("encuestas-layout", require("./layouts/EncuestasLayout.vue"));

Vue.component("aulas-virtuales", require("./layouts/AulasVirtuales.vue"));
Vue.component("curricula-grupos", require("./layouts/CurriculaGrupos.vue"));

Vue.component("matricula-usuario", require("./layouts/MatriculaUsuario.vue"));
Vue.component("modal-cursos-usuario", require("./components/Usuarios/ModalCursosUsuario.vue"));

Vue.component("criterios-layout", require("./layouts/Criterios.vue"));

Vue.component("boticas-layout", require("./layouts/Boticas.vue"));

Vue.component("cargos-layout", require("./layouts/Cargos.vue"));

Vue.component("subida-masiva", require("./layouts/SubidaMasiva.vue"));

Vue.component("convalidaciones", require("./layouts/Convalidaciones.vue"));
// Vue.component("cuentas-zoom-layout", require("./layouts/CuentasZoom.vue"));
Vue.component("cuentas-zoom-layout", require("./layouts/CuentasZoom/CuentaZoomListPage.vue"));
Vue.component("notificaciones-push", require("./layouts/NotificacionesPush.vue"));
Vue.component("evaluacion-form", require("./components/Evaluaciones/Evaluacion.vue"));
Vue.component("posteos-compatibles", require("./layouts/Compatibles.vue"));

Vue.component("entrenadores-layout", require("./layouts/Entrenadores/EntrenadoresListPage.vue"));
// Vue.component("entrenadores-layout", require("./layouts/Entrenadores/EntrenadoresLayout3.vue"));
Vue.component("checklist-layout", require("./layouts/Checklist/ChecklistList.vue"));
// Vue.component("checklist-layout", require("./layouts/Checklist/ChecklistLayout.vue"));
Vue.component("evaluacion-form", require("./components/Evaluaciones/Evaluacion"));

Vue.component("reportes-supervisores-layout", require("./layouts/ReportesSupervisoresLayout.vue"));

Vue.component("duplicar-escuelas", require("./components/Duplicar/DuplicarEscuelas.vue"));
Vue.component("duplicar-cursos", require("./components/Duplicar/DuplicarCursos.vue"));
Vue.component("auditoria-layout", require("./layouts/Auditoria/AuditoriaLayout.vue"));
Vue.component("videoteca-layout", require("./layouts/VideotecaLayout.vue"));

Vue.component("usuario-ayuda", require("./layouts/UsuarioAyuda.vue"));
Vue.component("soporte-layout", require("./layouts/Soporte/SoporteListPage.vue"));
Vue.component("soporte-ayuda-layout", require("./layouts/Soporte/Ayudas/AyudaListPage.vue"));

/* === diplomas ===*/
Vue.component("diploma-layout", require("./layouts/Diplomas/DiplomasListPage"));
Vue.component("diploma-form-page", require("./layouts/Diplomas/DiplomaFormPage"));
/* === diplomas ===*/

// Gestor views
Vue.component("blocks-layout", require("./layouts/Blocks/BlockListPage"));
Vue.component("blocks-form-data-layout", require("./layouts/Blocks/BlockFormDataPage"));
Vue.component("blocks-form-courses-layout", require("./layouts/Blocks/BlockFormCoursesPage"));
Vue.component("tag-layout", require("./layouts/Tags/TagListPage"));
Vue.component("error-layout", require("./layouts/Errores/ErrorListPage"));
Vue.component("usuario-layout", require("./layouts/Usuario/UsuarioListPage"));
Vue.component("user-layout", require("./layouts/Users/UserListPage"));
Vue.component("modulo-layout", require("./layouts/Modulos/ModuloListPage"));
Vue.component("role-layout", require("./layouts/Roles/RoleListPage"));
Vue.component("escuela-layout", require("./layouts/Escuelas/EscuelaListPage"));
Vue.component("escuela-form-page", require("./layouts/Escuelas/EscuelaFormPage"));
Vue.component("curso-layout", require("./layouts/Cursos/CursosListPage"));
Vue.component("segmentation-layout", require("./layouts/Cursos/SegmentationListPage"));
Vue.component("curso-form-page", require("./layouts/Cursos/CursoFormPage"));
Vue.component("tema-layout", require("./layouts/Temas/TemaListPage"));
Vue.component("tema-preguntas-layout", require("./layouts/Temas/TemaPreguntasListPage"));
Vue.component("tema-form-page", require("./layouts/Temas/TemasFormPage"));
Vue.component("anuncio-layout", require("./layouts/Anuncios/AnuncioListPage"));
Vue.component("cargo-layout", require("./layouts/Cargos/CargoListPage"));
Vue.component("criterion-layout", require("./layouts/Criteria/CriterionListPage"));
Vue.component("criterion-layout-wk", require("./layouts/Criteria/CriterionListPageWk"));
Vue.component("criterion-value-layout", require("./layouts/Criteria/CriterionValues/CriterionValueListPage"));
Vue.component("encuesta-layout", require("./layouts/Encuestas/EncuestaListPage"));
Vue.component("incidencia-layout", require("./layouts/Incidencias/IncidenciaListPage"));
Vue.component("ambiente-layout", require("./layouts/Ambiente/AmbientePage"));
Vue.component(
    "encuesta-pregunta-layout",
    require("./layouts/Encuestas/Preguntas/PreguntaListPage")
);
Vue.component("glosario-layout", require("./layouts/Glosario/GlosarioListPage"));
Vue.component("vademecum-layout", require("./layouts/Vademecum/VademecumListPage"));
Vue.component(
    "vademecum-categoria-layout",
    require("./layouts/Vademecum/Categorias/CategoriaListPage")
);
Vue.component(
    "vademecum-subcategoria-layout",
    require("./layouts/Vademecum/Categorias/Subcategorias/SubcategoriaListPage")
);
Vue.component("botica-layout", require("./layouts/Boticas/BoticaListPage"));
Vue.component("ayuda-layout", require("./layouts/Ayudas/AyudaListPage"));
Vue.component(
    "pregunta-frecuente-layout",
    require("./layouts/PreguntasFrecuentes/PreguntaFrecuenteListPage")
);

Vue.component("aulas-virtuales-layout", require("./layouts/AulasVirtuales/AulasVirtualesListPage"));
Vue.component("aulas-virtuales-create", require("./layouts/AulasVirtuales/AulaVirtualFormPage"));

Vue.component(
    "notificaciones-push-layout",
    require("./layouts/NotificacionesPush/NotificacionPushListPage")
);
Vue.component("multimedia-layout", require("./layouts/Multimedia/MultimediaListPage"));
Vue.component("supervisores-layout", require("./layouts/Supervisores/SupervisoresListPage"));

Vue.component("upload-topic-grades-layout", require("./layouts/Masivos/UploadTopicGrades.vue"));

Vue.component("homeview", require("./layouts/General/DashboardView"));

Vue.component("resumen-encuesta", require("./components/Encuestas/PollReportLayout.vue"));
Vue.component("resumen-evaluaciones", require("./components/Evaluaciones/EvaluationReportLayout.vue"));

// Workspaces

Vue.component('workspaces-list-layout', require('./layouts/Workspaces/WorkspacesList'));

// Side Menu
Vue.component("side-menu", require("./layouts/SideMenu"));
Vue.component(
    "learning-analytics-embed",
    require("./layouts/LearningAnalytics/LearningAnalyticsEmbed")
);

// ==============================================================================

Vue.component("migrar-avance", require("./layouts/MigrarAvance.vue"));
Vue.component("reinicios-masivos", require("./layouts/Masivos/ReiniciosMasivos.vue"));

Vue.component("subida-masiva-layout", require("./layouts/SubidaMasiva/MasivoLayout.vue"));

Vue.component("meetings-layout", require("./layouts/Meetings/MeetingsListPage.vue"));
Vue.component("accounts-layout", require("./layouts/Accounts/AccountListPage.vue"));

Vue.component("workspace-rol", require("./components/forms/WorkspaceRol.vue"));

Vue.component("benefit-layout", require("./layouts/Benefits/BenefitsList.vue"));
Vue.component("benefit-form-page", require("./layouts/Benefits/BenefitFormPage"));

Vue.component("speaker-layout", require("./layouts/Speakers/SpeakersList.vue"));
Vue.component("speaker-form-page", require("./layouts/Speakers/SpeakerFormPage"));
/*---Project list views--*/
Vue.component("project-layout", require("./layouts/Project/ProjectList.vue"));
Vue.component("project-users-layout", require("./layouts/Project/ProjectUsersList.vue"));
/*=== votaciones ===*/
Vue.component("votacion-layout", require("./layouts/Votaciones/VotacionesListPage.vue"));
Vue.component("votacion-form-page", require("./layouts/Votaciones/VotacionesFormPage.vue"));
Vue.component("votacion-detail-page", require("./layouts/Votaciones/VotacionesListDetailPage.vue"));
/*=== votaciones ===*/

Vue.component("guest-layout", require("./layouts/Guest/GuestListPage.vue"));

// Inducción
Vue.component("onboarding-dashboard-layout", require("./layouts/Onboarding/Dashboard/DashboardList.vue"));
Vue.component("processes-layout", require("./layouts/Processes/ProcessesList.vue"));
Vue.component("processes-assistants-layout", require("./layouts/Processes/Assistants/AssistantsList.vue"));
Vue.component("stages-layout", require("./layouts/Stages/StagesList.vue"));



/*----*/
const app = new Vue({
    vuetify,
    store,
    el: "#app",
    data: {
        adminId: 0,
        isSuperUser: false,
        superUserRoleId : 1,
        workspaceId: 0
    },
    mounted() {

        // Since content is ready,  destroy skeletons

        let skeleton1 = document.querySelector('.sidemenu-container .skeleton-wrapper');
        let skeleton2 = document.querySelector('.dashboard.skeleton-wrapper');
        let skeleton3 =  document.querySelector('.table-gui.skeleton-wrapper');

        if (skeleton1) skeleton1.remove();
        if (skeleton2) skeleton2.remove();
        if (skeleton3) skeleton3.remove();


        this.fetchSessionData()
        this.listenReportsNotifications()
    },
    methods: {
        listenReportsNotifications() {
            const vue = this
            let socket = window.io(this.getReportsBaseUrl());
            socket.on('report-finished', (e) => {

                if (vue.adminId === e.adminId || vue.isSuperUser) {
                    vue.notifyReportHasFinished(e)
                }
            })

            socket.on('report-started', (e) => {

                if (vue.adminId === e.adminId || vue.isSuperUser) {
                    vue.notifyReportHasStarted(e.report)
                }
            })
        }
        ,
        notifyReportHasFinished (e) {

            const vue = this

            // Notify user that report is ready to donwload

            if (e.success) {

                this.$toast.warning({
                    component: Vue.component('comp', {
                        template: `
                                    <div>${e.message} <a href="javascript:"
                                                         @click.stop="clicked">Descargar</a>
                                    </div>`,
                        methods: {
                            clicked() { this.$emit('download') }
                        }
                    }),
                    listeners: {
                        download: () => this.downloadReport(e.url, `${e.name}.${e.ext ? e.ext : 'xlsx'}`)
                    }
                });
            } else {

                // Notify user that report has no results

                this.$toast.error({
                    component: Vue.component('comp', {
                        template: `
                                    <div>${e.message} <a href="javascript:"
                                                         @click.stop="clicked">Revisalo aquí</a>
                                    </div>`,
                        methods: {
                            clicked() { this.$emit('redirect') }
                        }
                    }),
                    listeners: {
                        redirect: () => { window.location.href = '/exportar/node' }
                    }
                });
            }
        }
        ,
        notifyReportHasStarted (report) {

            const message = `Tu reporte "${report.name}" se encuentra en proceso.`
            this.$toast.warning(message)
        }
        ,
        async fetchSessionData() {
            let vue = this;

            // Fetch current session workspace

            let url = `/usuarios/session`
            let response = await axios({
                url: url,
                method: 'get'
            })
            vue.adminId = response.data.user.id
            vue.workspaceId = response.data.session.workspace.id

            if (response.data.user) {
                response.data
                    .user
                    .roles.forEach(r => {
                        if (r.role_id === vue.superUserRoleId) {
                            vue.isSuperUser = true;
                        }
                    })
            }

        },
        downloadReport(url, name) {
            try {
                // Realizar una solicitud para obtener el archivo desde la URL
                fetch(url)
                    .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.blob();
                        }
                    )
                    .then(blob => {
                        // Crear un nuevo Blob con el nombre deseado
                        const newBlob = new Blob([blob], { type: blob.type });

                        // Guardar el Blob con el nuevo nombre usando FileSaver.js
                        FileSaver.saveAs(newBlob, name );
                    })
                    .catch(error => {
                        console.error("Error al descargar el archivo:", error);
                    });
            } catch (error) {
                console.error("Error general:", error);
            }
        },
    }
});
