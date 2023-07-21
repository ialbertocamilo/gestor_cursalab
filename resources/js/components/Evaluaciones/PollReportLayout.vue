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
                    v-model="filters.modules"
                    :items="modules"
                    label="Módulos"
                    item-text="name"
                    item-value="id"
                    dense
                    multiple
                    :count-show-values="3"
                    :max-values-selected="1"
                    :loading="modules_loader"
                    @onChange="loadSchools(filters.modules)"
                  />
              </div>
              <div class="col-sm-3 mb-3">
                  <DefaultAutocomplete
                      :disabled="!modules.length"
                      v-model="filters.schools"
                      :items="schools"
                      label="Escuelas"
                      item-text="name"
                      item-value="id"
                      dense
                      multiple
                      :count-show-values="3"
                      :max-values-selected="1"
                      :loading="schools_loader"
                      @onChange="loadCourses(filters.schools)"
                  />
              </div>
              <div class="col-sm-3 mb-3">
                  <DefaultAutocomplete
                      :disabled="!schools.length"
                      v-model="filters.courses"
                      :items="courses"
                      label="Cursos"
                      item-text="name"
                      item-value="id"
                      dense
                      multiple
                      :count-show-values="5"
                      :max-values-selected="5"
                      :loading="courses_loader"
                      @onChange="loadTopics(filters.courses)"
                  />
              </div>
              <div class="col-sm-3 mb-3">
                  <DefaultAutocomplete
                      :disabled="!topics.length"
                      v-model="filters.topics"
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
                  range
                  :referenceComponent="'modalDateFilter1'"
                  :options="modalDateFilter1"
                  v-model="filters.date_start"
                  label="Fecha Inicio"
                />

              </div>
              <div class="col-sm-3">
                <DefaultInputDate
                  clearable
                  dense
                  range
                  :referenceComponent="'modalDateFilter2'"
                  :options="modalDateFilter2"
                  v-model="filters.date_end"
                  label="Fecha Fin"
                />
              </div>

              <div class="row col-sm-12 mt-4 m-0 p-0 justify-center">
                <div class="col-sm-4">
                  <b-button 
                    :disabled="!filters.modules.length || !filters.courses.length" 
                    block 
                    variant="primary" 
                    @click="searchData()"
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
                    <v-card-title class="default-dialog-title text-bold">Reporte evaluaciones</v-card-title>
                    
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
                            <a href v-text="eva.topic_name" @click.prevent="searchDataDetail(eva)"></a>
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

                <div class="row px-4 justify-content-between">
                  <div class="col-4">
                    <v-btn class="px-3" 
                        color="primary" 
                        dense 
                        @click="downloadReportEvaluations">
                      <v-icon>
                        mdi-file-excel
                      </v-icon>

                      Descargar resumen
                    </v-btn>
                  </div>

                  <div class="col-4 justify-content-center">
                    <v-btn class="px-3" 
                      color="primary" 
                      dense
                      @click="downloadReportEvaluationsDetails">
                      <v-icon>
                        mdi-file-excel
                      </v-icon>

                      Descargar detalle
                    </v-btn>

                    <v-btn class="px-3" color="primary" dense>
                      <v-icon>
                        mdi-file-eye
                      </v-icon>

                      Ver detalle
                    </v-btn>
                  </div>

                  <div class="col-4"></div>
                </div>

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
              <v-card-title class="default-dialog-title text-bold">Reporte evaluaciones</v-card-title>
              
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

  </section>
</template>

<script>
const FileSaver = require("file-saver");
import ModalBloqueReport from './ModalBloqueReport.vue';
import lang from "./../../plugins/lang_datepicker";
import InfoTable from './InfoTable.vue'
import ModalChangeNameReport from './ModalChangeNameReport.vue'
const now = new Date().toISOString();
const date_final = now.slice(0, 10);
const date_init = (now.slice(0, 4) - 1) +'-' +now.slice(5, 10);

