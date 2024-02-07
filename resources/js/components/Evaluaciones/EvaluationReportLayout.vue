<template>
  <section class="section-list">
      <v-card flat class="elevation-0 mb-4">
          <v-card-title>
            <div class="d-flex">
              <a href class="text-decoration-none"
                    :class="showTableDataDetail ? 'text-muted' : 'text-body'"
                    @click.prevent="showTableDataDetail = false">
                  Resumen de evaluaciones
              </a>
              <div v-show="showTableDataDetail">
                <span class="mdi mdi-chevron-right fa-lg"></span>
                <span>Tema</span>
              </div>
            </div>
          </v-card-title>
      </v-card>

      <!-- EVALUACIONES LISTADO-->
      <v-card v-show="!showTableDataDetail" flat class="elevation-0 mb-4">
          <v-card-text>
            <div class="row col-sm-12 p-4">
              <div class="col-sm-3">
                <DefaultAutocomplete
                    v-model="modulo"
                    :items="modules"
                    label="Módulos"
                    item-text="name"
                    item-value="id"
                    dense
                    multiple
                    :show-select-all="false"
                    :count-show-values="3"
                    :max-values-selected="1"
                    :loading="modules_loader"
                    @onChange="moduloChange"
                  />
              </div>
              <div class="col-sm-3 mb-3">
                  <DefaultAutocomplete
                      v-model="escuela"
                      :disabled="!filteredSchools[0]"
                      :items="filteredSchools"
                      label="Escuelas"
                      item-text="name"
                      item-value="id"
                      dense
                      multiple
                      :show-select-all="false"
                      :count-show-values="3"
                      :max-values-selected="1"
                      :loading="schools_loader"
                      @onChange="escuelaChange"
                  />
              </div>
              <div class="col-sm-3 mb-3">
                  <DefaultAutocomplete
                      v-model="curso"
                      :items="courses"
                      :disabled="!courses[0]"
                      label="Cursos"
                      item-text="name"
                      item-value="id"
                      dense
                      multiple
                      :count-show-values="5"
                      :max-values-selected="5"
                      :loading="courses_loader"
                      @onChange="cursoChange"
                  />
              </div>
              <div class="col-sm-3 mb-3">
                  <DefaultAutocomplete
                      :disabled="!topics[0]"
                      v-model="tema"
                      :items="topics"
                      label="Temas"
                      item-text="name"
                      item-value="id"
                      dense
                      multiple
                      :count-show-values="5"
                      :loading="topics_loader"
                  />
              </div>
              <div class="col-sm-3">
                <DefaultInputDate
                  clearable
                  dense
                  :referenceComponent="'modalDateFilter1'"
                  :options="modalDateFilter1"
                  v-model="date_start"
                  label="Fecha Inicio"
                />

              </div>
              <div class="col-sm-3">
                <DefaultInputDate
                  clearable
                  dense
                  :referenceComponent="'modalDateFilter2'"
                  :options="modalDateFilter2"
                  v-model="date_end"
                  label="Fecha Fin"
                />
              </div>

              <div class="row col-sm-12 mt-4 m-0 p-0 justify-center">
                <div class="col-sm-4">
                  <b-button
                    :disabled="modulo.length === 0 || curso.length === 0"
                    block
                    variant="primary"
                    @click="searchEvaluations"
                    >
                    Consultar
                  </b-button>
                </div>
              </div>
            </div>

            <!-- === TABLA RESUMEN === -->
            <div v-if="showTableData">
              <div class="container-fluid">
                <div>
                  <v-card>
                    <v-card-title class="default-dialog-title text-bold">Resumen de evaluaciones</v-card-title>

                    <InfoTable
                      :addClass="'py-5 px-10'"
                      :headers="headersTableData"
                      class="mb-4"
                      >

                      <template slot="content">
                        <div
                            class="row text-center border-bottom align-items-center"
                            v-for="eva of evaluations"
                            :key="eva.topic_id"
                          >
                          <div class="col text-left">
                            <span v-text="eva.course_name"></span>
                          </div>
                          <div class="col text-left">
                            <a href v-text="eva.topic_name" @click.prevent="searchEvaluationDetail(eva)"></a>
                          </div>
                          <div class="col">
                            <span v-text="eva.total_corrects"></span>
                          </div>
                          <div class="col">
                            <span v-text="eva.total_incorrects"></span>
                          </div>
                          <div class="col">
                            <span v-text="eva.total_evaluations"></span>
                          </div>
                        </div>

                        <!-- no-data -->
                        <div
                          v-show="!evaluations.length"
                          class="row justify-content-center mt-4">
                          No se encontraron resultados.
                        </div>
                        <!-- no-data -->

                      </template>

                    </InfoTable>

                  </v-card>
                </div>

                <!-- DOWNLOAD BUTTONS -->
                <div class="row justify-content-between">
                  <div class="col-4">
                    <v-btn class="px-3"
                        color="primary"
                        dense
                        @click="downloadReportEvaluations">

                      <span class="fas fa-download mr-1"></span>
                      Descargar resumen
                    </v-btn>
                  </div>

                  <div class="col-4 text-center">
                    <v-btn class="px-3"
                      color="primary"
                      dense
                      @click="downloadReportEvaluationsDetails">
                      <span class="fas fa-download mr-1"></span>
                      Descargar detalle
                    </v-btn>

                    <v-btn class="px-3"
                      color="primary"
                      dense
                      @click="searchEvaluationDetailTopics">
                      <v-icon class="mr-1">
                        mdi-eye-outline
                      </v-icon>

                      Ver detalle
                    </v-btn>
                  </div>

                  <div class="col-4"></div>
                </div>
                <!-- DOWNLOAD BUTTONS -->

              </div>
            </div>
            <!-- === TABLA RESUMEN === -->
          </v-card-text>

      </v-card>
      <!-- EVALUACIONES LISTADO-->

      <!-- EVALUACIONES DETALLE-->
      <v-card v-show="showTableDataDetail" flat class="elevation-0 mb-4">
        <div class="container-fluid">
            <v-card>
              <v-card-title class="default-dialog-title text-bold">Resumen de evaluaciones tema</v-card-title>

              <InfoTable
                :addClass="'py-5 px-10'"
                :headers="headersTableDetailData"
                class="mb-4"
                >

                <template slot="content">
                  <div
                      class="row text-center border-bottom align-items-center"
                      v-for="eva of evaluations_details"
                      :key="eva.question_id"
                    >
                    <div class="col text-left">
                      <span v-text="eva.course_name"></span>
                    </div>
                    <div class="col-3 text-left">
                      <span v-text="eva.topic_name"></span>
                    </div>
                    <div class="col-3 text-left">
                      <span v-html="eva.question_name"></span>
                    </div>
                    <div class="col">
                      <span v-text="eva.total_corrects"></span>
                    </div>
                    <div class="col">
                      <span v-text="eva.total_incorrects"></span>
                    </div>
                    <div class="col">
                      <span v-text="eva.total_evaluations"></span>
                    </div>
                  </div>

                  <!-- no-data -->
                  <div v-show="!evaluations_details.length"
                       class="row justify-content-center mt-4">
                     No se encontraron resultados.
                   </div>
                  <!-- no-data -->

                </template>

              </InfoTable>
            </v-card>
        </div>
      </v-card>
      <!-- EVALUACIONES DETALLE-->

      <DefaultReportModal
        :isOpen="openReport"
        :report="report"
        @onCancel="openReport = false"
        @onConfirm="openReport = false"
      />
  </section>
