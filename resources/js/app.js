require("./bootstrap");

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

Vue.use(VueNotification, {
    timer: 20,
    success: {
        background: "#d4edda",
        color: "#0f5132"
    }
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

// Gestor views
Vue.component("blocks-layout", require("./layouts/Blocks/BlockListPage"));
Vue.component("blocks-form-data-layout", require("./layouts/Blocks/BlockFormDataPage"));
Vue.component("blocks-form-courses-layout", require("./layouts/Blocks/BlockFormCoursesPage"));
Vue.component("tag-layout", require("./layouts/Tags/TagListPage"));
Vue.component("error-layout", require("./layouts/Errores/ErrorListPage"));
Vue.component("usuario-layout", require("./layouts/Usuario/UsuarioListPage"));
Vue.component("modulo-layout", require("./layouts/Modulos/ModuloListPage"));
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

const app = new Vue({
    vuetify,
    store,
    el: "#app"
});
