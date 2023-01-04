<template>
  <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
              Resultados de encuestas 
            </v-card-title>
        </v-card>
        <!-- FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
              <div class="row col-sm-12 p-4">
                <div class="col-sm-6">
                  <!-- {{Encuestas}} -->
                  <label>Encuesta</label>
                  <DefaultAutocomplete
                        v-model="filters.poll"
                        :items="polls"
                        label=""
                        item-text="titulo"
                        item-value="id"
                        dense
                        returnObject
                        @onChange="loadSchools"
                    />
                </div>
                <div class="col-sm-6">
                  <label>Módulo</label> 
                  <DefaultAutocomplete
                      v-model="filters.modules"
                      :items="modules"
                      label=""
                      item-text="name"
                      item-value="id"
                      dense
                      multiple
                    />
                </div>
                <div class="col-sm-6 mb-3">
                    <label text-variant="muted">Escuela</label>
                    <DefaultAutocomplete
                        :disabled="!schools[0]"
                        v-model="filters.schools"
                        :items="schools"
                        label=""
                        item-text="name"
                        item-value="id"
                        dense
                        multiple
                        @onChange="loadCourses"
                    />
                </div>
                <div class="col-sm-6 mb-3">
                    <label text-variant="muted">Curso</label>
                    <DefaultAutocomplete
                        :disabled="!courses[0]"
                        v-model="filters.courses"
                        :items="courses"
                        label=""
                        item-text="name"
                        item-value="id"
                        dense
                        multiple
                        returnObject
                    />
                </div>
                <div class="col-sm-6">
                  <label text-variant="muted">Fechas</label>
                  <date-picker
                      confirm
                      confirm-text="Seleccionar fecha"
                      attach
                      v-model="dates"
                      type="date"
                      range
                      placeholder="Rango de fecha"
                      :lang="lang"
                      @confirm="modifyFilterDate"
                      @clear="deleteFilterDate"
                      style="width: 100% !important"
                      value-type="YYYY-MM-DD"
                  ></date-picker>
                </div>
                <div class="row col-sm-12 mt-4 m-0 p-0 justify-center">
                  <div class="col-sm-4">
                    <b-button :disabled="!filters.schools[0] || !filters.modules[0]" block variant="primary" @click="searchData()">Consultar</b-button>
                  </div>
                </div>
              </div>
              <!-- Resumen -->
              <div class="m-5" v-if="poll_searched">
                <!-- <h2 class="text-center">Resumen</h2> -->
                <div class="container-fluid">
                  <div>
                    <!-- <ResumenEncuesta :resume="resume" /> -->
                    <!-- <h5>Detalle de encuestas</h5> -->
                    <v-card>
                      <v-card-title class="default-dialog-title text-bold">Reporte por tipo de pregunta</v-card-title>
                      <InfoTable :addClass="'py-5 px-10'" :headers="['Tipos de pregunta','Descripción']" class="mb-4">
                        <template slot="content">
                          <div class="row" v-for="(type_poll_question) in types_pool_questions" :key="type_poll_question.id">
                            <div class="col-sm-6 d-flex  justify-space-between align-center" style="border: 1px solid #EDF1F4;">
                              <div style="color: #5458EA;" v-text="type_poll_question.name"></div>
                              <v-icon 
                                color="#5458EA"
                                @click="downloadReportPollQuestion(type_poll_question)"
                              >
                                mdi-download
                              </v-icon>
                            </div>
                            <div class="col-sm-6" style="border: 1px solid #EDF1F4;">
                              <div v-text="type_poll_question.description">
                              </div>
                            </div>
                          </div>
                        </template>
                      </InfoTable>
                    </v-card>
                  </div>
                </div>
              </div>
            </v-card-text>
            <ModalBloqueReport 
              :modalOptions="modalOptions"
              :download_list="download_list"
              @saveReport="openModalChangeName"
              @closeModal="closeModal"
            />
            <ModalChangeNameReport 
              :filenameDialog="filenameDialog"
              :data="nameReport"
              @saveReport="saveReport"
              @closeModal="filenameDialog=false"
            />
        </v-card>
  </section>
</template>