</template>

<script>
import lang from "./../../plugins/lang_datepicker";

import DefaultReportModal from '../../layouts/Default/DefaultReportModal.vue';
import InfoTable from './InfoTable.vue';

export default {
  components:{ InfoTable, DefaultReportModal },
  props: ["Evaluaciones"],
  data() {
    return {
      reportsBaseUrl: '',
      lang: lang,
      openReport: false,
      report: {},
      reportDetail: {},

      // === tables ===
      headersTableData:[
        { label:'Curso', align:'left' }, { label:'Tema', align:'left' },
        { label:'Correctas'}, { label:'Incorrectas' },
        { label:'Total evaluaciones' }
      ],
      headersTableDetailData: [
        { label:'Curso', align:'left' }, { label:'Tema', align:'left', cols:3 }, { label:'Pregunta', align:'left', cols:3 },
        { label:'Correctas' }, { label:'Incorrectas' },
        { label:'Total evaluaciones' }
      ],
      // === tables ===

      reportType: 'evaluations',
      reportSubType: 'evaluations_detail',

      //=== forms==
      filteredSchools: [],
      schools: [],
      courses: [],
      topics: [],
      areas:[],

      modulo: [],
      escuela: [],
      curso: [],
      tema: [],
      date_start: null,
      date_end: null,

      modalDateFilter1: {
        open: false,
      },
      modalDateFilter2: {
        open: false,
      },
      //=== forms==

      evaluations: [],
      evaluations_details: [],
      showTableData: false,
      showTableDataDetail: false,

      modules_loader: false,
      schools_loader: false,
      courses_loader: false,
      topics_loader: false,

      workspaceId: 0,
      adminId: 0,
      modules: [],
      reportsBaseUrl: '',
    };
  },
  async mounted(){
    const  vue = this;

    vue.reportsBaseUrl = vue.getReportsBaseUrl();

    vue.reportData = await vue.fetchDataReport();
    vue.modules = vue.reportData.modules;
    vue.workspaceId = vue.reportData.workspaceId;
    vue.adminId = vue.reportData.adminId;

    await vue.fetchFiltersData();
  },
  methods: {
    async fetchFiltersData(){
      const vue = this;

      vue.schools_loader = true;

      let urlSchools = `${vue.reportsBaseUrl}/filtros/schools/${vue.workspaceId}?grouped=0`;
      let responseSchools = await axios({
          url: urlSchools,
          method: 'get'
      });
      vue.schools_loader = false;
      vue.schools = responseSchools.data
    },
    async moduloChange() {
        let vue = this;

        vue.escuela = [];
        vue.curso = [];
        vue.tema = [];

        let alreadyAdded = []
        vue.filteredSchools = vue.schools.filter(s => {

            if (vue.modulo.includes(s.subworkspace_id) &&
                !alreadyAdded.includes(s.id)) {
                alreadyAdded.push(s.id)
                return true
            } else {
                return false
            }
        })
    },
    /**
     * Fetch courses
     * @returns {Promise<boolean>}
     */
    async escuelaChange() {
      let vue = this;

      vue.curso = [];
      vue.tema = [];
      vue.courses = [];
      vue.topics = [];

      if (vue.escuela.length === 0) return false;
      vue.courses_loader = true;

      let url = `${vue.reportsBaseUrl}/filtros/courses/${vue.escuela.join()}`
      let res = await axios({
          url,
          method: 'get'
      });
      vue.courses = res.data;
      vue.courses_loader = false;
    },
    /**
     * Fetch topics
     * @returns {Promise<boolean>}
     */
    async cursoChange() {
      let vue = this;

      vue.tema = [];
      vue.topics = [];
      if (vue.curso.length === 0) return false;
      vue.topics_loader = true;


      let url = `${vue.reportsBaseUrl}/filtros/topics/${vue.curso.join()}`
      let res = await axios({
          url,
          method: 'get'
      });

      vue.topics = res.data;
      vue.topics_loader = false;
    },

    /* === EVALUACIONES === */
    async searchEvaluations(){
      let vue = this;
      vue.showLoader();

      let response = await axios({
          url: `${vue.reportsBaseUrl}/exportar/${vue.reportType}_data`,
          method: 'post',
          data: {
            workspaceId: vue.workspaceId,
            adminId: vue.adminId,
            modulos: vue.modulo,
            escuelas: vue.escuela,
            cursos: vue.curso,
            temas: vue.tema,
            start: vue.date_start,
            end: vue.date_end,
          }
      });
      vue.showTableData = true;
      vue.evaluations = response.data.data;

      vue.hideLoader();
    },
    downloadReportEvaluations(){
      let vue = this;
      vue.report = { callback: vue.exportEvaluations, reportName: 'Resumen de evaluaciones'};
      vue.openReport = true;
      // console.log('downloadReportEvaluations', {report: vue.report});
    },
    async exportEvaluations(reportName) {
      let vue = this;
      const reportData = vue.reportData;

      const filtersDescriptions = {
          "Módulos" : vue.generateNamesArray(reportData.modules, vue.modulo),
          "Escuelas": vue.generateNamesArray(vue.schools, vue.escuela),
          "Cursos": vue.generateNamesArray(vue.courses, vue.curso),
          "Temas": vue.generateNamesArray(vue.topics, vue.tema),
          "Fecha inicial": vue.date_start,
          "Fecha final": vue.date_end,
      }
      try {
        let response = await axios({
            url: `${vue.reportsBaseUrl}/exportar/${vue.reportType}_excel`,
            method: 'post',
            data: {
              workspaceId: vue.workspaceId,
              adminId: vue.adminId,
              modulos: vue.modulo,
              escuelas: vue.escuela,
              cursos: vue.curso,
              temas: vue.tema,
              start: vue.date_start,
              end: vue.date_end,
              reportName,
              filtersDescriptions
            }
        });

        if(response.statusText == "OK"){
          setTimeout(() => {
              vue.queryStatus("reportes", "descargar_reporte_evaluaciones");
          }, 500);
        }

      } catch (ex) {
        console.log(ex.message)
      }
    },
    /* === EVALUACIONES === */

    /* === EVALUACION DETALLE === */
    async searchEvaluationDetail(evaluation) {
      let vue = this;
      vue.showLoader();

      let response = await axios({
          url: `${vue.reportsBaseUrl}/exportar/${vue.reportSubType}_data`,
          method: 'post',
          data: {
            topicId: evaluation.topic_id,
            evaluations: [ evaluation ], // para reusar
            workspaceId: vue.workspaceId,
            adminId: vue.adminId,

            modulos: vue.modulo,
            escuelas: vue.escuela,
            cursos: vue.curso,
            temas: vue.tema,
            start: vue.date_start,
            end: vue.date_end,
          }
      });

      vue.evaluations_details = response.data.data;
      vue.showTableDataDetail = true;
      vue.hideLoader();
    },
    async searchEvaluationDetailTopics() {
      let vue = this;
      vue.showLoader();

      let response = await axios({
          url: `${vue.reportsBaseUrl}/exportar/${vue.reportSubType}_data`,
          method: 'post',
          data: {
            evaluations: vue.evaluations, // para reusar
            workspaceId: vue.workspaceId,
            adminId: vue.adminId,

            modulos: vue.modulo,
            escuelas: vue.escuela,
            cursos: vue.curso,
            temas: vue.evaluations.map((ele) => ele.topic_id), // topics ids
            start: vue.date_start,
            end: vue.date_end,
          }
      });

      vue.evaluations_details = response.data.data;
      vue.showTableDataDetail = true;
      vue.hideLoader();
    },
    downloadReportEvaluationsDetails() {
      let vue = this;
      vue.temas_ids = vue.evaluations.map((ele) => ele.topic_id);

      vue.report = { callback: vue.exportEvaluationsDetail, reportName: 'Resumen de evaluaciones detalle'};
      vue.openReport = true;

      // console.log('downloadReportEvaluationsDetails');
    },
    async exportEvaluationsDetail(reportName) {
      let vue = this;
      const reportData = vue.reportData;

      const filtersDescriptions = {
          "Módulos" : vue.generateNamesArray(reportData.modules, vue.modulo),
          "Escuelas": vue.generateNamesArray(vue.schools, vue.escuela),
          "Cursos": vue.generateNamesArray(vue.courses, vue.curso),
          "Temas": vue.generateNamesArray(vue.topics, vue.tema),
          "Fecha inicial": vue.date_start,
          "Fecha final": vue.date_end,
      }

      try {
        let response = await axios({
            url: `${vue.reportsBaseUrl}/exportar/${vue.reportSubType}_excel`,
            method: 'post',
            data: {
              evaluations: vue.evaluations, // para reusar
              workspaceId: vue.workspaceId,
              adminId: vue.adminId,

              modulos: vue.modulo,
              escuelas: vue.escuela,
              cursos: vue.curso,
              temas: vue.temas_ids, // temas prelistados - ids
              start: vue.date_start,
              end: vue.date_end,

              reportName,
              filtersDescriptions
            }
        });

        if(response.statusText == "OK"){
          setTimeout(() => {
              vue.queryStatus("reportes", "descargar_reporte_evaluaciones_detalle");
          }, 500);
        }

      } catch (ex) {
        console.log(ex.message)
      }
    },
    /* === EVALUACION DETALLE === */
  },
};
</script>
<style>
.mx-datepicker-btn-confirm{
  background: #5d5fef !important;
  color:white !important;
}
</style>