export default {
  components:{ ModalBloqueReport, InfoTable, ModalChangeNameReport },
  props: ["Encuestas"],
  data() {
    return {
      reportsBaseUrl: '',
      lang: lang,
      // === tabla cabeceras ===
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
      // === tabla cabeceras ===

      evaluations: [],
      evaluations_details: [],
      modules: [],
      groups: [],
      schools: [],
      courses: [],
      topics: [],
      //loaders
      modules_loader: false,
      schools_loader: false,
      courses_loader: false,
      topics_loader: false,
      filters:{
          modules: [],
          schools: [],
          courses: [],
          topics: [],
          date_start: [],
          date_end: [],
      },
      modalDateFilter1: {
        open: false,
      },
      modalDateFilter2: {
        open: false,
      },
      showTableData: false,
      showTableDataDetail: false,
      modalOptions: {
        title:'Generador de Reportes por bloques',
        ref: 'TableDownload',
        open: true,
        base_endpoint: '',
        persistent:true,
        hideCancelBtn:true,
        confirmLabel:'Cerrar',
        loading:true
      },
      dates:[ date_init, date_final ], 
      download_list: [],
      filenameDialog: false,
      nameReport: {} 
    };
  },
  mounted(){
    this.loadModulos();
    this.reportsBaseUrl = this.getReportsBaseUrl()
    // this.showLoader();
  },
  methods: {
    loadModulos() {
      const vue = this;
      vue.modules_loader = true;

      vue.$http.get('/resumen_evaluaciones/modules')
         .then((res) => {
          vue.modules = res.data.data
          vue.modules_loader = false;
        });
    },
    loadSchools(module) {
      const vue = this;

      vue.courses = [];
      vue.schools = [];
      vue.topics = [];
      vue.filters.courses = [];
      vue.filters.schools = [];
      vue.filters.topics = [];

      if (!module.length) return;
      vue.schools_loader = true;

      vue.$http.get(`/resumen_evaluaciones/schools/${module}`)
         .then((res) => {
          vue.schools = res.data.data;
          vue.schools_loader = false;
        })
    },
    loadCourses(school){
      let vue = this;

      vue.courses = [];
      vue.topics = [];
      vue.filters.courses = [];
      vue.filters.topics = [];

      if (!school.length) return;
      vue.courses_loader = true;

      vue.$http.get(`/resumen_evaluaciones/courses/${school}`)
         .then((res) => {
          vue.courses = res.data.data;
          vue.courses_loader = false;
        });
    },
    loadTopics(course) {
      let vue = this;
      
      vue.topics = [];
      vue.filters.topics = [];

      if (!course.length) return;  
      vue.topics_loader = true;

      vue.$http.get(`/resumen_evaluaciones/topics/${course}`)
         .then((res) => {
          vue.topics = res.data.data;
          vue.topics_loader = false;
        });
    },
    searchData(){
      let vue = this;
      vue.showLoader();

      vue.$http.put(`/resumen_evaluaciones/evaluations_data`, vue.filters)
         .then((res) => {
            vue.showTableData = true;
            vue.evaluations = res.data.data;

            vue.hideLoader();
         })
         .catch((err) => {
            vue.showtAlertError();
         });
    },
    searchDataDetail(evaluation) {
      let vue = this;
      vue.showLoader();

      vue.$http.put(`/resumen_evaluaciones/evaluations_data_detail/${evaluation.topic_id}`, evaluation)
         .then((res) => {

            vue.evaluations_details= res.data.data;
            vue.showTableDataDetail = true;
            vue.hideLoader();
         });
    },
    downloadReportEvaluations(){
      let vue = this;
    
    },
    downloadReportEvaluationsDetails() {
      let vue = this;
    },
    downloadReportPollQuestion(type_poll_question){
      let vue = this;
      vue.filters.type_poll_question = type_poll_question;
      if(vue.filters.poll.type.code == 'xcurso'){
        vue.downloadCoursePoll();
      }else{
        vue.downloadFreePoll();
      }
    },
    async downloadFreePoll(){
      this.showLoader();
      await this.callApiReport([]);
      this.hideLoader();
    },
    async downloadCoursePoll(){
      const vue = this;
      vue.filters.courses_selected = vue.filters.courses.length > 0 ? vue.filters.courses : vue.courses;
      const groupby_courses_by_school = vue.groupArrayOfObjects(vue.filters.courses_selected,'school_id','get_array'); //Function in mixin.js
      //If the selected schools are greater than 10, the data will be downloaded in parts
      const chunk_courses_by_school = vue.sliceIntoChunks(groupby_courses_by_school,15);//Function in mixin.js
      if (chunk_courses_by_school.length == 1) {
        vue.showLoader();
        await this.callApiReport(vue.filters.courses_selected.map(c => c.id));
      }else{
        for (const array_courses of chunk_courses_by_school){
          let get_all_courses_id = [];
          const content = '('+array_courses.length+')';
          //message in tooltip (list of name's schools)
          let schools_name = [];
          
          for (const courses of array_courses) {
            get_all_courses_id = [...get_all_courses_id,...courses.map(c => c.id)];
            const school = this.schools.find(school => school.id == courses[0].school_id);
            // schools_name = schools_name.length == 0 ? school.name : schools_name+', '+school.name  
            schools_name.push(school.name);
          }
          this.download_list.push({
            content,
            schools: schools_name,
            status:'pending',
            url:'',
            new_name:'',
            courses_id : get_all_courses_id
          });
          this.modalOptions.open = true;
        }
        this.verifyStatusDownload();
      }
    },
    verifyStatusDownload(){
      let vue = this;
      const find_index_donwload_pending =  vue.download_list.findIndex(dl => dl.status == 'pending');
      if(find_index_donwload_pending > -1){
        vue.download_list[find_index_donwload_pending].status = 'processing';
        vue.callApiReport(vue.download_list[find_index_donwload_pending].courses_id);
      }else{
        //if all donwload list is complete.
        vue.modalOptions.loading = false;
      }
      return true;
    },
    change_status_download(data){
      let vue = this;
      const find_index = vue.download_list.findIndex(dl => dl.status == 'processing');
      let urlReporte = `${vue.reportsBaseUrl}/${data.ruta_descarga}`;
      vue.download_list[find_index].url=urlReporte;
      vue.download_list[find_index].new_name = data.new_name;
      vue.download_list[find_index].status= (data.alert) ? 'no_data' : 'complete';
      vue.verifyStatusDownload();
      return true;
    },
    async callApiReport(courses_id){
      let vue = this;
      vue.filters.courses_selected = courses_id;
      await axios.post(`${vue.reportsBaseUrl}/exportar/poll-questions`,vue.filters).then(({data})=>{
        data.new_name = this.generateFilename(
                    'Reporte-Encuestas',
                    ''
                )
        if(vue.download_list.length>0){
          this.change_status_download(data);
          return true;
        }
        if(data.alert){
          vue.hideLoader();
          vue.showAlert(data.alert,'warning');
          return false;
        }
        if(data.error){
          vue.showtAlertError();
          return false;
        }
        vue.queryStatus("reportes", "descargar_reporte_encuesta");
        data.url = `${vue.reportsBaseUrl}/${data.ruta_descarga}`
        this.openModalChangeName(data);
        vue.hideLoader();
      })
      .catch((e)=>{
        console.log(e);
        vue.showtAlertError();
      })
    },
    showtAlertError(){
      this.hideLoader();
      this.showAlert('Ha ocurrido un problema. Contáctate con el equipo de soporte.','warning');
    },
    generateFilename(prefix, name) {
        return prefix + ' ' +
            name + ' ' +
            new Date().toISOString().slice(0, 10) +
            '.xlsx'
    },
    openModalChangeName(data){
      this.filenameDialog = true;
      this.nameReport = data;
    },
    async saveReport({url,new_name}){
      // La extension la define el back-end, ya que el quien crea el archivo
      this.showLoader();
      if(!new_name.includes('.xlsx')){
        new_name = new_name+'.xlsx';
      }
      this.filenameDialog = false;
      this.nameReport = {};
      await FileSaver.saveAs(url,new_name);
      this.hideLoader();
    },
    closeModal(){
      this.modalOptions.open = false;
      this.modalOptions.loading = true;
      this.download_list = [];
    },
    modifyFilterDate(dates){
      if(!dates[0]){
        return false;
      }
      let vue=this;
      vue.filters.date.start =  vue.parseToDateTime(dates[0],'start');
      vue.filters.date.end =  vue.parseToDateTime(dates[1],'end');
    },
    deleteFilterDate(){
      this.filters.date.end = null;
      this.filters.date.start = null;
    },
    verifyButton(){
      let vue = this;
      return 
    }
  },
};
</script>
<style>
.mx-datepicker-btn-confirm{
  background: #5d5fef !important;
  color:white !important;
}
</style>
