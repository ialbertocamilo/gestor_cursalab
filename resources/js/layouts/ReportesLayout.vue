<template>
    <v-app class="--section-list">

<!--        <alert-->
<!--            background='#F8F8FB'-->
<!--            border='#C1C1FF'-->
<!--            icon='mdi-information-outline'-->
<!--            text="Tu reporte esta siendo procesado."-->
<!--            class="mb-6"-->
<!--        />-->

        <v-row style="flex: 0 1 auto">
            <v-col cols="12" class="main-tabs-wrapper">
                <button
                    @click="activeTab = 'history'"
                    :class="{ active: activeTab === 'history' }"
                    type="button">
                    <v-icon :color="activeTab === 'history' ? 'white' : '#5457E7'">
                        mdi-folder-file-outline
                    </v-icon>
                    Reportes
                </button>
                <button
                    @click="activeTab = 'new-report'"
                    :class="{ active: activeTab === 'new-report' }"
                    type="button">
                    <v-icon :color="activeTab === 'new-report' ? 'white' : '#5457E7'">
                        mdi-file-chart
                    </v-icon>
                    Nuevo reporte
                </button>
            </v-col>
        </v-row>

        <v-card v-if="activeTab === 'history'" flat class="elevation-0 --mb-4">

            <ReportsHistory />

        </v-card>
        <v-card v-if="activeTab === 'new-report'" flat class="elevation-0 --mb-4">
            <v-tabs vertical class="reports-menu">

<!--

REPORTS TABS

============================================================================ -->


                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-account</v-icon>
                    <span class="pt-2">
                        Notas de usuario
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-account-multiple</v-icon>
                    <span class="pt-2">
                        Usuarios
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                   <v-icon left>mdi-book-open-page-variant-outline</v-icon>
                   <span class="pt-2">
                       Avance de currícula
                   </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-certificate</v-icon>
                    <span class="pt-2">
                        Diplomas
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-access-point</v-icon>
                    <span class="pt-2">
                        Visitas
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-book-outline</v-icon>
                    <span class="pt-2">
                        Notas por tema
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-book-outline</v-icon>
                    <span class="pt-2">
                        Temas no evaluables
                    </span>
                </v-tab>

               <v-tab class="justify-content-start py-7">
                   <v-icon left>mdi-book-open-page-variant-outline</v-icon>
                   <span class="pt-2">
                       Notas por curso
                   </span>
               </v-tab>

               <v-tab class="justify-content-start py-7">
                   <v-icon left>fa fa-square</v-icon>
                   <span class="pt-2">
                       Segmentación
                   </span>
               </v-tab>
                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-book-outline</v-icon>
                    <span class="pt-2">
                        Evaluaciones abiertas
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-restart</v-icon>
                    <span class="pt-2">
                        Reinicios
                    </span>
                </v-tab>
<!--
                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-access-point</v-icon>
                    <span class="pt-2">
                        Versiones usadas
                    </span>
                </v-tab>
-->
                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-file-account-outline</v-icon>
                    <span class="pt-2">
                        Usuario Uploads
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-access-point</v-icon>
                    <span class="pt-2">
                        Vademecum
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-play-box-outline</v-icon>
                    <span class="pt-2">
                        Videoteca
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-playlist-check</v-icon>
                    <span class="pt-2">
                        Checklist Detallado
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-playlist-check</v-icon>
                    <span class="pt-2">
                        Checklist General
                    </span>
                </v-tab>


                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-numeric</v-icon>
                    <span class="pt-2">
                        Ranking
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-monitor-account</v-icon>
                    <span class="pt-2">
                        Reuniones
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7">
                    <v-icon left>mdi-account</v-icon>
                    <span class="pt-2">
                        Historial de usuario
                    </span>
                </v-tab>

<!--

TABS CONTENT