<script>
const FileSaver = require("file-saver");
import ResumenEncuesta from './ResumenEncuesta.vue';
import ModalBloqueReport from './ModalBloqueReport.vue';
import lang from "./../../plugins/lang_datepicker";
import InfoTable from './InfoTable.vue'
import ModalChangeNameReport from './ModalChangeNameReport.vue'

export default {
  components:{ResumenEncuesta,ModalBloqueReport,InfoTable,ModalChangeNameReport},
  props: ["Encuestas"],
  data() {
    return {
      reportsBaseUrl: '',
      lang: lang,
      polls:[],
      modules:[],
      groups:[],
      schools: [],
      courses: [],
      //
      filters:{
          poll:0,
          groups:[],
          modules:[],
          schools: [],
          courses: [],
          type_poll_question:{},
          courses_selected:[],
          date:{
            start:null,
            end:null
          }
      },
      poll_searched:false,
      resume:{
        questions_type_califica:[]
      },
      types_pool_questions:[],
      modalOptions: {
        title:'Generador de Reportes por bloques',
        ref: 'TableDownload',
        open: true,
        base_endpoint: '',
        persistent:true,
        showCardActions:false,
        hideCancelBtn:true
      },
      dates:null,
      download_list:[],
      filenameDialog:false,
      nameReport:{}
    };
  },
  mounted(){
    this.loadInitialData();
    this.reportsBaseUrl = this.getReportsBaseUrl()
    // this.showLoader();
  },
  methods: {
    async loadInitialData(){
      let vue = this;
      vue.poll_searched = false;
      await axios.get('/resumen_encuesta/initial-data').then(({data})=>{
        vue.polls = data.data.polls;
        vue.modules = data.data.modules;
      })
    },
    async loadSchools(){
      let vue = this;
      vue.poll_searched = false;
      vue.courses = [];
      vue.schools = [];
      vue.filters.courses = [];
      vue.filters.schools = [];
     
      await axios.get('/resumen_encuesta/schools/'+vue.filters.poll.id).then(({data})=>{
        vue.schools = data.data.schools;
        if(vue.schools.length==0){
          vue.showAlert('Esta encuesta no tiene ningún curso asociado.','warning');
        }
      })
    }, 
    async loadCourses(){
      let vue = this;
      vue.poll_searched = false;
      vue.filters.courses = [];
      if(vue.filters.schools ==0){
        return true;
      }
      await axios.post('/resumen_encuesta/courses',{
        poll_id:vue.filters.poll.id,
        schools: vue.filters.schools
      }).then(({data})=>{
        vue.courses = data.data.courses;
        if(vue.courses.length==0){
          vue.showAlert('Esta encuesta no tiene ningún curso asociado.','warning');
        }
      })
    },
    async searchData(){
      let vue = this;
      vue.poll_searched = true;
      vue.types_pool_questions = [];
      vue.showLoader();
      vue.filters.courses_selected = vue.filters.courses.length > 0 ? vue.filters.courses.map(c=>c.id) : vue.courses.map(c=>c.id)
      await axios.post('/resumen_encuesta/poll-data',vue.filters).then(({data})=>{
        vue.types_pool_questions = data.data.data.types_pool_questions;
        vue.resume = data.data.data.resume;
        vue.hideLoader();
      })
      .catch((er)=>{
        console.log(er);
        vue.showtAlertError();
      })
    },  
    parseToDateTime(date,type){
      if(!date){
        return '';
      }
      return type == 'start' ? `${date} 00:00:00` : `${date} 23:59:59`;
    },
    async cargarGrupos() {
      let vue = this;
      vue.Grupos = [];
      vue.grupo = "";
      if (!vue.curso) return false;
      var res = await axios.post("cambia_grupo", {
        curso: vue.curso,
      });
      vue.Grupos = res.data;
    },
    async downloadReportPollQuestion(type_poll_question){
      let vue = this;
      vue.filters.type_poll_question = type_poll_question;
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
          let schools_name = '';
          
          for (const courses of array_courses) {
            get_all_courses_id = [...get_all_courses_id,...courses.map(c => c.id)];
            const school = this.schools.find(school => school.id == courses[0].school_id);
            schools_name = schools_name.length == 0 ? school.name : schools_name+', '+school.name  
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
        vue.modalOptions.showCardActions = true;
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
      this.modalOptions.showCardActions = false;
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
    }
  },
};
</script>