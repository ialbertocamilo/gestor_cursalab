<template>
    <v-app class="--section-list">

        <ReportPromptModal
            :prefix="reportName"
            :isOpen="isAskingForNewReport"
            @cancel="isAskingForNewReport = false"
            @confirm="confirmNewReport($event)"/>

        <v-row class="pl-3 pr-3" style="flex: 0 1 auto">
            <v-col cols="12" class="main-tabs-wrapper">
                <button
                    @click="activeTab = 'history'"
                    :class="{ active: activeTab === 'history' }"
                    type="button">
                    <v-icon :color="activeTab === 'history' ? 'white' : '#5457E7'">
                        mdi-folder-file-outline
                    </v-icon>
                    Mis reportes
                </button>
                <button
                    @click="activeTab = 'new-report'"
                    :class="{ active: activeTab === 'new-report' }"
                    type="button">
                    <v-icon :color="activeTab === 'new-report' ? 'white' : '#5457E7'">
                        mdi-file-chart
                    </v-icon>
                    Generar nuevo reporte
                </button>


            </v-col>
        </v-row>

        <v-card v-if="activeTab === 'history'" flat class="elevation-0 --mb-4">
            <ReportsHistory
                :is-super-user="isSuperUser"
                :workspaceId="workspaceId"
                :reportsBaseUrl="reportsBaseUrl"
                :adminId="adminId"/>
        </v-card>
        <v-card v-if="activeTab === 'new-report'" flat class="elevation-0 --mb-4">
            <v-tabs vertical class="reports-menu" v-model="selectedTab">

                <!--

                REPORTS TABS

                ============================================================================ -->


                <v-tab class="justify-content-start py-7" :key="'#notas-de-usuario'"  v-if="permissions.show_report_notas_usuario">
                    <v-icon left>mdi-account</v-icon>
                    <span class="pt-2">
                        Notas de usuario
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7" key='usuarios' v-if="permissions.show_report_usuarios">
                    <v-icon left>mdi-account-multiple</v-icon>
                    <span class="pt-2">
                        Usuarios
                    </span>
                </v-tab>
                <v-tab class="justify-content-start py-7" key='process_progress' v-if="permissions.show_report_process_progress">
                    <v-icon left>fa fa-users</v-icon>
                    <span class="pt-2">
                        Progreso de procesos
                    </span>
                </v-tab>
                <v-tab class="justify-content-start py-7" key='process_detail' v-if="permissions.show_report_process_detail">
                    <v-icon left>fa fa-users</v-icon>
                    <span class="pt-2">
                        Detalle de procesos
                    </span>
                </v-tab>
                
                <v-tab v-if="permissions.show_report_avance_curricula" class="justify-content-start py-7" key='avance-de-currícula'>
                    <v-icon left>mdi-book-open-page-variant-outline</v-icon>
                    <span class="pt-2">
                       Avance de currícula
                   </span>
                </v-tab>

                <v-tab v-if="permissions.show_report_diploma" class="justify-content-start py-7" key='diplomas'>
                    <v-icon left>mdi-certificate</v-icon>
                    <span class="pt-2">
                        Diplomas
                    </span>
                </v-tab>

                <v-tab v-if="permissions.show_report_visitas" class="justify-content-start py-7" key='visitas'>
                    <v-icon left>mdi-access-point</v-icon>
                    <span class="pt-2">
                        Visitas
                    </span>
                </v-tab>

                <v-tab v-if="permissions.show_report_nota_por_tema" class="justify-content-start py-7" key='notas-por-tema'>
                    <v-icon left>mdi-book-outline</v-icon>
                    <span class="pt-2">
                        Notas por tema
                    </span>
                </v-tab>

                <v-tab v-if="permissions.show_report_tema_no_evaluable" class="justify-content-start py-7" key='temas-no-evaluables'>
                    <v-icon left>mdi-book-outline</v-icon>
                    <span class="pt-2">
                        Temas no evaluables
                    </span>
                </v-tab>

                <v-tab v-if="permissions.show_report_nota_por_curso" class="justify-content-start py-7" key='notas-por-curso'>
                    <v-icon left>mdi-book-open-page-variant-outline</v-icon>
                    <span class="pt-2">
                       Notas por curso
                   </span>
                </v-tab>
                <v-tab class="justify-content-start py-7" key='dc3-dc4-report' v-if="permissions.show_report_dc3">
                    <v-icon left>mdi-book-open-page-variant-outline</v-icon>
                    <span class="pt-2">
                       DC3 - DC4
                   </span>
                </v-tab>
                <v-tab class="justify-content-start py-7" key='registro-capacitacion' v-if="permissions.show_report_registro_capacitacion">
                    <v-icon left>mdi-book-open-page-variant-outline</v-icon>
                    <span class="pt-2">
                       Registro de capacitación
                   </span>
                </v-tab>
                <v-tab v-if="permissions.show_report_segmentacion" class="justify-content-start py-7" key='segmentacion'>
                    <v-icon left>fa fa-square</v-icon>
                    <span class="pt-2">
                       Segmentación
                   </span>
                </v-tab>
                <v-tab v-if="permissions.show_report_evaluaciones_abiertas" class="justify-content-start py-7" key='evaluaciones-abiertas'>
                    <v-icon left>mdi-book-outline</v-icon>
                    <span class="pt-2">
                        Evaluaciones abiertas
                    </span>
                </v-tab>

                <v-tab v-if="permissions.show_report_reinicios" class="justify-content-start py-7" key='reinicios'>
                    <v-icon left>mdi-restart</v-icon>
                    <span class="pt-2">
                        Reinicios
                    </span>
                </v-tab>
                <!--
                                <v-tab class="justify-content-start py-7" key='first'>
                                    <v-icon left>mdi-access-point</v-icon>
                                    <span class="pt-2">
                                        Versiones usadas
                                    </span>
                                </v-tab>
                -->
                <v-tab v-if="permissions.show_report_usuario_uploads" class="justify-content-start py-7" key='usuarios-uploads'>
                    <v-icon left>mdi-file-account-outline</v-icon>
                    <span class="pt-2">
                        Usuario Uploads
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7" key='vademecum' v-if="permissions.show_report_vademecun">
                    <v-icon left>mdi-access-point</v-icon>
                    <span class="pt-2">
                        Protocolos y documentos
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7" key='videoteca' v-if="permissions.show_report_videoteca">
                    <v-icon left>mdi-play-box-outline</v-icon>
                    <span class="pt-2">
                        Videoteca
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7" key='checklist-detallado' v-if="permissions.show_report_checklist">
                    <v-icon left>mdi-playlist-check</v-icon>
                    <span class="pt-2">
                        Checklist Detallado
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7" key='checklist-general' v-if="permissions.show_report_checklist">
                    <v-icon left>mdi-playlist-check</v-icon>
                    <span class="pt-2">
                        Checklist General
                    </span>
                </v-tab>


                <v-tab v-if="permissions.show_report_ranking" class="justify-content-start py-7" key='ranking'>
                    <v-icon left>mdi-numeric</v-icon>
                    <span class="pt-2">
                        Ranking
                    </span>
                </v-tab>

                <v-tab class="justify-content-start py-7" key='reuniones' v-if="permissions.show_report_sessions_live">
                    <v-icon left>mdi-monitor-account</v-icon>
                    <span class="pt-2">
                        Reuniones
                    </span>
                </v-tab>

                <v-tab v-if="permissions.show_report_historial_usuario" class="justify-content-start py-7" key='historial-de-usuario'>
                    <v-icon left>mdi-account</v-icon>
                    <span class="pt-2">
                        Historial de usuario
                    </span>
                </v-tab>

                <v-tab v-if="permissions.show_report_criterios_vacios" class="justify-content-start py-7" key='criterios-vacios'>
                    <v-icon left>fa fa-square</v-icon>
                    <span class="pt-2">
                        Criterios vacíos
                    </span>
                </v-tab>

                <v-tab v-if="permissions.show_report_multiple_usuarios" class="justify-content-start py-7"
                       key='historial-multiples-usuarios'>
                    <v-icon left>mdi-account-multiple</v-icon>
                    <span class="pt-2">
                        Historial de múltiples usuarios
                    </span>
                </v-tab>
                <v-tab class="justify-content-start py-7" key='benefit-report' v-if="permissions.show_report_benefit">
                    <v-icon left>fa fa-square</v-icon>
                    <span class="pt-2">
                        Reporte de beneficios
                    </span>
                </v-tab>
                <v-tab class="justify-content-start py-7" key='user-benefit-report' v-if="permissions.show_report_benefit">
                    <v-icon left>fa fa-square</v-icon>
                    <span class="pt-2">
                        Usuarios segmentados al Beneficio
                    </span>
                </v-tab>
                <v-tab class="justify-content-start py-7" key='votaciones' v-if="permissions.show_report_reconocimiento">
                    <v-icon left>fa fa-paper-plane</v-icon>
                    <span class="pt-2">
                        Votaciones
                    </span>
                </v-tab>

                <!--

                TABS CONTENT

                ============================================================================ -->

                <v-tab-item v-if="permissions.show_report_notas_usuario">
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

                <v-tab-item v-if="permissions.show_report_usuarios">
                    <v-card flat>
                        <v-card-text>
                            <Usuarios
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                <v-tab-item v-if="permissions.show_report_process_progress">
                    <v-card flat>
                        <v-card-text>
                            <ProcessProgress
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                <v-tab-item v-if="permissions.show_report_process_detail">
                    <v-card flat>
                        <v-card-text>
                            <ProcessDetail
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                <v-tab-item v-if="permissions.show_report_avance_curricula">
                    <v-card flat>
                        <v-card-text>
                            <AvanceCurricula
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_diploma">
                    <v-card flat>
                        <v-card-text>
                            <Diploma
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_visitas">
                    <v-card flat>
                        <v-card-text>
                            <Visitas
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_nota_por_tema">
                    <v-card flat>
                        <v-card-text>
                            <NotasTema
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :modalities="modalities"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_tema_no_evaluable">
                    <v-card flat>
                        <v-card-text>
                            <TemasNoEvaluables
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :modalities="modalities"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_nota_por_curso">
                    <v-card flat>
                        <v-card-text>
                            <NotasCurso
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :modalities="modalities"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                <v-tab-item v-if="permissions.show_report_dc3">
                    <v-card flat>
                        <v-card-text>
                            <Dc3Dc4
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                <v-tab-item v-if="permissions.show_report_registro_capacitacion">
                    <v-card flat>
                        <v-card-text>
                            <RegistroCapacitacion
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                <v-tab-item v-if="permissions.show_report_segmentacion">
                    <v-card flat>
                        <v-card-text>
                            <Segmentacion
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_evaluaciones_abiertas">
                    <v-card flat>
                        <v-card-text>
                            <EvaAbiertas
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_reinicios">
                    <v-card flat>
                        <v-card-text>
                            <Renicios
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :admins="admins"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
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
                <v-tab-item v-if="permissions.show_report_usuario_uploads">
                    <v-card flat>
                        <v-card-text>
                            <UsuarioUploads
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_vademecun">
                    <v-card flat>
                        <v-card-text>
                            <Vademecum
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :vademecumList="VademecumList"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_videoteca">
                    <v-card flat>
                        <v-card-text>
                            <Videoteca
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_checklist">
                    <v-card flat>
                        <v-card-text>
                            <ChecklistDetallado
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_checklist">
                    <v-card flat>
                        <v-card-text>
                            <ChecklistGeneral
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_ranking">
                    <v-card flat>
                        <v-card-text>
                            <Ranking
                                :workspaceId="workspaceId"
                                :adminId="adminId"

                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"

                                @generateReport="generateReport($event)"
                            />
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_sessions_live">
                    <v-card flat>
                        <v-card-text>
                            <Meetings
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                @emitir-reporte="crearReporte"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>

                <v-tab-item v-if="permissions.show_report_historial_usuario">
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

                <v-tab-item v-if="permissions.show_report_criterios_vacios">
                    <v-card flat>
                        <v-card-text>
                            <EmptyCriteria
                                :workspaceId="workspaceId"
                                :adminId="adminId"

                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"

                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                <v-tab-item v-if="permissions.show_report_multiple_usuarios">
                    <v-card flat>
                        <v-card-text>
                            <UsersHistory
                                :workspaceId="workspaceId"
                                :adminId="adminId"

                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"

                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                <v-tab-item v-if="permissions.show_report_benefit">
                    <v-card flat>
                        <v-card-text>
                            <BenefitsReport
                                :workspaceId="workspaceId"
                                :adminId="adminId"

                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"

                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                <v-tab-item v-if="permissions.show_report_benefit">
                    <v-card flat>
                        <v-card-text>
                            <UsersBenefitReport
                                :workspaceId="workspaceId"
                                :adminId="adminId"

                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"

                                @generateReport="generateReport($event)"/>
                        </v-card-text>
                    </v-card>
                </v-tab-item>
                 <v-tab-item v-if="permissions.show_report_reconocimiento">
                    <v-card flat>
                        <v-card-text>
                            <Votaciones
                                :workspaceId="workspaceId"
                                :adminId="adminId"
                                :modules="modules"
                                :reportsBaseUrl="reportsBaseUrl"
                                @generateReport="generateReport($event)"/>
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