============================================================================ -->

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <NotasUsuario
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :reportsBaseUrl="reportsBaseUrl"
                                :API_REPORTES="API_REPORTES"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <Usuarios :workspaceId="workspaceId"
                                      :adminId="adminId"
                                      :modules="modules"
                                      :reportsBaseUrl="reportsBaseUrl"
                                      @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

               <v-tab-item>
                   <v-card flat>
                       <v-card-text>
                           <AvanceCurricula
                               :workspaceId="workspaceId"
                               :adminId="adminId"
                               :modules="modules"
                               :reportsBaseUrl="reportsBaseUrl"
                               @emitir-reporte="crearReporte"/>
                       </v-card-text>
                   </v-card>
               </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <Diploma
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @emitir-reporte="crearReporte" />
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <Visitas
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <NotasTema :workspaceId="workspaceId"
                                       :adminId="adminId"
                                       :modules="modules"
                                       :reportsBaseUrl="reportsBaseUrl"
                                       @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <TemasNoEvaluables
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

               <v-tab-item>
                   <v-card flat>
                       <v-card-text>
                           <NotasCurso
                               :workspaceId="workspaceId"
                               :adminId="adminId"
                               :modules="modules"
                               :reportsBaseUrl="reportsBaseUrl"
                               @emitir-reporte="crearReporte"/>
                       </v-card-text>
                   </v-card>
               </v-tab-item>

               <v-tab-item>
                   <v-card flat>
                       <v-card-text>
                           <Segmentacion
                               :workspaceId="workspaceId"
                               :adminId="adminId"
                               :modules="modules"
                               :reportsBaseUrl="reportsBaseUrl"
                               @emitir-reporte="crearReporte"/>
                       </v-card-text>
                   </v-card>
               </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <EvaAbiertas
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <Renicios :workspaceId="workspaceId"
                                      :adminId="adminId"
                                      :admins="admins"
                                      :reportsBaseUrl="reportsBaseUrl"
                                      @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
<!--
                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <VersionesUsadas :API_FILTROS="API_FILTROS" :API_REPORTES="API_REPORTES"
                                             @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
-->
                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <UsuarioUploads
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :reportsBaseUrl="reportsBaseUrl"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <Vademecum :workspaceId="workspaceId"
                                       :adminId="adminId"
                                       :vademecumList="VademecumList"
                                       :reportsBaseUrl="reportsBaseUrl"
                                       @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <Videoteca :workspaceId="workspaceId"
                                       :adminId="adminId"
                                       :reportsBaseUrl="reportsBaseUrl"
                                       @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <ChecklistDetallado
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <ChecklistGeneral
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <Ranking
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <Meetings
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item>
                    <v-card flat>
                        <v-card-text>
                            <HistorialUsuario
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :reportsBaseUrl="reportsBaseUrl"
                                :API_REPORTES="API_REPORTES"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

            </v-tabs>
        </v-card>

        <!--
        Report's filename dialog
        ======================================== -->

        <v-dialog
            v-model="filenameDialog"
            width="600"
        >
            <v-card class="p-4">
                <v-text-field
                    v-model="reportFilename"
                    label="Nombre de archivo del reporte"
                    required
                ></v-text-field>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <b-button
                        variant="primary"
                        block
                        @click="filenameDialog = false">
                        Cancelar
                    </b-button>
                    <b-button
                        variant="primary"
                        block
                        class="m-0 ml-4"
                        @click="downloadReport()">
                        Descargar
                    </b-button>
                </v-card-actions>

            </v-card>
        </v-dialog>

    </v-app>

</template>


<script>

import Alert from "../documentation_page/components/alert.vue";

const FileSaver = require("file-saver");
const moment = require("moment");
moment.locale("es");

import {mapState} from "vuex";
import NotasTema from "../components/Reportes/NotasTema";
import Diploma from "../components/Reportes/Diploma";
import NotasUsuario from "../components/Reportes/NotasUsuario";
import HistorialUsuario from "../components/Reportes/HistorialUsuario";
import Usuarios from "../components/Reportes/Usuarios";
import Visitas from "../components/Reportes/Visitas";
import NotasCurso from "../components/Reportes/NotasCurso";
import EvaAbiertas from "../components/Reportes/EvaAbiertas";
import Renicios from "../components/Reportes/Renicios";
import VersionesUsadas from "../components/Reportes/VersionesUsadas";
import UsuarioUploads from "../components/Reportes/UsuarioUploads";
import AvanceCurricula from "../components/Reportes/AvanceCurricula";
import Vademecum from "../components/Reportes/Vademecum.vue";
import Videoteca from "../components/Reportes/Videoteca.vue";
import TemasNoEvaluables from "../components/Reportes/TemasNoEvaluables.vue";
import ChecklistDetallado from "../components/Reportes/ChecklistDetallado.vue";
import ChecklistGeneral from "../components/Reportes/ChecklistGeneral.vue";
import Ranking from "../components/Reportes/Ranking.vue";
import Meetings from "../components/Reportes/Meetings";
import Segmentacion from '../components/Reportes/Segmentacion.vue';
import ReportsHistory from "../components/Reportes/ReportsHistory.vue";

export default {
    components: {
        ReportsHistory,
        Alert,
        HistorialUsuario,
        NotasUsuario,
        Usuarios,
        Visitas,
        NotasCurso,
        EvaAbiertas,
        Renicios,
        NotasTema,
        VersionesUsadas,
        UsuarioUploads,
        AvanceCurricula,
        Vademecum,
        Videoteca,
        TemasNoEvaluables,
        ChecklistDetallado,
        ChecklistGeneral,
        Ranking,
        Meetings,
        Diploma,
        Segmentacion
    },
    data() {
        return {

            workspaceId: 0,
            adminId: 0,
            modules: [],
            admins: [],
            reportsBaseUrl: '',

            value: "",
            Modulos: [],

            VademecumList: [],
            VideotecaList: [],
            // URL DE LAS APIS
            API_FILTROS: process.env.MIX_API_FILTROS,
            API_REPORTES: process.env.MIX_API_REPORTES,
            userSession:{},
            superUserRoleId : 1,
            configRoleId: 2,
            adminRoleId : 3,

            activeTab: 'history',

            selectedFilters: {},
            filenameDialog: false,
            reportDownloadUrl: null,
            reportFilename: null
        }
    },
    mounted () {
        this.reportsBaseUrl = this.getReportsBaseUrl()
        this.fetchData();

        let socket = window.io('http://localhost:3000');
        socket.on('report-finished', (e) => {
            console.log(e)
            alert(e.message)
        });

    }
    ,
    methods: {
        isAdmin () {
            let isAdmin = false;
            let vue = this;
            if (!vue.userSession.user) return isAdmin;
            vue.userSession
                .user
                .roles.forEach(r => {
                let isAdminOrSuper = (
                    r.role_id === vue.superUserRoleId
                );
                if (isAdminOrSuper) {
                    isAdmin = true;
                }
            })

            return isAdmin;
        },
        async fetchData() {
            let vue = this;
            // Fetch current session workspace

            let url = `../usuarios/session`
            let response = await axios({
                url: url,
                method: 'get'
            })
            vue.userSession = response.data;
            this.adminId = response.data.user.id
            this.workspaceId = response.data.session.workspace.id

            // Fetch modules and admins

            let url2 = `${this.reportsBaseUrl}/filtros/datosiniciales/${this.workspaceId}`

            let response2 = await axios({
                url: url2,
                method: 'get'
            })

            this.modules = response2.data.modules
            this.admins = response2.data.admins
            this.VademecumList = response2.data.vademecums

            // console.log(response2.data);
        },
        async crearReporte(res) {

            if (!res.data.ruta_descarga) return

            // Update report name and show filename dialog

            this.reportDownloadUrl = res.data.excludeBaseUrl
                ? res.data.ruta_descarga
                : `${this.reportsBaseUrl}/${res.data.ruta_descarga}`

            this.selectedFilters = res.data.selectedFilters;
            this.reportFilename = res.data.new_name;
            this.filenameDialog = true;

        },
        async downloadReport() {
            this.showLoader()

            try {
                FileSaver.saveAs(
                    this.reportDownloadUrl,
                    this.reportFilename
                )
                this.filenameDialog = false;
                this.hideLoader()

            } catch (error) {
                console.log(error);

                this.hideLoader()
            }
        }
    },
    computed: {
        ...mapState(["User"])
    },
    async beforeCreate() {
        // var res = await axios("/exportar/obtenerdatos");
        // this.Modulos = res.data.modulos;
        // this.Admins = res.data.users;
        // this.$store.commit("setUser", res.data.user[0]);
    }
};
</script>