import ReportPromptModal from "../components/Reportes/ReportPromptModal.vue";

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
import ReportsHistory from "../components/Reportes/ReportsHistory";
import EmptyCriteria from "../components/Reportes/EmptyCriteria.vue";
import UsersHistory from "../components/Reportes/UsersHistory.vue";
import BenefitsReport from "../components/Reportes/BenefitsReport.vue";
import UsersBenefitReport from "../components/Reportes/UsersBenefitReport.vue";
import Votaciones from "../components/Reportes/Votaciones.vue";
import Dc3Dc4 from '../components/Reportes/Dc3Dc4'
import RegistroCapacitacion
    from "../components/Reportes/RegistroCapacitacion.vue";
import ProcessProgress from '../components/Reportes/ProcessProgress.vue';
import ProcessDetail from '../components/Reportes/ProcessDetail.vue';
export default {
    components: {
        RegistroCapacitacion,
        Votaciones,
        UsersHistory,
        EmptyCriteria,
        ReportPromptModal,
        ReportsHistory,
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
        Segmentacion,
        BenefitsReport,
        UsersBenefitReport,
        Dc3Dc4,
        ProcessProgress,
        ProcessDetail
    },
    data() {
        return {

            workspaceId: 0,
            adminId: 0,
            isSuperUser: false,
            isAskingForNewReport: false,
            generateReportCallback: () => {},

            modules: [],
            modalities:[],
            admins: [],
            reportTypes: [],
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
            selectedTab: 0,

            selectedFilters: {},
            filenameDialog: false,
            reportName: '',
            isBeingProcessedNotification: false,
            isReadyNotification: false,
            reportDownloadUrl: null,
            reportFilename: null,
            permissions:{
                show_report_dc3:false,
                show_report_registro_capacitacion:false,
                show_report_sessions_live:false,
                show_report_benefit:false,
                show_report_reconocimiento:false,
                show_report_checklist:false,
                show_report_videoteca:false,
                show_report_vademecun:false,
                show_report_notas_usuario:false,
                show_report_usuarios:false,
                show_report_avance_curricula:false,
                show_report_diploma:false,
                show_report_visitas:false,
                show_report_nota_por_tema:false,
                show_report_tema_no_evaluable:false,
                show_report_nota_por_curso:false,
                show_report_segmentacion:false,
                show_report_evaluaciones_abiertas:false,
                show_report_reinicios:false,
                show_report_usuario_uploads:false,
                show_report_ranking:false,
                show_report_historial_usuario:false,
                show_report_criterios_vacios:false,
                show_report_multiple_usuarios:false,
                show_report_process_progress:false,
            }
        }
    },
    mounted () {
        const vue = this
        this.reportsBaseUrl = this.getReportsBaseUrl()
        this.fetchData();

        let uri = window.location.search.substring(1);
        let params = new URLSearchParams(uri);
        let tab = params.get("tab");
        let section = params.get("section");
        let dni = params.get("dni");

        if (tab) {
            this.activeTab = tab
            this.selectedTab = parseInt(section)
        } else if (dni) {
            this.activeTab = 'new-report';
            this.selectedTab = '#notas-de-usuario'
        }
    }
    ,
    methods: {
        isSuper () {
            let vue = this;
            let isSuper = false
            if (!vue.userSession) return isSuper;
            vue.userSession
                .user
                .roles.forEach(r => {
                let isSuperInOneRole = (r.role_id === vue.superUserRoleId);

                if (isSuperInOneRole) {
                    isSuper = true
                }
            })

            return isSuper
        },
        async fetchData() {
            let vue = this;
            // Fetch report types

            let reportTypesUrl = '../reports/types'
            try {
                let response3 = await axios({
                    url: reportTypesUrl,
                    method: 'get'
                })

                vue.reportTypes = response3.data.data.types;
                vue.permissions = response3.data.data.permissions;

            } catch (ex) {
                console.log(ex)
            }
            //Init data
            let {
                userSession,
                adminId,
                workspaceId,
                modules,
                modalities,
                admins,
                VademecumList
            } = await vue.fetchDataReport();

            this.userSession = userSession;
            this.adminId = adminId
            this.workspaceId = workspaceId

            this.modules = modules
            this.admins = admins
            this.VademecumList = VademecumList
            this.modalities= modalities
            vue.isSuperUser = vue.isSuper();
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
                console.log(error)
                this.hideLoader()
            }
        },
        generateReport(report) {
            this.generateReportCallback = report.callback
            this.isAskingForNewReport = true

            // Generate report name
            const reportType = this.reportTypes.find(rt => rt.code === report.type)

            if (reportType) {
                this.reportName = reportType.name + ' ' +
                    moment(new Date).format('MM-DD')
            }
        },
        confirmNewReport(event) {

            this.generateReportCallback(event.reportName)
            this.isAskingForNewReport = false

            // notify that the report has been added

            const message = event.reportName
                ? `Tu solicitud de reporte "${event.reportName}" se añadió correctamente.`
                : `Tu solicitud de reporte se añadió correctamente.`

            this.$toast.warning({
                component: Vue.component('comp', {
                    template: `
                        <div>${message} <a href="javascript:"
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

button.restart-queue {

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
.v-tab {
    text-transform: capitalize !important
}

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