<style>

.main-tabs-wrapper  button.active {
    background: green;
    color: white;
}

input[type="date"]::-webkit-calendar-picker-indicator {
    -webkit-appearance: none;
    display: none;
}

input[type="date"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    display: none;
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    /* display: none; <- Crashes Chrome on hover */
    -webkit-appearance: none;
    margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
}

input[type="number"] {
    -moz-appearance: textfield; /* Firefox */
}

label {
    margin: 0;
}

/* button {
color: #ffffff !important;
} */
.max-w-1920 {
    max-width: 1920px;
}

.v-label {
    /*display: contents !important;*/
}

.info-icon {
    color: darkgrey !important;
    font-size: 20px !important;
}


/* Tooltips */
[tooltip] {
    /* margin: 20px; */
    position: relative;
}

[tooltip]::before {
    content: "";
    position: absolute;
    top: -6px;
    left: 50%;
    transform: translateX(-50%);
    border-width: 4px 6px 0 6px;
    border-style: solid;
    border-color: rgba(0, 0, 0, 0.7) transparent transparent transparent;
    z-index: 100;
    opacity: 0;
}

[tooltip]::after {
    content: attr(tooltip);
    position: absolute;
    left: 50%;
    top: -6px;
    transform: translateX(-50%) translateY(-100%);
    background: rgba(0, 0, 0, 0.7);
    text-align: left;
    color: #fff;
    padding: 6px 4px;
    font-size: 14px;
    font-family: Roboto, sans-serif;
    font-weight: 500;
    min-width: 150px;
    width: 250px;
    max-width: 250px;
    border-radius: 5px;
    pointer-events: none;
    opacity: 0;
}

[tooltip-position="left"]::before {
    left: 0%;
    top: 50%;
    margin-left: -12px;
    transform: translatey(-50%) rotate(-90deg);
}

[tooltip-position="top"]::before {
    left: 50%;
}

[tooltip-position="bottom"]::before {
    top: 100%;
    margin-top: 8px;
    transform: translateX(-50%) translatey(-100%) rotate(-180deg);
}

[tooltip-position="right"]::before {
    left: 100%;
    top: 50%;
    margin-left: 1px;
    transform: translatey(-50%) rotate(90deg);
}

[tooltip-position="left"]::after {
    left: 0%;
    top: 50%;
    margin-left: -8px;
    transform: translateX(-100%) translateY(-50%);
}

[tooltip-position="top"]::after {
    left: 50%;
}

[tooltip-position="bottom"]::after {
    top: 100%;
    margin-top: 8px;
    transform: translateX(-50%) translateY(0%);
}

[tooltip-position="right"]::after {
    left: 100%;
    top: 50%;
    margin-left: 8px;
    transform: translateX(0%) translateY(-50%);
}

[tooltip]:hover::after,
[tooltip]:hover::before {
    opacity: 1;
}
</style>


<style scoped>

.main-tabs-wrapper {
    margin-bottom: 40px;
    margin-top: -25px;
    padding-left: 25px;
    background: white;
    height: 52px;
}

.main-tabs-wrapper  button {
    display: block;
    float: left;
    height: 40px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    color: #5457E7;
    background: white;
    border: 1px solid #5457E7;
    padding-left: 15px;
    padding-right: 15px;
    margin: 0;

}
.main-tabs-wrapper  button.active {
    background: #5457E7;
    color: white;
}

</style>
